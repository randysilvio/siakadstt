<?php

namespace App\Http\Controllers;

use App\Models\Koleksi;
use App\Models\Peminjaman;
use App\Models\PerpustakaanEksemplar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KoleksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Koleksi::withCount('eksemplars')->latest();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                  ->orWhere('pengarang', 'like', "%{$s}%")
                  ->orWhere('isbn', 'like', "%{$s}%")
                  ->orWhere('lokasi_rak', 'like', "%{$s}%");
            });
        }

        $koleksis = $query->paginate(10)->withQueryString();
        return view('perpustakaan.koleksi.index', compact('koleksis'));
    }

    public function create()
    {
        return view('perpustakaan.koleksi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'         => 'required|string|max:255',
            'pengarang'     => 'required|string|max:255',
            'penerbit'      => 'required|string|max:255',
            'tahun_terbit'  => 'required|digits:4',
            'isbn'          => 'nullable|string|unique:perpustakaan_koleksi,isbn',
            'jumlah_stok'   => 'required|integer|min:1',
            'lokasi_rak'    => 'required|string|max:255',
            'sinopsis'      => 'nullable|string',
            'gambar_sampul' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 
        ]);

        $validated['jumlah_tersedia'] = (int) $validated['jumlah_stok'];

        if ($request->hasFile('gambar_sampul')) {
            $validated['gambar_sampul'] = $request->file('gambar_sampul')->store('sampul_buku', 'public');
        }

        DB::transaction(function () use ($validated) {
            $koleksi = Koleksi::create($validated);
            $this->generateEksemplarFisik($koleksi, (int) $validated['jumlah_stok']);
        });

        return redirect()->route('perpustakaan.koleksi.index')
            ->with('success', 'Master Buku & ' . $validated['jumlah_stok'] . ' Eksemplar Fisik ber-barcode berhasil di-generate.');
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
            'judul'         => 'required|string|max:255',
            'pengarang'     => 'required|string|max:255',
            'penerbit'      => 'required|string|max:255',
            'tahun_terbit'  => 'required|digits:4',
            'isbn'          => 'nullable|string|unique:perpustakaan_koleksi,isbn,' . $koleksi->id,
            'jumlah_stok'   => 'required|integer|min:0',
            'lokasi_rak'    => 'required|string|max:255',
            'sinopsis'      => 'nullable|string',
            'gambar_sampul' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Hitung berapa eksemplar dari buku ini yang sedang dipinjam aktif
        $sedangDipinjam = Peminjaman::where('koleksi_id', $koleksi->id)
            ->where('status', 'Dipinjam')
            ->count();

        $stokBaru = (int) $validated['jumlah_stok'];
        if ($stokBaru < $sedangDipinjam) {
            return back()->withInput()->with('error', "Gagal memperbarui stok: Saat ini terdapat {$sedangDipinjam} eksemplar buku yang sedang dipinjam aktif oleh pemustaka. Jumlah stok total tidak boleh kurang dari {$sedangDipinjam}.");
        }

        $selisihStok = $stokBaru - (int) $koleksi->jumlah_stok;
        $validated['jumlah_tersedia'] = max(0, $stokBaru - $sedangDipinjam);

        if ($request->hasFile('gambar_sampul')) {
            if ($koleksi->gambar_sampul && Storage::disk('public')->exists($koleksi->gambar_sampul)) {
                Storage::disk('public')->delete($koleksi->gambar_sampul);
            }
            $validated['gambar_sampul'] = $request->file('gambar_sampul')->store('sampul_buku', 'public');
        }

        DB::transaction(function () use ($koleksi, $validated, $selisihStok, $stokBaru) {
            $koleksi->update($validated);

            if ($selisihStok > 0) {
                $this->generateEksemplarFisik($koleksi, $selisihStok);
            } elseif ($selisihStok < 0) {
                // Hapus eksemplar yang berstatus 'Tersedia' dan belum pernah memiliki riwayat peminjaman terlebih dahulu
                $jumlahHapus = abs($selisihStok);
                $eksemplarTersediaIds = PerpustakaanEksemplar::where('koleksi_id', $koleksi->id)
                    ->where('status_fisik', 'Tersedia')
                    ->orderBy('id', 'desc')
                    ->limit($jumlahHapus)
                    ->pluck('id');

                if ($eksemplarTersediaIds->isNotEmpty()) {
                    PerpustakaanEksemplar::whereIn('id', $eksemplarTersediaIds)->delete();
                }
            }

            // Self-healing jika master buku lama belum memiliki eksemplar fisik sama sekali
            $totalEksSekarang = PerpustakaanEksemplar::where('koleksi_id', $koleksi->id)->count();
            if ($totalEksSekarang < $stokBaru) {
                $this->generateEksemplarFisik($koleksi, $stokBaru - $totalEksSekarang);
            }
        });

        if ($selisihStok > 0) {
            $pesan = 'Data buku diperbarui & ' . $selisihStok . ' Eksemplar Fisik baru berhasil di-generate.';
        } elseif ($selisihStok < 0) {
            $pesan = 'Data buku diperbarui & ' . abs($selisihStok) . ' Eksemplar tersedia telah dikurangi dari inventaris.';
        } else {
            $pesan = 'Data buku berhasil diperbarui.';
        }

        return redirect()->route('perpustakaan.koleksi.index')->with('success', $pesan);
    }

    public function destroy(Koleksi $koleksi)
    {
        $sedangDipinjam = Peminjaman::where('koleksi_id', $koleksi->id)
            ->where('status', 'Dipinjam')
            ->exists();

        if ($sedangDipinjam) {
            return redirect()->route('perpustakaan.koleksi.index')
                ->with('error', 'Tidak dapat menghapus Master Buku ini karena masih ada eksemplar yang sedang dipinjam oleh pemustaka.');
        }

        if ($koleksi->gambar_sampul && Storage::disk('public')->exists($koleksi->gambar_sampul)) {
            Storage::disk('public')->delete($koleksi->gambar_sampul);
        }

        $koleksi->delete();
        return redirect()->route('perpustakaan.koleksi.index')->with('success', 'Master Buku & seluruh eksemplarnya berhasil dihapus.');
    }

    private function generateEksemplarFisik(Koleksi $koleksi, int $jumlahTambahan): void
    {
        $eksemplarBaru = [];
        $waktu = now();

        for ($i = 0; $i < $jumlahTambahan; $i++) {
            do {
                $kodeUnik = 'BK-' . str_pad((string) $koleksi->id, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(6));
            } while (PerpustakaanEksemplar::where('kode_barcode', $kodeUnik)->exists());
            
            $eksemplarBaru[] = [
                'koleksi_id'   => $koleksi->id,
                'kode_barcode' => $kodeUnik,
                'status_fisik' => 'Tersedia',
                'created_at'   => $waktu,
                'updated_at'   => $waktu,
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