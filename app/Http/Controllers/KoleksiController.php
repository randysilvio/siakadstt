<?php

namespace App\Http\Controllers;

use App\Models\Koleksi;
use App\Models\PerpustakaanEksemplar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KoleksiController extends Controller
{
    public function index()
    {
        $koleksis = Koleksi::withCount('eksemplars')->latest()->paginate(10);
        return view('perpustakaan.koleksi.index', compact('koleksis'));
    }

    public function create()
    {
        return view('perpustakaan.koleksi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|digits:4',
            'isbn' => 'nullable|string|unique:perpustakaan_koleksi,isbn',
            'jumlah_stok' => 'required|integer|min:1',
            'lokasi_rak' => 'required|string|max:255',
            'sinopsis' => 'nullable|string',
            'gambar_sampul' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 
        ]);

        $validated['jumlah_tersedia'] = (int) $validated['jumlah_stok'];

        if ($request->hasFile('gambar_sampul')) {
            $validated['gambar_sampul'] = $request->file('gambar_sampul')->store('public/sampul_buku');
        }

        $koleksi = Koleksi::create($validated);
        $this->generateEksemplarFisik($koleksi, (int) $validated['jumlah_stok']);

        return redirect()->route('perpustakaan.koleksi.index')->with('success', 'Master Buku & ' . $validated['jumlah_stok'] . ' Eksemplar Fisik berhasil di-generate.');
    }
    
    public function show(Koleksi $koleksi)
    {
        $koleksi->load('eksemplars');
        return view('perpustakaan.koleksi.show', compact('koleksi'));
    }

    public function edit(Koleksi $koleksi)
    {
        return view('perpustakaan.koleksi.edit', compact('koleksi'));
    }

    public function update(Request $request, Koleksi $koleksi)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|digits:4',
            'isbn' => 'nullable|string|unique:perpustakaan_koleksi,isbn,' . $koleksi->id,
            'jumlah_stok' => 'required|integer|min:0',
            'lokasi_rak' => 'required|string|max:255',
            'sinopsis' => 'nullable|string',
            'gambar_sampul' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $selisihStok = (int) $validated['jumlah_stok'] - (int) $koleksi->jumlah_stok;
        $validated['jumlah_tersedia'] = max(0, (int) $koleksi->jumlah_tersedia + $selisihStok);

        if ($request->hasFile('gambar_sampul')) {
            if ($koleksi->gambar_sampul) {
                Storage::delete($koleksi->gambar_sampul);
            }
            $validated['gambar_sampul'] = $request->file('gambar_sampul')->store('public/sampul_buku');
        }

        $koleksi->update($validated);

        if ($selisihStok > 0) {
            $this->generateEksemplarFisik($koleksi, $selisihStok);
            $pesan = 'Data buku diperbarui & ' . $selisihStok . ' Eksemplar Fisik baru berhasil di-generate.';
        } else {
            $pesan = 'Data buku berhasil diperbarui.';
        }

        return redirect()->route('perpustakaan.koleksi.index')->with('success', $pesan);
    }

    public function destroy(Koleksi $koleksi)
    {
        if ($koleksi->gambar_sampul) {
            Storage::delete($koleksi->gambar_sampul);
        }

        $koleksi->delete();
        return redirect()->route('perpustakaan.koleksi.index')->with('success', 'Master Buku & seluruh eksemplarnya berhasil dihapus.');
    }

    private function generateEksemplarFisik(Koleksi $koleksi, $jumlahTambahan)
    {
        $eksemplarBaru = [];
        $waktu = now();

        for ($i = 0; $i < $jumlahTambahan; $i++) {
            $kodeUnik = 'BK-' . str_pad($koleksi->id, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(6));
            
            $eksemplarBaru[] = [
                'koleksi_id' => $koleksi->id,
                'kode_barcode' => $kodeUnik,
                'status_fisik' => 'Tersedia',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ];
        }

        if (!empty($eksemplarBaru)) {
            PerpustakaanEksemplar::insert($eksemplarBaru);
        }
    }

    public function cetakBarcode(Koleksi $koleksi)
    {
        $koleksi->load('eksemplars');
        return view('perpustakaan.koleksi.cetak_barcode', compact('koleksi'));
    }
}