<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Mahasiswa;
use App\Models\Pengumuman; // 1. Tambahkan model Pengumuman
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    /**
     * Menampilkan dashboard khusus untuk staf keuangan.
     */
    public function dashboard()
    {
        $totalTagihan = Pembayaran::count();
        $totalLunas = Pembayaran::where('status', 'lunas')->count();
        $totalBelumLunas = Pembayaran::where('status', 'belum lunas')->count();

        // 2. Tambahkan logika untuk mengambil pengumuman
        $user = Auth::user();
        $pengumumans = Pengumuman::where('target_role', 'semua')
            ->orWhere('target_role', $user->role)
            ->latest()
            ->take(5)
            ->get();

        // 3. Kirim semua variabel ke view
        return view('pembayaran.dashboard', compact('totalTagihan', 'totalLunas', 'totalBelumLunas', 'pengumumans'));
    }

    public function index()
    {
        $pembayarans = Pembayaran::with('mahasiswa')->latest()->paginate(10);
        return view('pembayaran.index', compact('pembayarans'));
    }

    public function create()
    {
        $mahasiswas = Mahasiswa::all();
        return view('pembayaran.create', compact('mahasiswas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'jumlah' => 'required|integer|min:1',
            'semester' => 'required|string|max:50',
        ]);

        Pembayaran::create($request->all());
        return redirect()->route('pembayaran.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    public function tandaiLunas(Pembayaran $pembayaran)
    {
        $pembayaran->update([
            'status' => 'lunas',
            'tanggal_bayar' => now(),
        ]);
        return redirect()->route('pembayaran.index')->with('success', 'Tagihan berhasil ditandai lunas.');
    }
    
    public function destroy(Pembayaran $pembayaran)
    {
        $pembayaran->delete();
        return redirect()->route('pembayaran.index')->with('success', 'Tagihan berhasil dihapus.');
    }

    public function riwayat()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if(!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        $pembayarans = $mahasiswa->pembayarans()->latest()->get();
        return view('pembayaran.riwayat', compact('pembayarans'));
    }
}
