<?php

namespace App\Http\Controllers;

use App\Models\Koleksi;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerpustakaanController extends Controller
{
    /**
     * Menampilkan halaman katalog online (OPAC) untuk semua user.
     */
    public function index(Request $request): View
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
    public function dashboard(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $totalJudul = Koleksi::count();
        $totalEksemplar = Koleksi::sum('jumlah_stok');
        
        $pengumumans = Pengumuman::query()
            ->whereHas('roles', function ($q) use ($user) {
                $q->whereIn('id', $user->roles->pluck('id'));
            })
            ->latest()
            ->take(5)
            ->get();
        
        return view('perpustakaan.dashboard', compact('totalJudul', 'totalEksemplar', 'pengumumans'));
    }
}