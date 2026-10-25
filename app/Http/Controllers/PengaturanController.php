<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function index()
    {
        $cfg = Pengaturan::allCached();
        $pengaturans = Pengaturan::all()->keyBy('key');

        return view('pengaturan.index', compact('cfg', 'pengaturans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kampus'            => 'required|string|max:255',
            'singkatan_kampus'       => 'required|string|max:80',
            'nama_rektor'            => 'required|string|max:255',
            'logo_file'              => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'logo_event_file'        => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'favicon_file'           => 'nullable|image|mimes:png,ico,jpg,jpeg,webp|max:1024',
            'foto_pimpinan_file'     => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'watermark_card_file'    => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'watermark_section_file' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:3072',
            'bagan_struktur_file'    => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $allowedKeys = array_keys(Pengaturan::defaults());
        $fileKeys = [
            'logo_path',
            'logo_event_path',
            'favicon_path',
            'foto_pimpinan_path',
            'watermark_card_path',
            'watermark_section_path',
            'bagan_struktur_path',
        ];

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

        $uploadMapping = [
            'logo_file'              => 'logo_path',
            'logo_event_file'        => 'logo_event_path',
            'favicon_file'           => 'favicon_path',
            'foto_pimpinan_file'     => 'foto_pimpinan_path',
            'watermark_card_file'    => 'watermark_card_path',
            'watermark_section_file' => 'watermark_section_path',
            'bagan_struktur_file'    => 'bagan_struktur_path',
        ];

        foreach ($uploadMapping as $inputName => $settingKey) {
            if ($request->hasFile($inputName)) {
                $old = Pengaturan::get($inputName === 'logo_file' ? 'logo_path' : $settingKey);
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
                $path = $request->file($inputName)->store('branding', 'public');
                Pengaturan::updateOrCreate(['key' => $settingKey], ['value' => $path]);
            }
        }

        $resetFlags = [
            'hapus_logo_event'        => 'logo_event_path',
            'hapus_watermark_card'    => 'watermark_card_path',
            'hapus_watermark_section' => 'watermark_section_path',
        ];

        foreach ($resetFlags as $flag => $key) {
            if ($request->input($flag) === '1') {
                $old = Pengaturan::get($key);
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
                Pengaturan::updateOrCreate(['key' => $key], ['value' => '']);
            }
        }

        Pengaturan::clearCache();

        return redirect()->back()->with('success', 'Konfigurasi sistem, logo kegiatan, dan tema berhasil disimpan.');
    }
}