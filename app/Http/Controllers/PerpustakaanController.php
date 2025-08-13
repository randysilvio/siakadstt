<?php

namespace App\Http\Controllers;

use App\Models\Koleksi;
use App\Models\Pengumuman; // 1. Tambahkan model Pengumuman
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // 2. Tambahkan Auth

class PerpustakaanController extends Controller
{
    /**
     * Menampilkan halaman katalog online (OPAC) untuk semua user.
     */
    public function index(Request $request)
    {
        $query = Koleksi::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%");
        }

        $koleksis = $query->latest()->paginate(12);

        return view('perpustakaan.index', compact('koleksis'));
    }

    /**
     * Menampilkan halaman dashboard khusus untuk pustakawan.
     */
    public function dashboard()
    {
        $totalJudul = Koleksi::count();
        $totalEksemplar = Koleksi::sum('jumlah_stok');
        
        // 3. Tambahkan logika untuk mengambil pengumuman
        $user = Auth::user();
        $pengumumans = Pengumuman::where('target_role', 'semua')
            ->orWhere('target_role', $user->role)
            ->latest()
            ->take(5)
            ->get();
        
        // 4. Kirim semua variabel ke view
        return view('perpustakaan.dashboard', compact('totalJudul', 'totalEksemplar', 'pengumumans'));
    }
}
