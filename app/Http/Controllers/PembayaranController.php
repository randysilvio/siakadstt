<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // [PENTING] Pastikan baris ini ada
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
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
        $query = Pembayaran::with(['mahasiswa', 'user'])
                    ->orderBy('created_at', 'desc');

        // 1. Filter Tipe User
        if ($request->filled('tipe_user')) {
            if ($request->tipe_user == 'mahasiswa') {
                $query->whereNotNull('mahasiswa_id');
            } elseif ($request->tipe_user == 'camaba') {
                $query->whereNull('mahasiswa_id');
            }
        }

        // 2. Pencarian Cerdas
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($sub) use ($q) {
                $sub->whereHas('mahasiswa', function ($m) use ($q) {
                    $m->where('nama_lengkap', 'like', "%{$q}%")
                      ->orWhere('nim', 'like', "%{$q}%");
                })
                ->orWhereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%");
                });
            });
        }

        // 3. Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 4. Filter Semester
        if ($request->filled('semester')) {
            $query->where(function($sub) use ($request) {
                $sub->where('semester', 'like', '%' . $request->semester . '%')
                    ->orWhere('keterangan', 'like', '%' . $request->semester . '%')
                    ->orWhere('jenis_pembayaran', 'like', '%' . $request->semester . '%');
            });
        }

        $pembayarans = $query->paginate(20)->withQueryString();
        
        return view('pembayaran.index', compact('pembayarans'));
    }

    /**
     * Form buat tagihan manual.
     */
    public function create(): View
    {
        $mahasiswas = Mahasiswa::with('programStudi')->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
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
            'jenis_pembayaran' => 'required|string'
        ]);

        $validatedData['status'] = 'belum_lunas'; 
        
        $mhs = Mahasiswa::find($request->mahasiswa_id);
        $validatedData['user_id'] = $mhs->user_id;

        // Cek Duplikasi
        $exists = Pembayaran::where('mahasiswa_id', $request->mahasiswa_id)
                            ->where('semester', $request->semester)
                            ->where('jenis_pembayaran', $request->jenis_pembayaran)
                            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Tagihan untuk mahasiswa ini di semester tersebut sudah ada!');
        }

        Pembayaran::create($validatedData);
        return redirect()->route('pembayaran.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    /**
     * Edit Tagihan.
     */
    public function edit(Pembayaran $pembayaran): View
    {
        return view('pembayaran.edit', compact('pembayaran'));
    }

    /**
     * Update Tagihan.
     */
    public function update(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $request->validate([
            'jumlah' => 'required|integer|min:0',
            'semester' => 'required|string|max:50',
            'status' => 'required|in:lunas,belum_lunas,menunggu_konfirmasi',
            'keterangan' => 'nullable|string',
        ]);
        
        $pembayaran->update([
            'jumlah' => $request->jumlah,
            'semester' => $request->semester,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
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
     * Proses Generate Massal (DIPERBAIKI & AMAN).
     */
    public function storeGenerate(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $request->validate([
            'semester' => 'required|string|max:50',
            'jumlah' => 'required|integer|min:1',
            'jenis_pembayaran' => 'required|string',
            'prodi_id' => 'nullable|exists:program_studis,id',
            'angkatan' => 'nullable|integer',
        ]);

        // 2. Query Mahasiswa Aktif
        $query = Mahasiswa::where('status', 'Aktif');
        if ($request->filled('prodi_id')) $query->where('program_studi_id', $request->prodi_id);
        if ($request->filled('angkatan')) $query->where('tahun_masuk', $request->angkatan);
        
        $mahasiswas = $query->get();

        if ($mahasiswas->isEmpty()) {
            return back()->with('error', 'Tidak ditemukan mahasiswa aktif untuk kriteria ini.');
        }

        // 3. Proses Generate dengan Transaction (Agar aman jika error di tengah)
        DB::beginTransaction();
        try {
            $count = 0;
            $keteranganOtomatis = ucwords(str_replace('_', ' ', $request->jenis_pembayaran)) . ' - ' . $request->semester;

            foreach ($mahasiswas as $mhs) {
                // Safety Check: Lewati jika data user rusak (tidak punya user_id)
                if (!$mhs->user_id) {
                    continue; 
                }

                // Cek Duplikasi agar tidak double
                $exists = Pembayaran::where('mahasiswa_id', $mhs->id)
                                    ->where('semester', $request->semester)
                                    ->where('jenis_pembayaran', $request->jenis_pembayaran)
                                    ->exists();
                
                if (!$exists) {
                    Pembayaran::create([
                        'user_id' => $mhs->user_id,
                        'mahasiswa_id' => $mhs->id,
                        'semester' => $request->semester,
                        'jenis_pembayaran' => $request->jenis_pembayaran,
                        'jumlah' => $request->jumlah,
                        'status' => 'belum_lunas',
                        'keterangan' => $keteranganOtomatis,
                    ]);
                    $count++;
                }
            }

            DB::commit();
            return redirect()->route('pembayaran.index')->with('success', "Generate Selesai! $count tagihan berhasil dibuat.");

        } catch (\Exception $e) {
            DB::rollBack();
            // Tampilkan pesan error spesifik untuk debugging
            return back()->with('error', 'Gagal generate: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Laporan PDF.
     */
    public function cetakLaporan(Request $request)
    {
        $query = Pembayaran::with(['mahasiswa', 'user'])->orderBy('created_at', 'desc');

        if ($request->filled('tipe_user')) {
            if ($request->tipe_user == 'mahasiswa') $query->whereNotNull('mahasiswa_id');
            elseif ($request->tipe_user == 'camaba') $query->whereNull('mahasiswa_id');
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($sub) use ($q) {
                $sub->whereHas('mahasiswa', function ($m) use ($q) {
                    $m->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nim', 'like', "%{$q}%");
                })->orWhereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%");
                });
            });
        }

        if ($request->filled('status')) $query->where('status', $request->input('status'));
        
        if ($request->filled('semester')) {
            $query->where(function($sub) use ($request) {
                $sub->where('semester', 'like', '%' . $request->semester . '%')
                    ->orWhere('keterangan', 'like', '%' . $request->semester . '%')
                    ->orWhere('jenis_pembayaran', 'like', '%' . $request->semester . '%');
            });
        }

        $pembayarans = $query->get();
        
        $filterInfo = [
            'status' => $request->status ? ucfirst(str_replace('_', ' ', $request->status)) : 'Semua Status',
            'semester' => $request->semester ?? 'Semua Semester',
            'tipe_user' => $request->tipe_user ? ucfirst($request->tipe_user) : 'Semua User',
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
        return back()->with('success', 'Pembayaran diverifikasi LUNAS.');
    }
    
    /**
     * Hapus Tagihan.
     */
    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->bukti_bayar) {
            Storage::disk('public')->delete($pembayaran->bukti_bayar);
        }
        $pembayaran->delete();
        return back()->with('success', 'Tagihan dihapus.');
    }

    /**
     * Halaman Riwayat (KHUSUS MAHASISWA).
     */
    public function riwayat(): View
    {
        $user = Auth::user();
        if (!$user->mahasiswa) abort(403, 'Akses ditolak.');
        
        $pembayarans = Pembayaran::where('user_id', $user->id)
                        ->orWhere('mahasiswa_id', $user->mahasiswa->id)
                        ->latest()
                        ->get();
                        
        return view('pembayaran.riwayat', compact('pembayarans'));
    }
}