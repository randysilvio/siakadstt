<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class PembayaranController extends Controller
{
    /**
     * KONSTRUKTOR: Kunci Akses
     * HANYA role 'keuangan' yang boleh akses Controller ini.
     * KECUALI method 'riwayat' yang diizinkan untuk 'mahasiswa'.
     */
    public function __construct()
    {
        $this->middleware('role:keuangan')->except(['riwayat']);
    }

    /**
     * Menampilkan daftar tagihan dengan Smart Filter.
     */
    public function index(Request $request): View
    {
        $query = Pembayaran::with('mahasiswa')->orderBy('created_at', 'desc');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->whereHas('mahasiswa', function ($subQuery) use ($q) {
                $subQuery->where('nama_lengkap', 'like', "%{$q}%")
                         ->orWhere('nim', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('semester')) {
            $query->where('semester', 'like', '%' . $request->input('semester') . '%');
        }

        $pembayarans = $query->paginate(10)->withQueryString();
        return view('pembayaran.index', compact('pembayarans'));
    }

    /**
     * Form buat tagihan manual.
     */
    public function create(): View
    {
        $mahasiswas = Mahasiswa::with('programStudi')->orderBy('nama_lengkap')->get();
        $prodis = ProgramStudi::all();
        $angkatans = Mahasiswa::select('tahun_masuk')
                        ->distinct()
                        ->whereNotNull('tahun_masuk')
                        ->orderBy('tahun_masuk', 'desc')
                        ->pluck('tahun_masuk');

        return view('pembayaran.create', compact('mahasiswas', 'prodis', 'angkatans'));
    }

    /**
     * Simpan tagihan manual.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'jumlah' => 'required|integer|min:1',
            'semester' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        $validatedData['status'] = 'belum_lunas'; 

        $exists = Pembayaran::where('mahasiswa_id', $request->mahasiswa_id)
                            ->where('semester', $request->semester)
                            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Tagihan untuk mahasiswa ini di semester tersebut sudah ada!');
        }

        Pembayaran::create($validatedData);
        return redirect()->route('pembayaran.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    /**
     * Tampilkan form edit pembayaran (UNTUK CICILAN/REVISI).
     */
    public function edit(Pembayaran $pembayaran): View
    {
        return view('pembayaran.edit', compact('pembayaran'));
    }

    /**
     * Update data pembayaran.
     */
    public function update(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $request->validate([
            'jumlah' => 'required|integer|min:0',
            'semester' => 'required|string|max:50',
            'status' => 'required|in:lunas,belum_lunas',
            'keterangan' => 'nullable|string',
        ]);
        
        $pembayaran->update([
            'jumlah' => $request->jumlah,
            'semester' => $request->semester,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            // Jika status diubah jadi lunas dan belum ada tanggal bayar, set sekarang
            'tanggal_bayar' => ($request->status == 'lunas' && !$pembayaran->tanggal_bayar) ? now() : $pembayaran->tanggal_bayar
        ]);

        return redirect()->route('pembayaran.index')->with('success', 'Data tagihan berhasil diperbarui.');
    }

    /**
     * Halaman Generate Massal.
     */
    public function generate(): View
    {
        $prodis = ProgramStudi::all();
        $angkatans = Mahasiswa::select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk');
        return view('pembayaran.generate', compact('prodis', 'angkatans'));
    }

    /**
     * Proses Generate Massal.
     */
    public function storeGenerate(Request $request): RedirectResponse
    {
        $request->validate([
            'semester' => 'required|string|max:50',
            'jumlah' => 'required|integer|min:1',
            'prodi_id' => 'nullable|exists:program_studis,id',
            'angkatan' => 'nullable|integer',
        ]);

        $query = Mahasiswa::query();
        if ($request->filled('prodi_id')) $query->where('program_studi_id', $request->prodi_id);
        if ($request->filled('angkatan')) $query->where('tahun_masuk', $request->angkatan);
        
        $mahasiswas = $query->get();

        if ($mahasiswas->isEmpty()) return back()->with('error', 'Tidak ditemukan mahasiswa.');

        $count = 0;
        foreach ($mahasiswas as $mhs) {
            $exists = Pembayaran::where('mahasiswa_id', $mhs->id)->where('semester', $request->semester)->exists();
            if (!$exists) {
                Pembayaran::create([
                    'mahasiswa_id' => $mhs->id,
                    'semester' => $request->semester,
                    'jumlah' => $request->jumlah,
                    'status' => 'belum_lunas',
                    'keterangan' => 'Tagihan Otomatis ' . $request->semester,
                ]);
                $count++;
            }
        }
        return redirect()->route('pembayaran.index')->with('success', "Generate Selesai! $count tagihan dibuat.");
    }

    /**
     * Cetak Laporan PDF.
     */
    public function cetakLaporan(Request $request)
    {
        $query = Pembayaran::with('mahasiswa')->orderBy('created_at', 'desc');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->whereHas('mahasiswa', function ($subQuery) use ($q) {
                $subQuery->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nim', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        if ($request->filled('semester')) $query->where('semester', 'like', '%' . $request->input('semester') . '%');

        $pembayarans = $query->get();
        $filterInfo = [
            'status' => $request->status ? ucfirst(str_replace('_', ' ', $request->status)) : 'Semua Status',
            'semester' => $request->semester ?? 'Semua Semester',
            'tanggal_cetak' => now()->isoFormat('D MMMM Y (HH:mm)'),
            'pencetak' => Auth::user()->name
        ];

        $pdf = Pdf::loadView('pembayaran.cetak_laporan', compact('pembayarans', 'filterInfo'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('Laporan_Keuangan.pdf');
    }

    /**
     * Aksi Tandai Lunas.
     */
    public function tandaiLunas(Pembayaran $pembayaran): RedirectResponse
    {
        $pembayaran->update(['status' => 'lunas', 'tanggal_bayar' => now()]);
        return redirect()->route('pembayaran.index')->with('success', 'Tagihan lunas.');
    }
    
    /**
     * Hapus Tagihan.
     */
    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        $pembayaran->delete();
        return redirect()->route('pembayaran.index')->with('success', 'Tagihan dihapus.');
    }

    /**
     * Halaman Riwayat (KHUSUS MAHASISWA).
     */
    public function riwayat(): View
    {
        $user = Auth::user();
        if (!$user->mahasiswa) abort(403, 'Akses ditolak.');
        $pembayarans = $user->mahasiswa->pembayarans()->latest()->get();
        return view('pembayaran.riwayat', compact('pembayarans'));
    }
}