<?php

namespace App\Http\Controllers;

use App\Support\BerkasPrivat;
use App\Models\SuratKeputusan;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SuratKeputusanController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,administrasi_umum')->except('download');
    }

    public function index(Request $request)
    {
        $query = SuratKeputusan::with('dosens')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis_surat')) {
            $query->where('jenis_surat', $request->jenis_surat);
        }

        $suratKeputusans = $query->paginate(20)->withQueryString();

        $metrics = [
            'total'    => SuratKeputusan::count(),
            'selesai'  => SuratKeputusan::where('status', 'Selesai')->count(),
            'menunggu' => SuratKeputusan::where('status', 'Menunggu Tanda Tangan')->count(),
            'draf'     => SuratKeputusan::where('status', 'Draf')->count(),
        ];

        return view('administrasi.surat.index', compact('suratKeputusans', 'metrics'));
    }

    public function create()
    {
        $dosens = Dosen::orderBy('nama_lengkap', 'asc')->get();
        return view('administrasi.surat.create', compact('dosens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_surat'    => 'required|string',
            'judul'          => 'required|string',
            'tanggal_terbit' => 'nullable|date',
            'nomor_surat'    => 'nullable|string|max:255|unique:surat_keputusans,nomor_surat',
        ], ['nomor_surat.unique' => 'Nomor surat ini sudah dipakai SK lain. Setiap SK wajib bernomor unik.']);

        DB::transaction(function () use ($request) {
            $panitiaLainnya = [];
            if ($request->has('panitia_lainnya_nama')) {
                foreach ($request->panitia_lainnya_nama as $key => $namaLain) {
                    if (!empty($namaLain)) {
                        $panitiaLainnya[] = [
                            'nama'    => $namaLain,
                            'jabatan' => $request->panitia_lainnya_jabatan[$key] ?? 'Anggota'
                        ];
                    }
                }
            }

            $surat = SuratKeputusan::create([
                'jenis_surat'           => $request->jenis_surat,
                'nomor_surat'           => $request->nomor_surat,
                'judul'                 => $request->judul,
                'tanggal_terbit'        => $request->tanggal_terbit,
                'menimbang'             => array_filter($request->menimbang ?? []),
                'mengingat'             => array_filter($request->mengingat ?? []),
                'memperhatikan'         => array_filter($request->memperhatikan ?? []),
                'menetapkan'            => array_filter($request->menetapkan ?? []),
                'isi_surat'             => $request->isi_surat,
                'tembusan'              => array_filter($request->tembusan ?? []),
                'penandatangan_jabatan' => $request->penandatangan_jabatan,
                'penandatangan_nama'    => $request->penandatangan_nama,
                'panitia_lainnya'       => $panitiaLainnya,
                'status'                => 'Menunggu Tanda Tangan'
            ]);

            if ($request->has('dosen_id')) {
                $dosenSync = [];
                foreach ($request->dosen_id as $key => $dosen_id) {
                    if (!empty($dosen_id)) {
                        $dosenSync[$dosen_id] = [
                            'jabatan_dalam_surat' => $request->jabatan_dalam_surat[$key] ?? 'Anggota'
                        ];
                    }
                }
                $surat->dosens()->sync($dosenSync);
            }
        });

        return redirect()->route('administrasi.surat-keputusan.index')->with('success', 'Draf Surat berhasil dibuat dan siap dicetak.');
    }

    public function show(SuratKeputusan $suratKeputusan)
    {
        $suratKeputusan->load('dosens');
        return view('administrasi.surat.show', compact('suratKeputusan'));
    }

    public function edit(SuratKeputusan $suratKeputusan)
    {
        $dosens = Dosen::orderBy('nama_lengkap', 'asc')->get();
        return view('administrasi.surat.edit', compact('suratKeputusan', 'dosens'));
    }

    public function update(Request $request, SuratKeputusan $suratKeputusan)
    {
        $request->validate([
            'jenis_surat'    => 'required|string',
            'judul'          => 'required|string',
            'tanggal_terbit' => 'nullable|date',
            'nomor_surat'    => 'nullable|string|max:255|unique:surat_keputusans,nomor_surat,' . $suratKeputusan->id,
        ], ['nomor_surat.unique' => 'Nomor surat ini sudah dipakai SK lain. Setiap SK wajib bernomor unik.']);

        DB::transaction(function () use ($request, $suratKeputusan) {
            $panitiaLainnya = [];
            if ($request->has('panitia_lainnya_nama')) {
                foreach ($request->panitia_lainnya_nama as $key => $namaLain) {
                    if (!empty($namaLain)) {
                        $panitiaLainnya[] = [
                            'nama'    => $namaLain,
                            'jabatan' => $request->panitia_lainnya_jabatan[$key] ?? 'Anggota'
                        ];
                    }
                }
            }

            $suratKeputusan->update([
                'jenis_surat'           => $request->jenis_surat,
                'nomor_surat'           => $request->nomor_surat,
                'judul'                 => $request->judul,
                'tanggal_terbit'        => $request->tanggal_terbit,
                'menimbang'             => array_filter($request->menimbang ?? []),
                'mengingat'             => array_filter($request->mengingat ?? []),
                'memperhatikan'         => array_filter($request->memperhatikan ?? []),
                'menetapkan'            => array_filter($request->menetapkan ?? []),
                'isi_surat'             => $request->isi_surat,
                'tembusan'              => array_filter($request->tembusan ?? []),
                'penandatangan_jabatan' => $request->penandatangan_jabatan,
                'penandatangan_nama'    => $request->penandatangan_nama,
                'panitia_lainnya'       => $panitiaLainnya,
            ]);

            if ($request->has('dosen_id')) {
                $dosenSync = [];
                foreach ($request->dosen_id as $key => $dosen_id) {
                    if (!empty($dosen_id)) {
                        $dosenSync[$dosen_id] = [
                            'jabatan_dalam_surat' => $request->jabatan_dalam_surat[$key] ?? 'Anggota'
                        ];
                    }
                }
                $suratKeputusan->dosens()->sync($dosenSync);
            } else {
                $suratKeputusan->dosens()->detach();
            }
        });

        return redirect()->route('administrasi.surat-keputusan.index')->with('success', 'Draf Surat berhasil diperbarui.');
    }

    public function duplicate(SuratKeputusan $suratKeputusan)
    {
        $suratKeputusan->load('dosens');

        $newSurat = DB::transaction(function () use ($suratKeputusan) {
            $clone = $suratKeputusan->replicate();
            $clone->nomor_surat = null; 
            $clone->tanggal_terbit = null; 
            $clone->status = 'Draf';
            $clone->file_path = null;
            $clone->save();

            // Salin juga relasi dosen panitia beserta jabatannya ke draf baru
            $dosenSync = [];
            foreach ($suratKeputusan->dosens as $dsn) {
                $dosenSync[$dsn->id] = [
                    'jabatan_dalam_surat' => $dsn->pivot->jabatan_dalam_surat ?? 'Anggota',
                ];
            }
            if (!empty($dosenSync)) {
                $clone->dosens()->sync($dosenSync);
            }

            return $clone;
        });
        
        return redirect()->route('administrasi.surat-keputusan.edit', $newSurat->id)
                         ->with('success', 'Template beserta susunan panitia berhasil diduplikasi. Silakan lengkapi nomor surat dan tanggal terbit baru.');
    }

    public function uploadFinal(Request $request, SuratKeputusan $suratKeputusan)
    {
        $request->validate([
            'file_pdf' => 'required|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('file_pdf')) {
            BerkasPrivat::hapus($suratKeputusan->file_path);
            $path = BerkasPrivat::simpan($request->file('file_pdf'), 'dokumen_sk');
            $suratKeputusan->update([
                'file_path' => $path,
                'status'    => 'Selesai'
            ]);
        }

        return back()->with('success', 'Dokumen final berhasil diunggah. Arsip kini tersedia di dasbor seluruh dosen yang bersangkutan.');
    }
    
    public function download(SuratKeputusan $suratKeputusan)
    {
        $user = Auth::user();

        $isAuthorizedStaff = $user->hasRole(['admin', 'administrasi_umum', 'rektorat', 'penjaminan_mutu']);
        $isTaggedDosen = false;

        if ($user->hasRole('dosen') && $user->dosen) {
            $isTaggedDosen = $suratKeputusan->status === 'Selesai' &&
                             $suratKeputusan->dosens()->where('dosens.id', $user->dosen->id)->exists();
        }

        if (!$isAuthorizedStaff && !$isTaggedDosen) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otorisasi untuk mengunduh dokumen Surat Keputusan ini.');
        }

        if ($disk = BerkasPrivat::diskUntuk($suratKeputusan->file_path)) {
            $safeName = \Illuminate\Support\Str::slug($suratKeputusan->judul ?? 'SURAT_SK', '_') . '.pdf';
            return Storage::disk($disk)->download($suratKeputusan->file_path, $safeName);
        }

        // Dibuka dari tab baru tidak punya halaman sebelumnya: arahkan ke halaman asal yang jelas
        $kembali = $isAuthorizedStaff && $user->hasRole(['admin', 'administrasi_umum'])
            ? route('administrasi.surat-keputusan.index')
            : ($isTaggedDosen ? route('dosen.tridharma.index') : route('dashboard'));

        return redirect()->to(url()->previous() !== url()->current() ? url()->previous() : $kembali)
            ->with('error', 'File final SK belum diunggah atau tidak ditemukan di server.');
    }

    public function destroy(SuratKeputusan $suratKeputusan)
    {
        BerkasPrivat::hapus($suratKeputusan->file_path);
        $suratKeputusan->delete();
        
        return back()->with('success', 'Dokumen surat berhasil dihapus secara permanen.');
    }
}