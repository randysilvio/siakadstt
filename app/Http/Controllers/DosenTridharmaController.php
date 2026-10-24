<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\DosenTridharma;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DosenTridharmaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:dosen']);
    }

    /**
     * Halaman Utama Portofolio Tridharma, Kalkulator BKD 12-16 SKS, & Tracker Jafung (SISTER).
     */
    public function index(): View
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Profil dosen tidak ditemukan.');
        }

        $tahunAkademik = TahunAkademik::where('is_active', true)->first();

        $pivotMkIds = DB::table('dosen_mata_kuliah')
            ->where('dosen_id', $dosen->id)
            ->pluck('mata_kuliah_id')
            ->toArray();

        $mataKuliahs = MataKuliah::where(function ($q) use ($dosen, $pivotMkIds) {
                $q->where('dosen_id', $dosen->id)
                  ->orWhereIn('id', $pivotMkIds);
            })
            ->whereHas('kurikulum', fn($k) => $k->where('is_active', true))
            ->with('kurikulum.programStudi')
            ->orderBy('semester')
            ->get();

        $penelitians = $dosen->penelitians()->get();
        $pengabdians = $dosen->pengabdians()->get();
        $penunjangs  = $dosen->penunjangs()->get();
        $suratKeputusans = $dosen->suratKeputusans()->where('status', 'Selesai')->latest()->get();

        $ringkasanBkd = $dosen->hitungRingkasanBkd();

        return view('dosen.tridharma.index', compact(
            'dosen',
            'tahunAkademik',
            'mataKuliahs',
            'penelitians',
            'pengabdians',
            'penunjangs',
            'suratKeputusans',
            'ringkasanBkd'
        ));
    }

    /**
     * Menyimpan kegiatan Penelitian, Pengabdian Masyarakat (PkM), atau Penunjang baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403);
        }

        $validated = $request->validate([
            'unsur'                       => 'required|in:penelitian,pengabdian,penunjang',
            'kategori'                    => 'required|string|max:150',
            'judul'                       => 'required|string|max:300',
            'peran'                       => 'required|string|max:100',
            'tahun'                       => 'required|digits:4|integer|min:2000|max:' . (date('Y') + 1),
            'semester'                    => 'required|in:Ganjil,Genap',
            'sks_bkd'                     => 'required|numeric|min:0|max:12',
            'angka_kredit'                => 'required|numeric|min:0|max:100',
            'nama_jurnal_penerbit_lokasi' => 'nullable|string|max:255',
            'nomor_identitas'             => 'nullable|string|max:150',
            'tautan_eksternal'            => 'nullable|url|max:500',
            'file_bukti'                  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')->store('tridharma-dosen', 'public');
        }

        $dosen->tridharmas()->create([
            'unsur'                       => $validated['unsur'],
            'kategori'                    => strip_tags($validated['kategori']),
            'judul'                       => strip_tags($validated['judul']),
            'peran'                       => strip_tags($validated['peran']),
            'tahun'                       => $validated['tahun'],
            'semester'                    => $validated['semester'],
            'sks_bkd'                     => $validated['sks_bkd'],
            'angka_kredit'                => $validated['angka_kredit'],
            'nama_jurnal_penerbit_lokasi' => isset($validated['nama_jurnal_penerbit_lokasi']) ? strip_tags($validated['nama_jurnal_penerbit_lokasi']) : null,
            'nomor_identitas'             => isset($validated['nomor_identitas']) ? strip_tags($validated['nomor_identitas']) : null,
            'tautan_eksternal'            => $validated['tautan_eksternal'] ?? null,
            'file_bukti'                  => $filePath,
        ]);

        return redirect()->route('dosen.tridharma.index')
            ->with('success', 'Rekam jejak Tridharma berhasil disimpan dan disinkronkan ke Kalkulator BKD serta Profil Publik Anda.');
    }

    /**
     * Menghapus item Tridharma milik dosen bersangkutan.
     */
    public function destroy(DosenTridharma $tridharma): RedirectResponse
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen || (int) $tridharma->dosen_id !== (int) $dosen->id) {
            abort(403, 'Anda tidak berhak menghapus data portofolio ini.');
        }

        if ($tridharma->file_bukti && Storage::disk('public')->exists($tridharma->file_bukti)) {
            Storage::disk('public')->delete($tridharma->file_bukti);
        }

        $tridharma->delete();

        return redirect()->route('dosen.tridharma.index')
            ->with('success', 'Item portofolio Tridharma berhasil dihapus.');
    }

    /**
     * Cetak Laporan Beban Kerja Dosen (BKD) & CV Akademik Standar SISTER (PDF).
     */
    public function cetakCvBkd()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403);
        }

        $tahunAkademik = TahunAkademik::where('is_active', true)->first();

        $pivotMkIds = DB::table('dosen_mata_kuliah')
            ->where('dosen_id', $dosen->id)
            ->pluck('mata_kuliah_id')
            ->toArray();

        $mataKuliahs = MataKuliah::where(function ($q) use ($dosen, $pivotMkIds) {
                $q->where('dosen_id', $dosen->id)
                  ->orWhereIn('id', $pivotMkIds);
            })
            ->whereHas('kurikulum', fn($k) => $k->where('is_active', true))
            ->orderBy('semester')
            ->get();

        $penelitians = $dosen->penelitians()->get();
        $pengabdians = $dosen->pengabdians()->get();
        $penunjangs  = $dosen->penunjangs()->get();
        $suratKeputusans = $dosen->suratKeputusans()->where('status', 'Selesai')->latest()->get();
        $ringkasanBkd = $dosen->hitungRingkasanBkd();

        $pdf = Pdf::loadView('dosen.tridharma.pdf_bkd_cv', compact(
            'dosen',
            'tahunAkademik',
            'mataKuliahs',
            'penelitians',
            'pengabdians',
            'penunjangs',
            'suratKeputusans',
            'ringkasanBkd'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_BKD_dan_CV_Akademik_' . str_replace(' ', '_', $dosen->nama_lengkap) . '.pdf');
    }
}