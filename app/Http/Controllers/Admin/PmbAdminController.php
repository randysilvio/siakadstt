<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camaba;
use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use App\Models\Pembayaran; // [TAMBAHAN] Import Model Pembayaran
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PmbAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status_pendaftaran', $request->status);
        }

        $pendaftars = $query->paginate(20);

        return view('admin.pmb.index', compact('pendaftars'));
    }

    public function show(Camaba $camaba)
    {
        // [TAMBAHAN] Load data tagihan untuk ditampilkan di view admin
        $tagihan = Pembayaran::where('user_id', $camaba->user_id)
            ->where('jenis_pembayaran', 'formulir_pmb')
            ->first();
            
        return view('admin.pmb.show', compact('camaba', 'tagihan'));
    }

    /**
     * [BARU] TAHAP 1: Verifikasi Pembayaran Formulir
     * Admin klik ini agar Camaba bisa lanjut isi biodata.
     */
    public function approvePayment($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        
        $pembayaran->update([
            'status' => 'lunas',
            'tanggal_bayar' => $pembayaran->tanggal_bayar ?? now(),
            'keterangan' => 'Diverifikasi oleh Admin PMB',
        ]);

        return back()->with('success', 'Pembayaran divalidasi LUNAS. Camaba sekarang bisa mengisi biodata.');
    }

    /**
     * TAHAP 2: Finalisasi Kelulusan (Konversi ke Mahasiswa)
     */
    public function approve(Request $request, Camaba $camaba)
    {
        if ($camaba->is_migrated) {
            return back()->with('error', 'Camaba ini sudah menjadi mahasiswa aktif.');
        }

        // [VALIDASI] Pastikan Biodata sudah diisi sebelum diluluskan
        if (!$camaba->pilihan_prodi_1_id) {
             return back()->with('error', 'Biodata belum lengkap (Prodi belum dipilih). Tidak bisa diluluskan.');
        }

        DB::beginTransaction();
        try {
            // 1. Generate NIM Otomatis
            $tahunMasuk = date('Y');
            $kodeProdi = $camaba->prodi1->kode_prodi ?? '00'; 
            
            $lastMhs = Mahasiswa::where('program_studi_id', $camaba->pilihan_prodi_1_id)
                ->whereYear('created_at', $tahunMasuk)
                ->orderBy('nim', 'desc')
                ->first();

            $urutan = 1;
            if ($lastMhs) {
                // Ambil 3 digit terakhir (Pastikan NIM di DB formatnya konsisten angka)
                $lastUrutan = (int) substr($lastMhs->nim, -3);
                $urutan = $lastUrutan + 1;
            }

            $nimBaru = $tahunMasuk . $kodeProdi . str_pad($urutan, 3, '0', STR_PAD_LEFT);

            // 2. Insert ke Tabel Mahasiswas
            Mahasiswa::create([
                'user_id' => $camaba->user_id,
                'nim' => $nimBaru,
                'nama_lengkap' => $camaba->user->name,
                'program_studi_id' => $camaba->pilihan_prodi_1_id,
                'tempat_lahir' => $camaba->tempat_lahir,
                'tanggal_lahir' => $camaba->tanggal_lahir,
                'jenis_kelamin' => $camaba->jenis_kelamin,
                'agama' => $camaba->agama,
                'no_hp' => $camaba->no_hp,
                'alamat' => $camaba->alamat,
                'tanggal_masuk' => now(),
                'tahun_masuk' => $tahunMasuk, // [TAMBAHAN] Penting untuk filter angkatan
                'status_mahasiswa' => 'Aktif',
            ]);

            // 3. Update Status Camaba
            $camaba->update([
                'status_pendaftaran' => 'lulus',
                'is_migrated' => true
            ]);

            // 4. Ganti Role User
            $user = User::find($camaba->user_id);
            $roleCamaba = Role::where('name', 'camaba')->first();
            $roleMhs = Role::where('name', 'mahasiswa')->first();

            if ($roleCamaba && $roleMhs) {
                $user->roles()->detach($roleCamaba->id);
                $user->roles()->attach($roleMhs->id);
            }

            DB::commit();

            return redirect()->route('admin.pmb.index')
                ->with('success', 'Berhasil! ' . $user->name . ' resmi menjadi Mahasiswa dengan NIM: ' . $nimBaru);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    public function reject(Camaba $camaba)
    {
        $camaba->update(['status_pendaftaran' => 'tidak_lulus']);
        return back()->with('success', 'Pendaftaran ditolak.');
    }
}