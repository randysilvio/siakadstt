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

            // 2. Assign Role 'camaba'
            // Pastikan role 'camaba' ada di database
            $roleCamaba = Role::firstOrCreate(['name' => 'camaba'], ['display_name' => 'Calon Mahasiswa']);
            $user->roles()->attach($roleCamaba);

            // 3. Buat Data Profil Camaba
            $periode = PmbPeriod::find($request->pmb_period_id);
            
            // Generate No Pendaftaran Sementara (Format: REG-Tahun-UrutanUser)
            $noPendaftaran = 'REG-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            Camaba::create([
                'user_id' => $user->id,
                'pmb_period_id' => $periode->id,
                'no_pendaftaran' => $noPendaftaran,
                'no_hp' => $request->no_hp,
                'status_pendaftaran' => 'draft', // Status awal DRAFT (belum bayar/isi biodata)
            ]);

            // 4. Otomatis Buat Tagihan Pembayaran Formulir
            // PERBAIKAN: Menggunakan user_id, dan set mahasiswa_id ke NULL
            Pembayaran::create([
                'user_id' => $user->id,          // Relasi ke User (Camaba)
                'mahasiswa_id' => null,          // Kosongkan karena belum jadi mahasiswa
                'jenis_pembayaran' => 'formulir_pmb',
                'jumlah' => $periode->biaya_pendaftaran ?? 250000,
                'status' => 'belum_bayar',
                'semester' => 'PMB',             // Penanda semester
                'keterangan' => 'Biaya Pendaftaran ' . $periode->nama_gelombang
            ]);

            DB::commit();

            // 5. Auto Login & Redirect ke Dashboard Camaba
            Auth::login($user);
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan pendaftaran: ' . $e->getMessage())->withInput();
        }
    }
}