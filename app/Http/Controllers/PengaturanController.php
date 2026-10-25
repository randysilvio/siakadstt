<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman Pusat Kendali Pengaturan Sistem (7 Tab).
     */
    public function index()
    {
        $cfg = Pengaturan::allCached();
        $pengaturans = Pengaturan::all()->keyBy('key');

        return view('pengaturan.index', compact('cfg', 'pengaturans'));
    }

    /**
     * Menyimpan seluruh konfigurasi dari 7 Tab Pengaturan & Upload Berkas Visual.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kampus'          => 'required|string|max:255',
            'singkatan_kampus'     => 'required|string|max:80',
            'nama_rektor'          => 'required|string|max:255',
            'logo_file'            => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon_file'         => 'nullable|image|mimes:png,ico,jpg,jpeg|max:1024',
            'foto_pimpinan_file'   => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
            'bagan_struktur_file'  => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $allowedKeys = array_keys(Pengaturan::defaults());
        $fileKeys = ['logo_path', 'favicon_path', 'foto_pimpinan_path', 'bagan_struktur_path'];

        foreach ($allowedKeys as $key) {
            if (in_array($key, $fileKeys, true)) {
                continue;
            }
            if ($request->has($key)) {
                $val = $request->input($key);
                Pengaturan::updateOrCreate(
                    ['key' => $key],
                    ['value' => is_null($val) ? '' : trim((string) $val)]
                );
            }
        }

        // Handle Upload Logo Utama Kampus
        if ($request->hasFile('logo_file')) {
            $old = Pengaturan::get('logo_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('logo_file')->store('branding', 'public');
            Pengaturan::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);
        }

        // Handle Upload Favicon
        if ($request->hasFile('favicon_file')) {
            $old = Pengaturan::get('favicon_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('favicon_file')->store('branding', 'public');
            Pengaturan::updateOrCreate(['key' => 'favicon_path'], ['value' => $path]);
        }

        // Handle Upload Foto Pimpinan (Ketua / Rektor)
        if ($request->hasFile('foto_pimpinan_file')) {
            $old = Pengaturan::get('foto_pimpinan_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('foto_pimpinan_file')->store('branding', 'public');
            Pengaturan::updateOrCreate(['key' => 'foto_pimpinan_path'], ['value' => $path]);
        }

        // Handle Upload Gambar Bagan Struktur Organisasi
        if ($request->hasFile('bagan_struktur_file')) {
            $old = Pengaturan::get('bagan_struktur_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('bagan_struktur_file')->store('branding', 'public');
            Pengaturan::updateOrCreate(['key' => 'bagan_struktur_path'], ['value' => $path]);
        }

        Pengaturan::clearCache();

        return redirect()->back()->with('success', 'Seluruh konfigurasi Pusat Kendali Sistem, Tema Visual, Kop Surat, dan Struktur Organisasi berhasil diperbarui.');
    }
}