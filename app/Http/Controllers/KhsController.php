<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\TahunAkademik;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;
use Barryvdh\DomPDF\Facade\Pdf; 

class KhsController extends Controller
{
    /**
     * Menampilkan halaman KHS dengan filter semester
     */
    public function index(Request $request): View | RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->mahasiswa) {
            abort(403, 'Hanya mahasiswa yang dapat mengakses halaman ini.');
        }
        $mahasiswa = $user->mahasiswa;

        // --- INTERCEPTOR: Cek Kewajiban Mengisi EDOM ---
        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->first();

        if ($sesiAktif && $mahasiswa->status_krs === 'Disetujui' && $sesiAktif->tahun_akademik_id) {
            $mataKuliahWajibEdom = $mahasiswa->mataKuliahs()
                ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
                ->pluck('mata_kuliahs.id')
                ->toArray();

            if (!empty($mataKuliahWajibEdom)) {
                $evaluasiSelesai = EvaluasiJawaban::where('mahasiswa_id', $mahasiswa->id)
                    ->where('evaluasi_sesi_id', $sesiAktif->id)
                    ->distinct()
                    ->pluck('mata_kuliah_id')
                    ->toArray();

                $belumDievaluasi = array_diff($mataKuliahWajibEdom, $evaluasiSelesai);

                if (!empty($belumDievaluasi)) {
                    return redirect()->route('evaluasi.index')
                        ->with('error', 'PERHATIAN: Anda wajib menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM) untuk seluruh mata kuliah semester ini sebelum dapat melihat Kartu Hasil Studi (KHS).');
                }
            }
        }
        // ------------------------------------------------

        // 1. Ambil daftar Tahun Akademik yang PERNAH DIAMBIL oleh mahasiswa tersebut
        $riwayatTahunAkademikIds = $mahasiswa->mataKuliahs()
            ->whereNotNull('mahasiswa_mata_kuliah.tahun_akademik_id')
            ->distinct()
            ->pluck('mahasiswa_mata_kuliah.tahun_akademik_id');

        $riwayatTahunAkademiks = TahunAkademik::whereIn('id', $riwayatTahunAkademikIds)
            ->orderBy('tahun', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        // 2. Tentukan Tahun Akademik mana yang sedang dilihat (Filter Dropdown)
        $selectedTaId = $request->input('tahun_akademik_id');
        
        // Jika tidak ada pilihan, default ke riwayat paling terbaru yang dia punya
        if (!$selectedTaId && $riwayatTahunAkademiks->isNotEmpty()) {
            $selectedTaId = $riwayatTahunAkademiks->first()->id;
        }

        // 3. Tarik data KHS HANYA untuk 1 semester tersebut
        $krsSelected = collect();
        $tahunSelected = null;
        $ipsData = ['total_sks' => 0, 'ips' => 0, 'nilaiBobot' => []];

        if ($selectedTaId) {
            $tahunSelected = TahunAkademik::find($selectedTaId);
            $krsSelected = $mahasiswa->mataKuliahs()
                ->wherePivot('tahun_akademik_id', $selectedTaId)
                ->withPivot('nilai')
                ->get();
            $ipsData = $mahasiswa->hitungIps($selectedTaId);
        }

        return view('khs.index', compact(
            'mahasiswa',
            'riwayatTahunAkademiks',
            'krsSelected',
            'tahunSelected',
            'ipsData',
            'selectedTaId'
        ));
    }

    /**
     * Memproses KHS menjadi PDF (Bisa Pratinjau atau Unduh)
     */
    public function cetak(Request $request)
    {
        $user = Auth::user();
        if (!$user->mahasiswa) { abort(403); }
        $mahasiswa = $user->mahasiswa;

        $selectedTaId = $request->input('tahun_akademik_id');
        
        if (!$selectedTaId) {
            return redirect()->back()->with('error', 'Silakan pilih Tahun Akademik terlebih dahulu sebelum mencetak KHS.');
        }

        $tahunSelected = TahunAkademik::findOrFail($selectedTaId);
        
        $krsSelected = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $selectedTaId)
            ->withPivot('nilai')
            ->get();
            
        $ipsData = $mahasiswa->hitungIps($selectedTaId);

        // Load view HTML dan ubah menjadi format PDF
        $pdf = Pdf::loadView('khs.pdf', compact('mahasiswa', 'krsSelected', 'tahunSelected', 'ipsData'));
        
        // Format penamaan file PDF yang rapi
        $namaFile = 'KHS_' . $mahasiswa->nim . '_' . str_replace('/', '-', $tahunSelected->tahun) . '_' . strtoupper($tahunSelected->semester) . '.pdf';
        
        // Jika ada parameter download=1 di URL, paksa browser untuk mengunduh
        if ($request->has('download') && $request->input('download') == 1) {
            return $pdf->download($namaFile);
        }
        
        // Jika tidak, tampilkan pratinjau (stream) di dalam browser/iframe
        return $pdf->stream($namaFile);
    }
}