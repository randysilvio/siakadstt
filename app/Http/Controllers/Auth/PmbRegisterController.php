<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Camaba;
use App\Models\PmbPeriod;
use App\Models\Pembayaran;
use App\Models\Role;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;

class PmbRegisterController extends Controller
{
    /**
     * Menampilkan Form Pendaftaran Publik
     */
    public function showRegistrationForm()
    {
        $today = Carbon::today();

        $periodeAktif = PmbPeriod::where('is_active', true)
            ->whereDate('tanggal_buka', '<=', $today)
            ->whereDate('tanggal_tutup', '>=', $today)
            ->first();

        $prodis = ProgramStudi::orderBy('nama_prodi')->get(); 
        
        return view('auth.register_pmb', compact('periodeAktif', 'prodis'));
    }

    /**
     * Proses Pendaftaran Akun Camaba & Tagihan Formulir
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|string|email|max:255|unique:users',
            'no_hp'              => 'required|string|max:15',
            'password'           => 'required|string|min:8|confirmed',
            'pmb_period_id'      => 'required|exists:pmb_periods,id',
            'pilihan_prodi_1_id' => 'required|exists:program_studis,id',
        ]);

        $today = Carbon::today();
        $periode = PmbPeriod::where('id', $request->pmb_period_id)
            ->where('is_active', true)
            ->whereDate('tanggal_buka', '<=', $today)
            ->whereDate('tanggal_tutup', '>=', $today)
            ->first();

        if (!$periode) {
            return back()->withInput()->with('error', 'Gelombang pendaftaran yang dipilih sudah tidak aktif atau telah melewati batas waktu pendaftaran.');
        }

        DB::beginTransaction();
        try {
            // 1. Buat User Baru
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2. Pastikan Role Camaba selalu tersedia di database & pasangkan ke User
            $roleCamaba = Role::firstOrCreate(
                ['name' => 'camaba'],
                ['display_name' => 'Calon Mahasiswa Baru (PMB)']
            );
            $user->roles()->syncWithoutDetaching([$roleCamaba->id]);

            // 3. Buat Data Camaba
            $noPendaftaran = 'REG-' . date('Y') . '-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);

            Camaba::create([
                'user_id'            => $user->id,
                'pmb_period_id'      => $periode->id,
                'no_pendaftaran'     => $noPendaftaran,
                'no_hp'              => $request->no_hp,
                'pilihan_prodi_1_id' => $request->pilihan_prodi_1_id,
                'status_pendaftaran' => 'draft', 
            ]);

            // 4. Otomatis Buat Tagihan Pembayaran Formulir
            Pembayaran::create([
                'user_id'          => $user->id,          
                'mahasiswa_id'     => null,          
                'jenis_pembayaran' => 'formulir_pmb',
                'jumlah'           => $periode->biaya_pendaftaran ?? 250000,
                'status'           => 'belum_lunas',
                'semester'         => 'PMB',             
                'keterangan'       => 'Biaya Pendaftaran ' . $periode->nama_gelombang
            ]);

            DB::commit();

            // Kirim Notifikasi ke semua Admin
            $admins = User::whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new GeneralNotification(
                    'Pendaftar PMB Baru', 
                    'Camaba baru atas nama ' . $user->name . ' telah mendaftar.', 
                    route('admin.pmb.index'), 
                    'bi-person-badge-fill text-dark'
                ));
            }

            // 5. Auto Login & Redirect ke Dashboard Camaba
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Silakan unggah bukti pembayaran formulir.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pendaftaran: ' . $e->getMessage())->withInput();
        }
    }
}