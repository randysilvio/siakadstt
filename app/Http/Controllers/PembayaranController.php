<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\ProgramStudi;
use App\Services\PembayaranService;
use App\Support\BerkasPrivat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function __construct(private PembayaranService $keuangan)
    {
        // Hanya Staf Keuangan (termasuk dosen ber-otoritas keuangan) yang mengelola tagihan & verifikasi pembayaran.
        // Super Admin sengaja tidak diberi akses ke modul ini.
        $this->middleware('role:keuangan')->except(['riwayat', 'uploadBuktiMahasiswa']);
    }

    public function index(Request $request): View
    {
        $pembayarans = $this->keuangan->query($request->only(PembayaranService::FILTER))
            ->orderByRaw("CASE WHEN status = 'menunggu_konfirmasi' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('pembayaran.index', compact('pembayarans'));
    }

    public function create(): View
    {
        $mahasiswas = Mahasiswa::with('programStudi')->where('status_mahasiswa', 'Aktif')->orderBy('nama_lengkap')->get();

        return view('pembayaran.create', [
            'mahasiswas' => $mahasiswas,
            'prodis'     => ProgramStudi::orderBy('nama_prodi')->get(),
            'angkatans'  => $this->daftarAngkatan(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mahasiswa_id'     => 'required|exists:mahasiswas,id',
            'jumlah'           => 'required|integer|min:1',
            'semester'         => 'required|string|max:50',
            'keterangan'       => 'nullable|string',
            'jenis_pembayaran' => 'required|string',
        ]);

        if ($this->keuangan->sudahAda((int) $data['mahasiswa_id'], $data['semester'], $data['jenis_pembayaran'])) {
            return back()->withInput()->with('error', 'Tagihan untuk mahasiswa ini di semester tersebut sudah ada!');
        }

        Pembayaran::create($data + [
            'status'  => 'belum_lunas',
            'user_id' => Mahasiswa::whereKey($data['mahasiswa_id'])->value('user_id'),
        ]);

        return redirect()->route('pembayaran.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    public function edit(Pembayaran $pembayaran): View
    {
        return view('pembayaran.edit', compact('pembayaran'));
    }

    public function update(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $data = $request->validate([
            'jumlah'     => 'required|integer|min:0',
            'semester'   => 'required|string|max:50',
            'status'     => 'required|in:lunas,belum_lunas,menunggu_konfirmasi,dispensasi',
            'keterangan' => 'nullable|string',
        ]);

        $data['tanggal_bayar'] = ($data['status'] === 'lunas' && !$pembayaran->tanggal_bayar) ? now() : $pembayaran->tanggal_bayar;
        $pembayaran->update($data);

        return redirect()->route('pembayaran.index')->with('success', 'Data tagihan berhasil diperbarui.');
    }

    public function generate(): View
    {
        return view('pembayaran.generate', [
            'prodis'    => ProgramStudi::orderBy('nama_prodi')->get(),
            'angkatans' => $this->daftarAngkatan(),
        ]);
    }

    public function storeGenerate(Request $request): RedirectResponse
    {
        $request->validate([
            'semester'         => 'required|string|max:50',
            'jumlah'           => 'required|integer|min:1',
            'jenis_pembayaran' => 'required|string',
            'prodi_id'         => 'nullable|exists:program_studis,id',
            'angkatan'         => 'nullable|integer',
        ]);

        $mahasiswas = Mahasiswa::where('status_mahasiswa', 'Aktif')
            ->when($request->filled('prodi_id'), fn ($q) => $q->where('program_studi_id', $request->prodi_id))
            ->when($request->filled('angkatan'), fn ($q) => $q->where('tahun_masuk', $request->angkatan))
            ->get();

        if ($mahasiswas->isEmpty()) {
            return back()->withInput()->with('error', 'Tidak ditemukan mahasiswa aktif untuk kriteria ini.');
        }

        try {
            $count = $this->keuangan->generateMassal($mahasiswas, $request->semester, $request->jenis_pembayaran, (int) $request->jumlah);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Gagal generate tagihan massal. Detail kesalahan telah dicatat di log sistem.');
        }

        return redirect()->route('pembayaran.index')->with('success', "Generate Selesai! {$count} tagihan baru berhasil dibuat.");
    }

    public function cetakLaporan(Request $request)
    {
        $pembayarans = $this->keuangan->query($request->only(PembayaranService::FILTER))->orderBy('created_at', 'desc')->get();

        $filterInfo = [
            'status'        => $request->status ? ucfirst(str_replace('_', ' ', $request->status)) : 'Semua Status',
            'semester'      => $request->semester ?? 'Semua Semester',
            'tipe_user'     => $request->tipe_user ? ucfirst($request->tipe_user) : 'Semua User',
            'tanggal_cetak' => now()->isoFormat('D MMMM Y (HH:mm)'),
            'pencetak'      => Auth::user()->name,
        ];

        return Pdf::loadView('pembayaran.cetak_laporan', compact('pembayarans', 'filterInfo'))
            ->setPaper('a4', 'landscape')
            ->stream('Laporan_Keuangan.pdf');
    }

    public function tandaiLunas(Pembayaran $pembayaran): RedirectResponse
    {
        $this->keuangan->tandaiLunas($pembayaran);

        return back()->with('success', 'Pembayaran berhasil diverifikasi LUNAS.');
    }

    /**
     * Menolak bukti bayar yang tidak valid: status kembali "belum_lunas" sehingga gerbang KRS tetap terkunci,
     * dan pembayar diberi tahu alasannya agar dapat mengunggah ulang.
     */
    public function tolakBukti(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Hanya tagihan berstatus menunggu konfirmasi yang dapat ditolak buktinya.');
        }

        $request->validate(['alasan' => 'nullable|string|max:255']);
        $alasan = trim(strip_tags((string) $request->input('alasan'))) ?: 'Bukti transfer tidak dapat diverifikasi';

        $this->keuangan->tolakBukti($pembayaran, $alasan);

        return back()->with('success', 'Bukti pembayaran ditolak. Tagihan dikembalikan ke status BELUM LUNAS.');
    }

    public function tandaiDispensasi(Pembayaran $pembayaran): RedirectResponse
    {
        $this->keuangan->beriDispensasi($pembayaran);

        return back()->with('success', 'Status tagihan diubah menjadi DISPENSASI / CICILAN. Kunci KRS mahasiswa telah dibuka.');
    }

    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        BerkasPrivat::hapus($pembayaran->bukti_bayar);
        $pembayaran->delete();

        return back()->with('success', 'Tagihan berhasil dihapus.');
    }

    public function riwayat(): View
    {
        $mahasiswa = Auth::user()->mahasiswa ?? abort(403, 'Akses ditolak.');

        $pembayarans = Pembayaran::where(fn ($q) => $q->where('user_id', Auth::id())->orWhere('mahasiswa_id', $mahasiswa->id))
            ->latest()
            ->get();

        return view('pembayaran.riwayat', compact('pembayarans'));
    }

    public function uploadBuktiMahasiswa(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $mahasiswa = Auth::user()->mahasiswa ?? abort(403, 'Akses ditolak.');

        if ((int) $pembayaran->user_id !== (int) Auth::id() && (int) $pembayaran->mahasiswa_id !== (int) $mahasiswa->id) {
            abort(403, 'Anda tidak berhak mengunggah bukti untuk tagihan ini.');
        }

        if ($pembayaran->status === 'lunas') {
            return back()->with('error', 'Tagihan ini sudah diverifikasi lunas.');
        }

        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'bukti_bayar.required' => 'Silakan pilih file foto/screenshot bukti transfer.',
            'bukti_bayar.image'    => 'File bukti transfer harus berupa gambar (JPG/PNG).',
            'bukti_bayar.max'      => 'Ukuran file bukti transfer maksimal 2MB.',
        ]);

        BerkasPrivat::hapus($pembayaran->bukti_bayar);
        $pembayaran->update([
            'bukti_bayar'   => BerkasPrivat::simpan($request->file('bukti_bayar'), 'bukti-bayar-spp'),
            'status'        => 'menunggu_konfirmasi',
            'tanggal_bayar' => now(),
        ]);

        return redirect()->route('pembayaran.riwayat')->with('success', 'Bukti pembayaran berhasil diunggah! Mohon tunggu verifikasi dari Bagian Keuangan.');
    }

    private function daftarAngkatan()
    {
        return Mahasiswa::select('tahun_masuk')->distinct()->whereNotNull('tahun_masuk')->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk');
    }
}
