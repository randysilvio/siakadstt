<?php

namespace App\Http\Controllers;

use App\Models\Camaba;
use App\Models\ProgramStudi;
use App\Models\PmbDocument;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PmbController extends Controller
{
    /**
     * Tampilkan Halaman Upload Bukti Bayar
     */
    public function showPaymentForm()
    {
        $user = Auth::user();
        
        // Cari tagihan PMB user ini
        $tagihan = $user->pembayarans()
            ->where('jenis_pembayaran', 'formulir_pmb')
            ->latest()
            ->first();

        if (!$tagihan) {
            return redirect()->route('dashboard')->with('error', 'Tagihan tidak ditemukan.');
        }

        return view('pmb.pembayaran', compact('tagihan'));
    }

    /**
     * Proses Simpan Bukti Bayar
     */
    public function storePaymentProof(Request $request) 
    {
        $request->validate([
            'bukti_bayar' => 'required|image|max:2048', // Max 2MB
            'pembayaran_id' => 'required|exists:pembayarans,id'
        ]);

        $tagihan = Pembayaran::where('id', $request->pembayaran_id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        if ($request->hasFile('bukti_bayar')) {
            // Hapus bukti lama jika ada
            if ($tagihan->bukti_bayar) {
                Storage::disk('public')->delete($tagihan->bukti_bayar);
            }

            $path = $request->file('bukti_bayar')->store('bukti-bayar-pmb', 'public');

            $tagihan->update([
                'bukti_bayar' => $path,
                'status' => 'menunggu_konfirmasi', // Status berubah agar admin tahu
                'tanggal_bayar' => now(),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Bukti pembayaran berhasil diupload! Mohon tunggu verifikasi Admin Keuangan.');
    }

    /**
     * Menampilkan Form Biodata Lengkap
     */
    public function showBiodataForm()
    {
        $user = Auth::user();
        
        // Cek apakah user adalah camaba
        if (!$user->hasRole('camaba')) {
            return redirect()->route('dashboard');
        }

        $camaba = $user->camaba;

        // Cek apakah sudah bayar formulir (Status LUNAS)
        $lunas = $user->pembayarans()
            ->where('jenis_pembayaran', 'formulir_pmb')
            ->where('status', 'lunas')
            ->exists();

        if (!$lunas) {
            return redirect()->route('dashboard')->with('error', 'Silakan selesaikan pembayaran formulir terlebih dahulu.');
        }

        $programStudis = ProgramStudi::all(); // Untuk dropdown pilihan prodi
        $dokumen = $camaba->documents->pluck('path_file', 'jenis_dokumen'); // Ambil dokumen yg sudah diupload

        return view('pmb.form_biodata', compact('camaba', 'programStudis', 'dokumen'));
    }

    /**
     * Menyimpan Data Biodata & Dokumen
     */
    public function updateBiodata(Request $request)
    {
        $user = Auth::user();
        $camaba = $user->camaba;

        // Validasi
        $request->validate([
            // Data Diri
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'alamat' => 'required|string',
            
            // Data Sekolah
            'sekolah_asal' => 'required|string',
            'nisn' => 'required|numeric',
            'tahun_lulus' => 'required|numeric',
            'nilai_rata_rata_rapor' => 'required|numeric|between:0,100',
            
            // Pilihan Prodi
            'pilihan_prodi_1_id' => 'required|exists:program_studis,id',
            'pilihan_prodi_2_id' => 'nullable|exists:program_studis,id|different:pilihan_prodi_1_id',
            
            // Validasi File (Nullable karena mungkin user hanya update data teks)
            'file_ijazah' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048', 
            'file_kk' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_pas_foto' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update Data Camaba
            $camaba->update([
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'alamat' => $request->alamat,
                'sekolah_asal' => $request->sekolah_asal,
                'nisn' => $request->nisn,
                'tahun_lulus' => $request->tahun_lulus,
                'nilai_rata_rata_rapor' => $request->nilai_rata_rata_rapor,
                'pilihan_prodi_1_id' => $request->pilihan_prodi_1_id,
                'pilihan_prodi_2_id' => $request->pilihan_prodi_2_id,
                'status_pendaftaran' => 'menunggu_verifikasi', // Update status jadi menunggu verifikasi admin
            ]);

            // 2. Handle Upload File
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

    // Helper untuk upload file
    private function handleUpload($request, $inputName, $jenisDokumen, $camaba)
    {
        if ($request->hasFile($inputName)) {
            // Hapus file lama jika ada
            $existingDoc = PmbDocument::where('camaba_id', $camaba->id)
                ->where('jenis_dokumen', $jenisDokumen)
                ->first();
            
            if ($existingDoc) {
                Storage::disk('public')->delete($existingDoc->path_file);
                $existingDoc->delete();
            }

            // Simpan file baru
            $path = $request->file($inputName)->store('dokumen-pmb', 'public');

            PmbDocument::create([
                'camaba_id' => $camaba->id,
                'jenis_dokumen' => $jenisDokumen,
                'path_file' => $path,
                'status_validasi' => 'pending'
            ]);
        }
    }
}