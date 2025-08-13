<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengumumanController extends Controller
{
    /**
     * Konstruktor sekarang tidak memerlukan middleware karena
     * perlindungan hak akses sudah ditangani di level routing (web.php).
     */
    public function __construct()
    {
        // Tidak ada middleware yang diperlukan di sini.
    }

    /**
     * Menampilkan daftar semua pengumuman.
     */
    public function index()
    {
        $pengumumans = Pengumuman::latest()->paginate(10);
        return view('pengumuman.index', compact('pengumumans'));
    }

    /**
     * Menampilkan formulir untuk membuat pengumuman baru.
     */
    public function create()
    {
        return view('pengumuman.create');
    }

    /**
     * Menyimpan pengumuman baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'target_role' => 'required|in:semua,admin,dosen,mahasiswa,tendik',
        ]);

        Pengumuman::create($request->all());

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Menampilkan detail satu pengumuman.
     */
    public function show(Pengumuman $pengumuman)
    {
        return view('pengumuman.show', compact('pengumuman'));
    }

    /**
     * Menampilkan formulir untuk mengedit pengumuman.
     */
    public function edit(Pengumuman $pengumuman)
    {
        return view('pengumuman.edit', compact('pengumuman'));
    }

    /**
     * Memperbarui pengumuman di database.
     */
    public function update(Request $request, Pengumuman $pengumuman)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'target_role' => 'required|in:semua,admin,dosen,mahasiswa,tendik',
        ]);

        $pengumuman->update($request->all());

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Menghapus pengumuman dari database.
     */
    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
