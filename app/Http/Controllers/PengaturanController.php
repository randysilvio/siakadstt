<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Pengaturan;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman form pengaturan.
     */
    public function index()
    {
        // Ambil semua pengaturan dan jadikan key sebagai kunci array agar mudah diakses di view
        $pengaturans = Pengaturan::all()->keyBy('key');
        return view('pengaturan.index', compact('pengaturans'));
    }

    /**
     * Menyimpan data pengaturan.
     */
    public function store(Request $request)
    {
        // Ambil semua data kecuali _token
        $data = $request->except('_token');
        foreach ($data as $key => $value) {
            // Gunakan updateOrCreate untuk membuat atau memperbarui pengaturan
            Pengaturan::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        
        // PERBAIKAN: Menggunakan nama rute yang benar
        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}