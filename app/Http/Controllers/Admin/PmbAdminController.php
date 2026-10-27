<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camaba;
use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use App\Models\Pembayaran; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PmbAdminController extends Controller
{
    /**
     * Menampilkan daftar pendaftar dengan fitur Paginasi dan Smart Search.
     */
    public function index(Request $request)
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status_pendaftaran', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_pendaftaran', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $pendaftars = $query->paginate(20)->withQueryString();

        return view('admin.pmb.index', compact('pendaftars'));
    }

    public function show(Camaba $camaba)
    {
        $camaba->load(['user', 'prodi1', 'prodi2', 'period', 'documents']);
        $tagihan = Pembayaran::where('user_id', $camaba->user_id)
            ->where('jenis_pembayaran', 'formulir_pmb')
            ->latest()
            ->first();
            
        return view('admin.pmb.show', compact('camaba', 'tagihan'));
    }

    /**
     * TAHAP 1: Verifikasi Pembayaran Formulir
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
        if ($camaba->is_migrated || $camaba->status_pendaftaran === 'lulus') {
            return back()->with('error', 'Camaba ini sudah menjadi mahasiswa aktif.');
        }

        // [PERBAIKAN KEAMANAN FASE 4]: Pastikan pembayaran formulir PMB sudah lunas sebelum diluluskan
        $lunas = Pembayaran::where('user_id', $camaba->user_id)
            ->where('jenis_pembayaran', 'formulir_pmb')
            ->where('status', 'lunas')
            ->exists();

        if (!$lunas) {
            return back()->with('error', 'Gagal meluluskan: Tagihan formulir pendaftaran Camaba ini belum berstatus LUNAS.');
        }

        if (!$camaba->pilihan_prodi_1_id || !$camaba->tempat_lahir) {
            return back()->with('error', 'Biodata belum lengkap. Camaba wajib melengkapi biodata terlebih dahulu.');
        }

        // Hanya pendaftar yang biodatanya sudah diserahkan & menunggu verifikasi yang dapat diluluskan.
        if ($camaba->status_pendaftaran === 'tidak_lulus') {
            return back()->with('error', 'Pendaftar ini sudah dinyatakan TIDAK LULUS. Ubah keputusan seleksi tidak dapat dilakukan dari tombol kelulusan.');
        }
        if ($camaba->status_pendaftaran !== 'menunggu_verifikasi') {
            return back()->with('error', 'Pendaftar belum menyerahkan biodata untuk diverifikasi.');
        }

        DB::beginTransaction();
        try {
            // 1. Generate NIM Otomatis dengan proteksi anti-bentrok (Lock & Loop Check)
            // Kunci baris prodi sebagai mutex agar dua admin yang meluluskan bersamaan tidak mendapat NIM yang sama
            DB::table('program_studis')->where('id', $camaba->pilihan_prodi_1_id)->lockForUpdate()->first();

            $tahunMasuk = date('Y');
            $kodeProdi = $camaba->prodi1->kode_prodi ?: str_pad((string) $camaba->pilihan_prodi_1_id, 2, '0', STR_PAD_LEFT);
            $prefixNim = $tahunMasuk . $kodeProdi;

            $lastMhs = Mahasiswa::where('nim', 'like', $prefixNim . '%')
                ->lockForUpdate()
                ->orderBy('nim', 'desc')
                ->first();

            $urutan = 1;
            if ($lastMhs && !empty($lastMhs->nim)) {
                $lastUrutan = (int) substr($lastMhs->nim, strlen($prefixNim));
                $urutan = $lastUrutan + 1;
            }

            do {
                $nimBaru = $prefixNim . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
                $urutan++;
            } while (Mahasiswa::where('nim', $nimBaru)->exists());


            // 2. Insert ke Tabel Mahasiswas
            // [PERBAIKAN BUG FASE 4]: Menggunakan 'nomor_telepon' & menyertakan 'nisn' agar data tidak hilang
            Mahasiswa::create([
                'user_id' => $camaba->user_id,
                'nim' => $nimBaru,
                'nama_lengkap' => $camaba->user->name,
                'program_studi_id' => $camaba->pilihan_prodi_1_id,
                'tempat_lahir' => $camaba->tempat_lahir ?? 'Belum Diisi',
                'tanggal_lahir' => $camaba->tanggal_lahir ?? now(),
                'jenis_kelamin' => $camaba->jenis_kelamin ?? 'L',
                'agama' => $camaba->agama ?? 'Kristen Protestan',
                'nomor_telepon' => $camaba->no_hp ?? '-',
                'nisn' => $camaba->nisn,
                'alamat' => $camaba->alamat ?? 'Belum Diisi',
                // NIK tidak lagi diisi angka buatan: dibiarkan kosong agar terdeteksi wajib dilengkapi sebelum sinkron Feeder.
                'nik' => null,
                'kewarganegaraan' => 'WNI',
                'nama_ibu_kandung' => 'Belum Diisi',
                'jalur_pendaftaran' => 'Mandiri (PMB)',
                'tahun_masuk' => $tahunMasuk, 
                'status_mahasiswa' => 'Aktif',
            ]);

            // 3. Update Status Camaba
            $camaba->update([
                'status_pendaftaran' => 'lulus',
                'is_migrated' => true
            ]);

            // 4. Ganti Role User dari Camaba menjadi Mahasiswa
            $user = User::find($camaba->user_id);
            $roleCamaba = Role::where('name', 'camaba')->first();
            $roleMhs = Role::where('name', 'mahasiswa')->first();

            if ($user && $roleCamaba && $roleMhs) {
                $user->roles()->detach($roleCamaba->id);
                $user->roles()->syncWithoutDetaching([$roleMhs->id]);
            }

            DB::commit();

            return redirect()->route('admin.pmb.index')
                ->with('success', 'Berhasil! ' . $user->name . ' resmi menjadi Mahasiswa Aktif dengan NIM: ' . $nimBaru);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Approve PMB: ' . $e->getMessage()); 
            
            return back()->with('error', 'Gagal memproses kelulusan. Detail kesalahan telah dicatat di log sistem.');
        }
    }

    public function reject(Request $request, Camaba $camaba)
    {
        if ($camaba->is_migrated) {
            return back()->with('error', 'Camaba yang sudah dimigrasikan menjadi mahasiswa tidak dapat ditolak.');
        }

        $camaba->update(['status_pendaftaran' => 'tidak_lulus']);
        return back()->with('success', 'Pendaftaran kandidat telah ditolak.');
    }
}