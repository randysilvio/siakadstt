<?php

namespace App\Http\Controllers;

use App\Models\Koleksi;
use Illuminate\Http\Request;

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
        
        return view('perpustakaan.dashboard', compact('totalJudul', 'totalEksemplar'));
    }
}
