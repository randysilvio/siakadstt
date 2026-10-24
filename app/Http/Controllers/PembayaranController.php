<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PembayaranController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:keuangan')->except(['riwayat', 'uploadBuktiMahasiswa']);
    }

    public function index(Request $request): View
    {
        $query = Pembayaran::with(['mahasiswa', 'user'])
                    ->orderBy('created_at', 'desc');

        if ($request->filled('tipe_user')) {
            if ($request->tipe_user == 'mahasiswa') {
                $query->whereNotNull('mahasiswa_id');
            } elseif ($request->tipe_user == 'camaba') {
                $query->whereNull('mahasiswa_id');
            }
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('mahasiswa', function ($m) use ($q) {
                    $m->where('nama_lengkap', 'like', "%{$q}%")
                      ->orWhere('nim', 'like', "%{$q}%");
                })
                ->orWhereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%");
                });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'belum_lunas') {
                $query->whereIn('status', ['belum_lunas', 'belum_bayar']);
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->filled('semester')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('semester', 'like', '%' . $request->semester . '%')
                    ->orWhere('keterangan', 'like', '%' . $request->semester . '%')
                    ->orWhere('jenis_pembayaran', 'like', '%' . $request->semester . '%');
            });
        }

        $pembayarans = $query->paginate(20)->withQueryString();
        
        return view('pembayaran.index', compact('pembayarans'));
    }

    public function create(): View
    {
        $mahasiswas = Mahasiswa::with('programStudi')
                        ->where('status_mahasiswa', 'Aktif')
                        ->orderBy('nama_lengkap')
                        ->get();
        $prodis = ProgramStudi::orderBy('nama_prodi')->get();
        $angkatans = Mahasiswa::select('tahun_masuk')
                        ->distinct()
                        ->whereNotNull('tahun_masuk')
                        ->orderBy('tahun_masuk', 'desc')
                        ->pluck('tahun_masuk');

        return view('pembayaran.create', compact('mahasiswas', 'prodis', 'angkatans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'mahasiswa_id'     => 'required|exists:mahasiswas,id',
            'jumlah'           => 'required|integer|min:1',
            'semester'         => 'required|string|max:50',
            'keterangan'       => 'nullable|string',
            'jenis_pembayaran' => 'required|string',
        ]);

        $validatedData['status'] = 'belum_lunas'; 
        
        $mhs = Mahasiswa::findOrFail($request->mahasiswa_id);
        $validatedData['user_id'] = $mhs->user_id;

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

    public function edit(Pembayaran $pembayaran): View
    {
        return view('pembayaran.edit', compact('pembayaran'));
    }

    public function update(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $request->validate([
            'jumlah'     => 'required|integer|min:0',
            'semester'   => 'required|string|max:50',
            'status'     => 'required|in:lunas,belum_lunas,menunggu_konfirmasi,dispensasi',
            'keterangan' => 'nullable|string',
        ]);
        
        $pembayaran->update([
            'jumlah'        => $request->jumlah,
            'semester'      => $request->semester,
            'status'        => $request->status,
            'keterangan'    => $request->keterangan,
            'tanggal_bayar' => ($request->status == 'lunas' && !$pembayaran->tanggal_bayar) ? now() : $pembayaran->tanggal_bayar,
        ]);

        return redirect()->route('pembayaran.index')->with('success', 'Data tagihan berhasil diperbarui.');
    }

    public function generate(): View
    {
        $prodis = ProgramStudi::orderBy('nama_prodi')->get();
        $angkatans = Mahasiswa::select('tahun_masuk')
                        ->distinct()
                        ->whereNotNull('tahun_masuk')
                        ->orderBy('tahun_masuk', 'desc')
                        ->pluck('tahun_masuk');
        return view('pembayaran.generate', compact('prodis', 'angkatans'));
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

        $query = Mahasiswa::where('status_mahasiswa', 'Aktif');
        if ($request->filled('prodi_id')) {
            $query->where('program_studi_id', $request->prodi_id);
        }
        if ($request->filled('angkatan')) {
            $query->where('tahun_masuk', $request->angkatan);
        }
        
        $mahasiswas = $query->get();

        if ($mahasiswas->isEmpty()) {
            return back()->withInput()->with('error', 'Tidak ditemukan mahasiswa aktif untuk kriteria ini.');
        }

        DB::beginTransaction();
        try {
            $count = 0;
            $keteranganOtomatis = ucwords(str_replace('_', ' ', $request->jenis_pembayaran)) . ' - ' . $request->semester;

            foreach ($mahasiswas as $mhs) {
                if (!$mhs->user_id) {
                    continue; 
                }

                $exists = Pembayaran::where('mahasiswa_id', $mhs->id)
                                    ->where('semester', $request->semester)
                                    ->where('jenis_pembayaran', $request->jenis_pembayaran)
                                    ->exists();
                
                if (!$exists) {
                    Pembayaran::create([
                        'user_id'          => $mhs->user_id,
                        'mahasiswa_id'     => $mhs->id,
                        'semester'         => $request->semester,
                        'jenis_pembayaran' => $request->jenis_pembayaran,
                        'jumlah'           => $request->jumlah,
                        'status'           => 'belum_lunas',
                        'keterangan'       => $keteranganOtomatis,
                    ]);
                    $count++;
                }
            }

            DB::commit();
            return redirect()->route('pembayaran.index')->with('success', "Generate Selesai! {$count} tagihan baru berhasil dibuat.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal generate tagihan massal: ' . $e->getMessage());
        }
    }

    public function cetakLaporan(Request $request)
    {
        $query = Pembayaran::with(['mahasiswa', 'user'])->orderBy('created_at', 'desc');

        if ($request->filled('tipe_user')) {
            if ($request->tipe_user == 'mahasiswa') $query->whereNotNull('mahasiswa_id');
            elseif ($request->tipe_user == 'camaba') $query->whereNull('mahasiswa_id');
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('mahasiswa', function ($m) use ($q) {
                    $m->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nim', 'like', "%{$q}%");
                })->orWhereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%");
                });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'belum_lunas') {
                $query->whereIn('status', ['belum_lunas', 'belum_bayar']);
            } else {
                $query->where('status', $request->input('status'));
            }
        }
        
        if ($request->filled('semester')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('semester', 'like', '%' . $request->semester . '%')
                    ->orWhere('keterangan', 'like', '%' . $request->semester . '%')
                    ->orWhere('jenis_pembayaran', 'like', '%' . $request->semester . '%');
            });
        }

        $pembayarans = $query->get();
        
        $filterInfo = [
            'status'        => $request->status ? ucfirst(str_replace('_', ' ', $request->status)) : 'Semua Status',
            'semester'      => $request->semester ?? 'Semua Semester',
            'tipe_user'     => $request->tipe_user ? ucfirst($request->tipe_user) : 'Semua User',
            'tanggal_cetak' => now()->isoFormat('D MMMM Y (HH:mm)'),
            'pencetak'      => Auth::user()->name,
        ];

        $pdf = Pdf::loadView('pembayaran.cetak_laporan', compact('pembayarans', 'filterInfo'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('Laporan_Keuangan.pdf');
    }

    public function tandaiLunas(Pembayaran $pembayaran): RedirectResponse
    {
        $pembayaran->update([
            'status'        => 'lunas',
            'tanggal_bayar' => $pembayaran->tanggal_bayar ?? now(),
        ]);
        return back()->with('success', 'Pembayaran berhasil diverifikasi LUNAS.');
    }

    /**
     * [FITUR BARU FASE 7]: Memberikan status Dispensasi / Cicilan agar mahasiswa diizinkan mengisi KRS.
     */
    public function tandaiDispensasi(Pembayaran $pembayaran): RedirectResponse
    {
        $catatan = $pembayaran->keterangan;
        if (!str_contains(strtolower((string) $catatan), 'dispensasi')) {
            $catatan = trim(($catatan ? $catatan . ' | ' : '') . 'Dispensasi Cicilan KRS');
        }

        $pembayaran->update([
            'status'     => 'dispensasi',
            'keterangan' => $catatan,
        ]);

        return back()->with('success', 'Status tagihan diubah menjadi DISPENSASI / CICILAN. Kunci KRS mahasiswa telah dibuka.');
    }
    
    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->bukti_bayar && Storage::disk('public')->exists($pembayaran->bukti_bayar)) {
            Storage::disk('public')->delete($pembayaran->bukti_bayar);
        }
        $pembayaran->delete();
        return back()->with('success', 'Tagihan berhasil dihapus.');
    }

    public function riwayat(): View
    {
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Akses ditolak.');
        }
        
        $pembayarans = Pembayaran::where(function ($q) use ($user) {
                            $q->where('user_id', $user->id)
                              ->orWhere('mahasiswa_id', $user->mahasiswa->id);
                        })
                        ->latest()
                        ->get();
                        
        return view('pembayaran.riwayat', compact('pembayarans'));
    }

    public function uploadBuktiMahasiswa(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Akses ditolak.');
        }

        if ((int) $pembayaran->user_id !== (int) $user->id && (int) $pembayaran->mahasiswa_id !== (int) $user->mahasiswa->id) {
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

        if ($request->hasFile('bukti_bayar')) {
            if ($pembayaran->bukti_bayar && Storage::disk('public')->exists($pembayaran->bukti_bayar)) {
                Storage::disk('public')->delete($pembayaran->bukti_bayar);
            }

            $path = $request->file('bukti_bayar')->store('bukti-bayar-spp', 'public');
            $pembayaran->update([
                'bukti_bayar'   => $path,
                'status'        => 'menunggu_konfirmasi',
                'tanggal_bayar' => now(),
            ]);
        }

        return redirect()->route('pembayaran.riwayat')->with('success', 'Bukti pembayaran berhasil diunggah! Mohon tunggu verifikasi dari Bagian Keuangan.');
    }
}