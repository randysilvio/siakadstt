<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camaba;
use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PmbAdminController extends Controller
{
    /**
     * Menampilkan Daftar Pendaftar
     */
    public function index(Request $request)
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])
            ->orderBy('created_at', 'desc');

        // Filter status
        if ($request->status) {
            $query->where('status_pendaftaran', $request->status);
        }

        $pendaftars = $query->paginate(20);

        return view('admin.pmb.index', compact('pendaftars'));
    }

    /**
     * Menampilkan Detail Pendaftar & Dokumen
     */
    public function show(Camaba $camaba)
    {
        return view('admin.pmb.show', compact('camaba'));
    }

    /**
     * Proses Kelulusan (Terima Mahasiswa)
     */
    public function approve(Request $request, Camaba $camaba)
    {
        if ($camaba->is_migrated) {
            return back()->with('error', 'Camaba ini sudah menjadi mahasiswa aktif.');
        }

        DB::beginTransaction();
        try {
            // 1. Generate NIM Otomatis
            // Format: TAHUN + KODE_PRODI + URUTAN (3 Digit)
            // Contoh: 202401001
            $tahunMasuk = date('Y');
            $kodeProdi = $camaba->prodi1->kode_prodi ?? '00'; 
            
            // Cari urutan terakhir di tahun & prodi yg sama
            $lastMhs = Mahasiswa::where('program_studi_id', $camaba->pilihan_prodi_1_id)
                ->whereYear('created_at', $tahunMasuk)
                ->orderBy('nim', 'desc')
                ->first();

            $urutan = 1;
            if ($lastMhs) {
                // Ambil 3 digit terakhir NIM
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
                'status' => 'Aktif',
                // Data sekolah asal bisa disimpan jika ada kolomnya di tabel mahasiswa
            ]);

            // 3. Update Status Camaba
            $camaba->update([
                'status_pendaftaran' => 'lulus',
                'is_migrated' => true
            ]);

            // 4. Ganti Role User dari 'camaba' jadi 'mahasiswa'
            $user = User::find($camaba->user_id);
            $roleCamaba = Role::where('name', 'camaba')->first();
            $roleMhs = Role::where('name', 'mahasiswa')->first();

            if ($roleCamaba && $roleMhs) {
                $user->roles()->detach($roleCamaba->id); // Lepas role camaba
                $user->roles()->attach($roleMhs->id);    // Pasang role mahasiswa
            }

            DB::commit();

            return redirect()->route('admin.pmb.index')
                ->with('success', 'Berhasil! ' . $user->name . ' resmi menjadi Mahasiswa dengan NIM: ' . $nimBaru);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    /**
     * Tolak Pendaftaran
     */
    public function reject(Camaba $camaba)
    {
        $camaba->update(['status_pendaftaran' => 'tidak_lulus']);
        return back()->with('success', 'Pendaftaran ditolak.');
    }
}