<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Camaba;
use App\Models\PmbPeriod;
use App\Models\Pembayaran;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification; // [TAMBAHAN]
use App\Notifications\GeneralNotification; // [TAMBAHAN]

class PmbRegisterController extends Controller
{
    /**
     * Menampilkan Form Pendaftaran Publik
     */
    public function showRegistrationForm()
    {
        // Cari Gelombang PMB yang sedang aktif
        $periodeAktif = PmbPeriod::where('is_active', true)->first();
        
        return view('auth.register_pmb', compact('periodeAktif'));
    }

    /**
     * Proses Pendaftaran Akun Camaba & Tagihan Formulir
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'no_hp' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'pmb_period_id' => 'required|exists:pmb_periods,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat User Baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2. Berikan Role Camaba
            $roleCamaba = Role::where('name', 'camaba')->first();
            if ($roleCamaba) {
                $user->roles()->attach($roleCamaba->id);
            }

            // 3. Buat Data Camaba
            $periode = PmbPeriod::findOrFail($request->pmb_period_id);
            $noPendaftaran = 'REG-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            Camaba::create([
                'user_id' => $user->id,
                'pmb_period_id' => $periode->id,
                'no_pendaftaran' => $noPendaftaran,
                'no_hp' => $request->no_hp,
                'status_pendaftaran' => 'draft', 
            ]);

            // 4. Otomatis Buat Tagihan Pembayaran Formulir
            Pembayaran::create([
                'user_id' => $user->id,          
                'mahasiswa_id' => null,          
                'jenis_pembayaran' => 'formulir_pmb',
                'jumlah' => $periode->biaya_pendaftaran ?? 250000,
                'status' => 'belum_bayar',
                'semester' => 'PMB',             
                'keterangan' => 'Biaya Pendaftaran ' . $periode->nama_gelombang
            ]);

            DB::commit();

            // [TAMBAHAN] Kirim Notifikasi ke semua Admin
            $admins = User::whereHas('roles', function($q) { $q->where('name', 'admin'); })->get();
            Notification::send($admins, new GeneralNotification(
                'Pendaftar PMB Baru!', 
                'Camaba baru atas nama ' . $user->name . ' telah mendaftar.', 
                route('admin.pmb.index'), 
                'bi-person-badge-fill text-success'
            ));

            // 5. Auto Login & Redirect ke Dashboard Camaba
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Silakan upload bukti pembayaran formulir.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pendaftaran. Silakan coba lagi.')->withInput();
        }
    }
}