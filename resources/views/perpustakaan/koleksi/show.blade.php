@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Spesifikasi Detail Koleksi</h3>
            <span class="text-muted small uppercase">Informasi Fisik, Lokasi Rak, & Sirkulasi Ketersediaan Buku</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Detail Informasi Buku
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-3 bg-light d-flex align-items-center justify-content-center p-4 border-end border-dark border-opacity-25">
                    @if($koleksi->gambar_sampul)
                        <img src="{{ Storage::url($koleksi->gambar_sampul) }}" alt="Sampul Buku" class="img-fluid rounded-0 border border-dark border-opacity-25 shadow-none" style="max-height: 320px; object-fit: cover;">
                    @else
                        <div class="text-center text-muted uppercase fw-bold">
                            <i class="bi bi-book fs-1 d-block mb-2"></i>
                            <span class="small font-monospace">SAMPUL KOSONG</span>
                        </div>
                    @endif
                </div>

                <div class="col-md-9 p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                        <div>
                            <span class="small text-muted font-monospace uppercase d-block">NOMOR IDENTITAS BUKU (ISBN):</span>
                            <span class="fw-bold fs-6 font-monospace text-dark">{{ $koleksi->isbn ?? 'TIDAK TERSEDIA' }}</span>
                        </div>
                        
                        <div class="btn-group rounded-0">
                            <a href="{{ route('perpustakaan.koleksi.edit', $koleksi) }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                                <i class="bi bi-pencil-square me-1"></i> Edit Data
                            </a>
                            <form action="{{ route('perpustakaan.koleksi.destroy', $koleksi) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus master buku ini secara permanen?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger rounded-0 px-3 uppercase fw-bold small">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <h2 class="fw-bold text-dark uppercase mb-4">{{ $koleksi->judul }}</h2>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <span class="text-muted small uppercase fw-bold d-block">PENGARANG UTAMA</span>
                            <strong class="text-dark fs-6 uppercase">{{ $koleksi->pengarang }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small uppercase fw-bold d-block">PENERBIT & TAHUN</span>
                            <strong class="text-dark fs-6 uppercase">{{ $koleksi->penerbit }}</strong>
                            <span class="text-muted font-monospace ms-1">({{ $koleksi->tahun_terbit }})</span>
                        </div>
                    </div>

                    <div class="bg-light border border-dark border-opacity-25 rounded-0 p-3 mb-4">
                        <div class="row text-center g-2 align-items-center">
                            <div class="col-4 border-end border-dark border-opacity-25">
                                <span class="small text-muted uppercase fw-bold d-block">STOK TOTAL</span>
                                <span class="fw-bold fs-4 font-monospace text-dark">{{ $koleksi->jumlah_stok }}</span>
                            </div>
                            <div class="col-4 border-end border-dark border-opacity-25">
                                <span class="small text-muted uppercase fw-bold d-block">TERSEDIA</span>
                                <span class="fw-bold fs-4 font-monospace {{ $koleksi->jumlah_tersedia > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $koleksi->jumlah_tersedia }}
                                </span>
                            </div>
                            <div class="col-4">
                                <span class="small text-muted uppercase fw-bold d-block">LOKASI RAK</span>
                                <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 12px;">
                                    {{ $koleksi->lokasi_rak }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark uppercase mb-2 border-bottom pb-1">Sinopsis & Uraian Konten</h6>
                    <p class="text-dark small uppercase" style="line-height: 1.8; text-align: justify;">
                        {{ $koleksi->sinopsis ?? 'TIDAK ADA SINOPSIS ATAU URAIAN RINGKAS UNTUK BUKU INI.' }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-5 mb-3 border-bottom pb-2">
                        <h6 class="fw-bold text-dark uppercase mb-0">
                            <i class="bi bi-upc-scan me-2"></i>Daftar Eksemplar Fisik & Barcode
                        </h6>
                        {{-- TOMBOL CETAK DIPERBARUI --}}
                        <a href="{{ route('perpustakaan.koleksi.cetak-barcode', $koleksi) }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold font-monospace shadow-sm" style="font-size: 11px;">
                            <i class="bi bi-printer-fill me-1"></i> Cetak Label Stiker
                        </a>
                    </div>
                    
                    <div class="table-responsive border border-dark border-opacity-25 rounded-0">
                        <table class="table table-hover table-borderless table-sm mb-0 align-middle">
                            <thead class="bg-light border-bottom">
                                <tr>
                                    <th class="ps-3 text-muted small fw-bold uppercase" style="width: 5%;">#</th>
                                    <th class="text-muted small fw-bold uppercase" style="width: 35%;">Kode Label Fisik (Barcode)</th>
                                    <th class="text-muted small fw-bold uppercase" style="width: 25%;">Tgl. Registrasi</th>
                                    <th class="text-center text-muted small fw-bold uppercase" style="width: 20%;">Kondisi/Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($koleksi->eksemplars) && $koleksi->eksemplars->count() > 0)
                                    @foreach($koleksi->eksemplars as $index => $eks)
                                        <tr class="border-bottom">
                                            <td class="ps-3 text-muted small font-monospace">{{ $index + 1 }}</td>
                                            <td>
                                                <span class="fw-bold text-dark font-monospace fs-6">{{ $eks->kode_barcode }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted small font-monospace">{{ $eks->created_at->format('d/m/Y H:i') }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($eks->status_fisik == 'Tersedia')
                                                    <span class="badge bg-success text-white rounded-0 uppercase font-monospace px-2 py-1" style="font-size: 10px;">TERSEDIA</span>
                                                @elseif($eks->status_fisik == 'Dipinjam')
                                                    <span class="badge bg-warning text-dark rounded-0 uppercase font-monospace px-2 py-1" style="font-size: 10px;">DIPINJAM</span>
                                                @else
                                                    <span class="badge bg-danger text-white rounded-0 uppercase font-monospace px-2 py-1" style="font-size: 10px;">{{ strtoupper($eks->status_fisik) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small uppercase fw-bold">
                                            <i class="bi bi-box-seam fs-4 d-block mb-1 opacity-50"></i>
                                            Belum ada eksemplar fisik yang diregistrasikan.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="text-muted small uppercase mt-2 font-monospace" style="font-size: 10px;">
                        * Tempelkan label barcode pada sampul belakang setiap buku fisik untuk di-scan oleh alat pembaca (scanner) pada meja sirkulasi/kasir.
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection