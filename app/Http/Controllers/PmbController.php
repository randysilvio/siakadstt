<?php

namespace App\Http\Controllers;

use App\Models\Camaba;
use App\Models\ProgramStudi;
use App\Models\PmbDocument;
use App\Models\PmbPeriod;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PmbExport;

class PmbController extends Controller
{
    // ====================================================================
    // FUNGSI UNTUK ADMIN PMB (BACKEND)
    // ====================================================================

    /**
     * Menampilkan Halaman Daftar Pendaftar (Dengan Smart Filter)
     */
    public function index(Request $request)
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])->latest();

        // 1. Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQ) use ($search) {
                    $userQ->where('name', 'like', "%{$search}%");
                })->orWhere('no_pendaftaran', 'like', "%{$search}%");
            });
        }

        // 2. Filter Status Pendaftaran
        if ($request->filled('status')) {
            $query->where('status_pendaftaran', $request->status);
        }

        // 3. Filter Gelombang / Periode
        if ($request->filled('pmb_period_id')) {
            $query->where('pmb_period_id', $request->pmb_period_id);
        }

        // 4. Filter Program Studi Pilihan 1
        if ($request->filled('pilihan_prodi_1_id')) {
            $query->where('pilihan_prodi_1_id', $request->pilihan_prodi_1_id);
        }

        $pendaftars = $query->paginate(15)->withQueryString();
        $periods = PmbPeriod::orderBy('id', 'desc')->get();
        $prodis = ProgramStudi::orderBy('nama_prodi')->get();

        return view('admin.pmb.index', compact('pendaftars', 'periods', 'prodis'));
    }

    /**
     * Menampilkan Detail Pendaftar
     */
    public function show($id)
    {
        $camaba = Camaba::with(['user', 'prodi1', 'prodi2', 'period', 'documents'])->findOrFail($id);
        $tagihan = Pembayaran::where('user_id', $camaba->user_id)->where('jenis_pembayaran', 'formulir_pmb')->latest()->first();
        
        return view('admin.pmb.show', compact('camaba', 'tagihan'));
    }

    /**
     * Menghapus Data Pendaftar Secara Bersih (Cascade Delete)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $camaba = Camaba::findOrFail($id);
            $userId = $camaba->user_id;

            // 1. Hapus File Dokumen Fisik dari Storage
            foreach ($camaba->documents as $doc) {
                if (Storage::disk('public')->exists($doc->path_file)) {
                    Storage::disk('public')->delete($doc->path_file);
                }
                $doc->delete();
            }

            // 2. Hapus Riwayat Tagihan / Pembayaran
            Pembayaran::where('user_id', $userId)->where('jenis_pembayaran', 'formulir_pmb')->delete();

            // 3. Hapus Data Camaba
            $camaba->delete();

            // 4. Hapus Akun User
            User::find($userId)->delete();

            DB::commit();
            
            // PERBAIKAN: Menggunakan back() agar filter yang sedang aktif tidak hilang setelah menghapus data
            return back()->with('success', 'Data pendaftar (termasuk dokumen dan tagihan) berhasil dihapus bersih.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Ekspor Data ke Excel
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(new PmbExport($request->all()), 'Laporan_Pendaftar_PMB.xlsx');
    }

    /**
     * Ekspor/Cetak Data ke PDF (Mode Print Friendly)
     */
    public function exportPdf(Request $request)
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) $query->where('status_pendaftaran', $request->status);
        if ($request->filled('pmb_period_id')) $query->where('pmb_period_id', $request->pmb_period_id);
        if ($request->filled('pilihan_prodi_1_id')) $query->where('pilihan_prodi_1_id', $request->pilihan_prodi_1_id);

        $pendaftars = $query->get();
        return view('admin.pmb.cetak_pdf', compact('pendaftars'));
    }


    // ====================================================================
    // FUNGSI UNTUK MAHASISWA / CAMABA (FRONTEND)
    // ====================================================================

    public function showPaymentForm()
    {
        $user = Auth::user();
        $tagihan = $user->pembayarans()->where('jenis_pembayaran', 'formulir_pmb')->latest()->first();
        if (!$tagihan) return redirect()->route('dashboard')->with('error', 'Tagihan tidak ditemukan.');
        return view('pmb.pembayaran', compact('tagihan'));
    }

    public function storePaymentProof(Request $request) 
    {
        $request->validate([
            'bukti_bayar' => 'required|image|max:2048',
            'pembayaran_id' => 'required|exists:pembayarans,id'
        ]);

        $tagihan = Pembayaran::where('id', $request->pembayaran_id)->where('user_id', Auth::id())->firstOrFail();

        if ($request->hasFile('bukti_bayar')) {
            if ($tagihan->bukti_bayar) Storage::disk('public')->delete($tagihan->bukti_bayar);
            $path = $request->file('bukti_bayar')->store('bukti-bayar-pmb', 'public');
            $tagihan->update(['bukti_bayar' => $path, 'status' => 'menunggu_konfirmasi', 'tanggal_bayar' => now()]);
        }
        return redirect()->route('dashboard')->with('success', 'Bukti pembayaran berhasil diupload! Mohon tunggu verifikasi Admin Keuangan.');
    }

    public function showBiodataForm()
    {
        $user = Auth::user();
        if (!$user->hasRole('camaba')) return redirect()->route('dashboard');

        $camaba = $user->camaba;
        $lunas = $user->pembayarans()->where('jenis_pembayaran', 'formulir_pmb')->where('status', 'lunas')->exists();

        if (!$lunas) return redirect()->route('dashboard')->with('error', 'Silakan selesaikan pembayaran formulir terlebih dahulu.');

        $programStudis = ProgramStudi::all();
        $dokumen = $camaba->documents->pluck('path_file', 'jenis_dokumen');

        return view('pmb.form_biodata', compact('camaba', 'programStudis', 'dokumen'));
    }

    public function updateBiodata(Request $request)
    {
        $user = Auth::user();
        $camaba = $user->camaba;

        $request->validate([
            'tempat_lahir' => 'required|string', 'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P', 'agama' => 'required|string', 'alamat' => 'required|string',
            'sekolah_asal' => 'required|string', 'nisn' => 'required|numeric',
            'tahun_lulus' => 'required|numeric', 'nilai_rata_rata_rapor' => 'required|numeric|between:0,100',
            'pilihan_prodi_1_id' => 'required|exists:program_studis,id',
            'pilihan_prodi_2_id' => 'nullable|exists:program_studis,id|different:pilihan_prodi_1_id',
            'file_ijazah' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048', 
            'file_kk' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_pas_foto' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $camaba->update([
                'tempat_lahir' => $request->tempat_lahir, 'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin, 'agama' => $request->agama, 'alamat' => $request->alamat,
                'sekolah_asal' => $request->sekolah_asal, 'nisn' => $request->nisn,
                'tahun_lulus' => $request->tahun_lulus, 'nilai_rata_rata_rapor' => $request->nilai_rata_rata_rapor,
                'pilihan_prodi_1_id' => $request->pilihan_prodi_1_id, 'pilihan_prodi_2_id' => $request->pilihan_prodi_2_id,
                'status_pendaftaran' => 'menunggu_verifikasi',
            ]);

            $this->handleUpload($request, 'file_ijazah', 'Ijazah', $camaba);
            $this->handleUpload($request, 'file_kk', 'Kartu Keluarga', $camaba);
            $this->handleUpload($request, 'file_pas_foto', 'Pas Foto', $camaba);

            DB::commit();
            return redirect()->route('dashboard')->with('success', 'Biodata berhasil disimpan! Data Anda sedang diverifikasi oleh Panitia PMB.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    private function handleUpload($request, $inputName, $jenisDokumen, $camaba)
    {
        if ($request->hasFile($inputName)) {
            $existingDoc = PmbDocument::where('camaba_id', $camaba->id)->where('jenis_dokumen', $jenisDokumen)->first();
            if ($existingDoc) {
                Storage::disk('public')->delete($existingDoc->path_file);
                $existingDoc->delete();
            }
            $path = $request->file($inputName)->store('dokumen-pmb', 'public');
            PmbDocument::create([
                'camaba_id' => $camaba->id, 'jenis_dokumen' => $jenisDokumen, 'path_file' => $path, 'status_validasi' => 'pending'
            ]);
        }
    }
}