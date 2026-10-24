<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\TahunAkademik;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PengajuanSuratController extends Controller
{
    /**
     * Halaman Daftar Pengajuan Surat Mandiri (Untuk Mahasiswa).
     */
    public function mahasiswaIndex(): View
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa) {
            abort(403, 'Halaman ini khusus untuk Mahasiswa.');
        }

        $mahasiswa = $user->mahasiswa;
        $pengajuans = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->paginate(10);

        return view('pengajuan_surat.mahasiswa_index', compact('mahasiswa', 'pengajuans'));
    }

    /**
     * Mahasiswa mengirim permohonan surat baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa) {
            abort(403);
        }

        $validated = $request->validate([
            'jenis_surat'     => 'required|string|max:120',
            'keperluan'       => 'required|string|max:500',
            'tujuan_instansi' => 'nullable|string|max:255',
        ]);

        $kodeUnik = 'SRT-' . $user->mahasiswa->nim . '-' . strtoupper(Str::random(8));

        PengajuanSurat::create([
            'mahasiswa_id'    => $user->mahasiswa->id,
            'jenis_surat'     => strip_tags($validated['jenis_surat']),
            'keperluan'       => strip_tags($validated['keperluan']),
            'tujuan_instansi' => isset($validated['tujuan_instansi']) ? strip_tags($validated['tujuan_instansi']) : null,
            'status'          => 'Menunggu Verifikasi',
            'kode_verifikasi' => $kodeUnik,
        ]);

        return back()->with('success', 'Permohonan surat berhasil dikirim ke Biro Administrasi Akademik (BAAK).');
    }

    /**
     * Halaman Manajemen Ticketing Permohonan Surat Mahasiswa (Untuk Admin & Administrasi Umum).
     */
    public function adminIndex(Request $request): View
    {
        $query = PengajuanSurat::with(['mahasiswa.programStudi'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('mahasiswa', function ($q) use ($s) {
                $q->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhere('nim', 'like', "%{$s}%");
            });
        }

        $pengajuans = $query->paginate(15)->withQueryString();

        return view('pengajuan_surat.admin_index', compact('pengajuans'));
    }

    /**
     * Admin / BAAK memvalidasi permohonan surat dan menerbitkan Nomor Surat (Stay-on-Page).
     */
    public function updateStatus(Request $request, PengajuanSurat $pengajuanSurat): RedirectResponse
    {
        $validated = $request->validate([
            'status'        => 'required|in:Menunggu Verifikasi,Disetujui,Ditolak',
            'nomor_surat'   => 'nullable|string|max:120',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $nomorSurat = $validated['nomor_surat'] ?? $pengajuanSurat->nomor_surat;
        if ($validated['status'] === 'Disetujui' && empty($nomorSurat)) {
            $nomorSurat = str_pad((string) $pengajuanSurat->id, 3, '0', STR_PAD_LEFT);
        }

        $pengajuanSurat->update([
            'status'            => $validated['status'],
            'nomor_surat'       => $nomorSurat,
            'catatan_admin'     => isset($validated['catatan_admin']) ? strip_tags($validated['catatan_admin']) : null,
            'tanggal_disetujui' => $validated['status'] === 'Disetujui' ? now() : null,
        ]);

        return back()->with('success', 'Status permohonan surat mahasiswa ' . ($pengajuanSurat->mahasiswa->nama_lengkap ?? '') . ' berhasil diperbarui.');
    }

    /**
     * Cetak PDF Surat Keterangan Resmi hasil pengajuan mandiri yang telah disetujui (Dilengkapi QR-Code Verifikasi).
     */
    public function cetakPdf(PengajuanSurat $pengajuanSurat)
    {
        $user = Auth::user();
        $isOwner = $user->mahasiswa && (int) $user->mahasiswa->id === (int) $pengajuanSurat->mahasiswa_id;
        $isStaff = $user->hasRole('admin') || $user->hasRole('administrasi_umum');

        if (!$isOwner && !$isStaff) {
            abort(403, 'Akses ditolak.');
        }

        if ($pengajuanSurat->status !== 'Disetujui') {
            return back()->with('error', 'Surat ini belum disetujui oleh bagian Administrasi Akademik.');
        }

        $mahasiswa = $pengajuanSurat->mahasiswa->load('programStudi');
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        $pdf = Pdf::loadView('pengajuan_surat.pdf_surat', compact('pengajuanSurat', 'mahasiswa', 'periodeAktif'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Resmi_' . $mahasiswa->nim . '_' . $pengajuanSurat->id . '.pdf');
    }
}