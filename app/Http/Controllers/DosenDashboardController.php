<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\Jadwal; 
use App\Models\Pengumuman;

class DosenDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dosen');
    }
    
    public function index(Request $request)
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }
        
        $mata_kuliahs = $dosen->mataKuliahs()
            ->with(['mahasiswas' => function ($query) {
                $query->where('mahasiswas.status_krs', 'Disetujui');
            }, 'mahasiswas.programStudi'])
            ->withCount(['mahasiswas' => function ($query) {
                $query->where('mahasiswas.status_krs', 'Disetujui');
            }])
            ->get();
        
        $tahunAkademik = TahunAkademik::where('is_active', true)->first();
        $jadwalKuliah = collect();

        if ($tahunAkademik) {
            $mkIds = $mata_kuliahs->pluck('id');
            $jadwalKuliah = Jadwal::with('mataKuliah')
                ->whereIn('mata_kuliah_id', $mkIds)
                ->get()
                ->sortBy(function($jadwal) {
                    $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                    return $hariOrder[$jadwal->hari] ?? 99;
                });
        }

        $jumlahMahasiswaWali = $dosen->mahasiswaWali()->count();
        $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first();
        $pengumumans = Pengumuman::latest()->take(5)->get();

        // --- SMART FILTER & PAGINASI SURAT ---
        $query = $dosen->suratKeputusans()->where('status', 'Selesai');

        if ($request->filled('search_surat')) {
            $search = $request->search_surat;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        $suratKeputusans = $query->latest()->paginate(10)->withQueryString();

        return view('dosen.dashboard', compact(
            'dosen', 'mata_kuliahs', 'jadwalKuliah', 'jumlahMahasiswaWali', 
            'prodiYangDikepalai', 'pengumumans', 'suratKeputusans'
        ));
    }

    public function cetakJadwal()
    {
        $dosen = Auth::user()->dosen;
        $tahunAkademik = TahunAkademik::where('is_active', true)->firstOrFail();
        $mkIds = $dosen->mataKuliahs()->pluck('id');

        $jadwals = Jadwal::with(['mataKuliah', 'mataKuliah.kurikulum.programStudi'])
            ->whereIn('mata_kuliah_id', $mkIds)
            ->get()
            ->sortBy(function($jadwal) {
                $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                return $hariOrder[$jadwal->hari] ?? 99;
            });

        $pdf = Pdf::loadView('dosen.cetak_jadwal', compact('dosen', 'jadwals', 'tahunAkademik'));
        return $pdf->stream('Jadwal_Mengajar_' . $dosen->nama_lengkap . '.pdf');
    }

    public function uploadRps(Request $request, MataKuliah $mataKuliah)
    {
        $dosen = Auth::user()->dosen;
        if ($mataKuliah->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengubah RPS mata kuliah ini.');
        }

        $request->validate(['file_rps' => 'required|mimes:pdf|max:5120']);

        if ($request->hasFile('file_rps')) {
            if ($mataKuliah->file_rps && Storage::disk('public')->exists($mataKuliah->file_rps)) {
                Storage::disk('public')->delete($mataKuliah->file_rps);
            }
            $path = $request->file('file_rps')->store('rps', 'public');
            $mataKuliah->update(['file_rps' => $path]);
        }

        return redirect()->back()->with('success', 'File RPS berhasil diunggah.');
    }
}