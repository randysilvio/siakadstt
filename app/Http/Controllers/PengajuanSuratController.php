<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\TahunAkademik;
use App\Notifications\GeneralNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PengajuanSuratController extends Controller
{
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

    public function updateStatus(Request $request, PengajuanSurat $pengajuanSurat): RedirectResponse
    {
        $validated = $request->validate([
            'status'        => 'required|in:Menunggu Verifikasi,Disetujui,Ditolak',
            'nomor_surat'   => 'nullable|string|max:120',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $nomorSurat = $validated['nomor_surat'] ?? $pengajuanSurat->nomor_surat;
        if ($validated['status'] === 'Disetujui' && empty($nomorSurat)) {
            $urutan = str_pad((string) $pengajuanSurat->id, 3, '0', STR_PAD_LEFT);
            $nomorSurat = "{$urutan}/BAAK-SRT/STT-GPI/" . date('m/Y');
        }

        // Tanggal surat = saat pertama kali disetujui; tidak berubah bila status disimpan ulang
        $tanggalDisetujui = $validated['status'] === 'Disetujui'
            ? ($pengajuanSurat->status === 'Disetujui' && $pengajuanSurat->tanggal_disetujui ? $pengajuanSurat->tanggal_disetujui : now())
            : null;

        $pengajuanSurat->update([
            'status'            => $validated['status'],
            'nomor_surat'       => $nomorSurat,
            'catatan_admin'     => isset($validated['catatan_admin']) ? strip_tags($validated['catatan_admin']) : null,
            'tanggal_disetujui' => $tanggalDisetujui,
        ]);

        // Kirim notifikasi ke mahasiswa terkait
        if ($pengajuanSurat->mahasiswa && $pengajuanSurat->mahasiswa->user && in_array($validated['status'], ['Disetujui', 'Ditolak'])) {
            $icon = $validated['status'] === 'Disetujui' ? 'bi-envelope-check-fill text-success' : 'bi-envelope-x-fill text-danger';
            $pesan = 'Permohonan ' . $pengajuanSurat->jenis_surat . ' Anda telah ' . strtoupper($validated['status']) . '.'
                . ($pengajuanSurat->catatan_admin ? ' Catatan BAAK: ' . $pengajuanSurat->catatan_admin : '');

            $pengajuanSurat->mahasiswa->user->notify(new GeneralNotification(
                'Status Surat: ' . $validated['status'],
                $pesan,
                route('surat-mandiri.index'),
                $icon
            ));
        }

        return back()->with('success', 'Status permohonan surat mahasiswa ' . ($pengajuanSurat->mahasiswa->nama_lengkap ?? '') . ' berhasil diperbarui.');
    }

    public function cetakPdf(PengajuanSurat $pengajuanSurat)
    {
        $user = Auth::user();
        $isAdminOrStaf = $user->hasRole('admin') || $user->hasRole('administrasi_umum');
        $isPemilik = $user->mahasiswa && ((int) $user->mahasiswa->id === (int) $pengajuanSurat->mahasiswa_id);

        if (!$isAdminOrStaf && !$isPemilik) {
            abort(403, 'Anda tidak berhak mencetak dokumen surat ini.');
        }

        if ($pengajuanSurat->status !== 'Disetujui') {
            return back()->with('error', 'Dokumen surat belum dapat dicetak karena masih berstatus: ' . $pengajuanSurat->status);
        }

        $mahasiswa = $pengajuanSurat->mahasiswa()->with('programStudi.kaprodi')->firstOrFail();
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        $pdf = Pdf::loadView('pengajuan_surat.pdf_surat', compact('pengajuanSurat', 'mahasiswa', 'periodeAktif'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Surat_' . Str::slug($pengajuanSurat->jenis_surat) . '_' . $mahasiswa->nim . '.pdf');
    }
}