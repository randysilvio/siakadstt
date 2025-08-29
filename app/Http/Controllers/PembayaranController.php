<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pembayaran::with('mahasiswa.user')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('mahasiswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $pembayarans = $query->paginate(10)->withQueryString();
        return view('pembayaran.index', compact('pembayarans'));
    }

    public function create(): View
    {
        $mahasiswas = Mahasiswa::orderBy('nama_lengkap')->get();
        return view('pembayaran.create', compact('mahasiswas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'jumlah' => 'required|integer|min:1',
            'semester' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        Pembayaran::create($request->all());
        return redirect()->route('admin.pembayaran.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    public function tandaiLunas(Pembayaran $pembayaran): RedirectResponse
    {
        $pembayaran->update([
            'status' => 'lunas',
            'tanggal_bayar' => now(),
        ]);
        return redirect()->route('admin.pembayaran.index')->with('success', 'Tagihan berhasil ditandai lunas.');
    }
    
    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        $pembayaran->delete();
        return redirect()->route('admin.pembayaran.index')->with('success', 'Tagihan berhasil dihapus.');
    }

    public function riwayat(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        $pembayarans = $user->mahasiswa->pembayarans()->latest()->get();
        return view('pembayaran.riwayat', compact('pembayarans'));
    }
}
