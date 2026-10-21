<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengaturan;

class PengaturanController extends Controller
{
    /**
     * Daftar pengaturan resmi yang diizinkan untuk disimpan (Whitelist).
     */
    protected array $allowedKeys = [
        'nama_rektor',
    ];

    /**
     * Menampilkan halaman form pengaturan.
     */
    public function index()
    {
        $pengaturans = Pengaturan::all()->keyBy('key');
        return view('pengaturan.index', compact('pengaturans'));
    }

    /**
     * Menyimpan data pengaturan secara aman.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_rektor' => 'required|string|max:255',
        ]);

        foreach ($this->allowedKeys as $key) {
            if (array_key_exists($key, $validated)) {
                Pengaturan::updateOrCreate(
                    ['key' => $key],
                    ['value' => trim((string) $validated[$key])]
                );
            }
        }
        
        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}