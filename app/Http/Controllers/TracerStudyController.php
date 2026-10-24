<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TracerStudy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TracerStudyController extends Controller
{
    /**
     * Halaman Pengisian Kuesioner Tracer Study khusus bagi Alumni (Status Lulus).
     */
    public function index(): View | RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa) {
            abort(403, 'Halaman ini khusus untuk Alumni.');
        }

        $mahasiswa = $user->mahasiswa;

        if ($mahasiswa->status_mahasiswa !== 'Lulus') {
            return redirect()->route('dashboard')
                ->with('error', 'Menu Tracer Study hanya diperuntukkan bagi mahasiswa yang telah dinyatakan Lulus (Alumni).');
        }

        $tracer = TracerStudy::where('mahasiswa_id', $mahasiswa->id)->first();

        return view('tracer_study.index', compact('mahasiswa', 'tracer'));
    }

    /**
     * Menyimpan atau memperbarui data Tracer Study Alumni.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa) {
            abort(403);
        }

        $mahasiswa = $user->mahasiswa;

        if ($mahasiswa->status_mahasiswa !== 'Lulus') {
            abort(403, 'Hanya Alumni yang dapat mengisi Tracer Study.');
        }

        $validated = $request->validate([
            'tahun_lulus'           => 'required|digits:4|integer|min:1990|max:' . (date('Y') + 1),
            'status_pekerjaan'      => 'required|string|max:100',
            'waktu_tunggu_bulan'    => 'required|integer|min:0|max:120',
            'nama_instansi_gereja'  => 'nullable|string|max:255',
            'jabatan_pekerjaan'     => 'nullable|string|max:150',
            'kesesuaian_bidang'     => 'required|in:Sangat Sesuai,Sesuai,Kurang Sesuai,Tidak Sesuai',
            'kisaran_pendapatan'    => 'nullable|string|max:100',
            'kontak_atasan_nama'    => 'nullable|string|max:150',
            'kontak_atasan_jabatan' => 'nullable|string|max:150',
            'kontak_atasan_hp'      => 'nullable|string|max:30',
            'saran_kurikulum'       => 'nullable|string|max:2000',
        ]);

        TracerStudy::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id],
            [
                'tahun_lulus'           => $validated['tahun_lulus'],
                'status_pekerjaan'      => strip_tags($validated['status_pekerjaan']),
                'waktu_tunggu_bulan'    => $validated['waktu_tunggu_bulan'],
                'nama_instansi_gereja'  => isset($validated['nama_instansi_gereja']) ? strip_tags($validated['nama_instansi_gereja']) : null,
                'jabatan_pekerjaan'     => isset($validated['jabatan_pekerjaan']) ? strip_tags($validated['jabatan_pekerjaan']) : null,
                'kesesuaian_bidang'     => $validated['kesesuaian_bidang'],
                'kisaran_pendapatan'    => $validated['kisaran_pendapatan'] ?? null,
                'kontak_atasan_nama'    => isset($validated['kontak_atasan_nama']) ? strip_tags($validated['kontak_atasan_nama']) : null,
                'kontak_atasan_jabatan' => isset($validated['kontak_atasan_jabatan']) ? strip_tags($validated['kontak_atasan_jabatan']) : null,
                'kontak_atasan_hp'      => isset($validated['kontak_atasan_hp']) ? strip_tags($validated['kontak_atasan_hp']) : null,
                'saran_kurikulum'       => isset($validated['saran_kurikulum']) ? strip_tags($validated['saran_kurikulum']) : null,
            ]
        );

        return redirect()->route('tracer.index')
            ->with('success', 'Terima kasih! Data Tracer Study Anda telah tersimpan. Anda kini dapat mengakses dan mengunduh Transkrip Nilai Akhir.');
    }

    /**
     * Halaman Rekapitulasi Tracer Study untuk Admin & Penjaminan Mutu (UPM).
     */
    public function adminIndex(Request $request): View
    {
        $query = TracerStudy::with(['mahasiswa.programStudi'])->latest();

        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', $request->tahun_lulus);
        }

        if ($request->filled('program_studi_id')) {
            $prodiId = $request->program_studi_id;
            $query->whereHas('mahasiswa', fn($q) => $q->where('program_studi_id', $prodiId));
        }

        $tracers = $query->paginate(15)->withQueryString();
        $allTracers = (clone $query)->get();

        $totalResponden = $allTracers->count();
        $rataWaktuTunggu = $totalResponden > 0 ? round($allTracers->avg('waktu_tunggu_bulan'), 1) : 0;
        $persenSesuai = $totalResponden > 0
            ? round(($allTracers->whereIn('kesesuaian_bidang', ['Sangat Sesuai', 'Sesuai'])->count() / $totalResponden) * 100, 1)
            : 0;
        $persenBekerja = $totalResponden > 0
            ? round(($allTracers->where('status_pekerjaan', '!=', 'Belum Bekerja')->count() / $totalResponden) * 100, 1)
            : 0;

        $prodis = ProgramStudi::orderBy('nama_prodi')->get();
        $tahunLulusList = TracerStudy::select('tahun_lulus')->distinct()->orderBy('tahun_lulus', 'desc')->pluck('tahun_lulus');

        return view('tracer_study.admin_index', compact(
            'tracers',
            'totalResponden',
            'rataWaktuTunggu',
            'persenSesuai',
            'persenBekerja',
            'prodis',
            'tahunLulusList'
        ));
    }

    /**
     * Cetak Laporan PDF Tracer Study Alumni.
     */
    public function cetakPdf(Request $request)
    {
        $query = TracerStudy::with(['mahasiswa.programStudi'])->orderBy('tahun_lulus', 'desc');

        $judulLingkup = 'SELURUH PROGRAM STUDI (INSTITUSI)';
        if ($request->filled('program_studi_id')) {
            $prodi = ProgramStudi::findOrFail($request->program_studi_id);
            $query->whereHas('mahasiswa', fn($q) => $q->where('program_studi_id', $prodi->id));
            $judulLingkup = 'PROGRAM STUDI ' . strtoupper($prodi->nama_prodi);
        }

        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', $request->tahun_lulus);
        }

        $tracers = $query->get();
        $totalResponden = $tracers->count();
        $rataWaktuTunggu = $totalResponden > 0 ? round($tracers->avg('waktu_tunggu_bulan'), 1) : 0;
        $persenSesuai = $totalResponden > 0
            ? round(($tracers->whereIn('kesesuaian_bidang', ['Sangat Sesuai', 'Sesuai'])->count() / $totalResponden) * 100, 1)
            : 0;

        $pdf = Pdf::loadView('tracer_study.pdf_laporan', compact(
            'tracers',
            'judulLingkup',
            'totalResponden',
            'rataWaktuTunggu',
            'persenSesuai'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Tracer_Study_Alumni_' . date('Ymd') . '.pdf');
    }
}