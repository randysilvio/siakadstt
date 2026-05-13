@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- BAGIAN HEADER PENCARIAN FORMAL --}}
    <div class="card border-0 shadow-sm rounded-0 bg-dark text-white p-4 mb-5 mt-2">
        <div class="max-w-3xl mx-auto text-center py-3">
            <span class="small font-monospace text-light opacity-75 uppercase tracking-widest d-block mb-1">Pangkalan Data Pustaka</span>
            <h2 class="fw-bold uppercase text-white mb-4">Pencarian Katalog Perpustakaan</h2>
            
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <form action="{{ route('perpustakaan.index') }}" method="GET">
                        <div class="input-group rounded-0">
                            <span class="input-group-text bg-white rounded-0 border-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" class="form-control rounded-0 font-monospace uppercase border-0 py-2" name="search" placeholder="KETIK JUDUL BUKU, PENGARANG, ATAU PENERBIT..." value="{{ request('search') }}">
                            <button class="btn btn-primary rounded-0 px-4 uppercase fw-bold small" type="submit">Cari Koleksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- DAFTAR HASIL PENCARIAN BUKU --}}
    @if($koleksis->isEmpty())
        <div class="card border-0 shadow-sm rounded-0 p-5 text-center my-4">
            <div class="py-4">
                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <h6 class="uppercase fw-bold text-dark mb-1">Koleksi Tidak Ditemukan</h6>
                <p class="small text-muted uppercase font-monospace mb-0">
                    KATA KUNCI TIDAK COCOK DENGAN PANGKALAN DATA ATAU <a href="{{ route('perpustakaan.index') }}" class="text-primary fw-bold text-decoration-none">RESET PENCARIAN</a>.
                </p>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            @foreach($koleksis as $koleksi)
                <div class="col">
                    {{-- KARTU BUKU ENTERPRISE (SUDUT 0PX, BORDER TEGAS, TANPA ANIMASI MELAYANG) --}}
                    <div class="card h-100 border border-dark border-opacity-25 rounded-0 shadow-none">
                        {{-- Gambar Sampul Bingkai Kaku --}}
                        <div class="position-relative overflow-hidden bg-light border-bottom border-dark border-opacity-25" style="height: 280px;">
                            @if($koleksi->gambar_sampul)
                                <img src="{{ Storage::url($koleksi->gambar_sampul) }}" class="h-100 w-100 rounded-0" style="object-fit: cover;" alt="{{ $koleksi->judul }}">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-dark opacity-50">
                                    <div class="text-center uppercase fw-bold">
                                        <i class="bi bi-book fs-1 d-block mb-1"></i>
                                        <span class="small font-monospace" style="font-size: 10px;">TANPA SAMPUL</span>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Lencana Ketersediaan Tajam --}}
                            <div class="position-absolute top-0 end-0 m-2">
                                @if($koleksi->jumlah_tersedia > 0)
                                    <span class="badge bg-success text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">
                                        TERSEDIA: {{ $koleksi->jumlah_tersedia }}
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">
                                        STOK HABIS
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Isi Informasi Buku --}}
                        <div class="card-body d-flex flex-column p-3 bg-white">
                            <h6 class="uppercase fw-bold text-dark mb-1 line-clamp-2" style="min-height: 2.5em; line-height: 1.25;">
                                {{ $koleksi->judul }}
                            </h6>
                            <span class="text-muted small uppercase mb-3 flex-grow-1" style="font-size: 11px;">
                                <strong class="text-dark">PENGARANG:</strong> {{ $koleksi->pengarang }}
                            </span>
                            
                            {{-- Pembatas Bawah Atribut Teknis --}}
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                                <span class="badge bg-light text-dark border border-dark border-opacity-25 rounded-0 font-monospace uppercase fw-bold" style="font-size: 10px;">
                                    THN: {{ $koleksi->tahun_terbit }}
                                </span>
                                <span class="small text-muted font-monospace uppercase fw-bold" style="font-size: 11px;">
                                    <i class="bi bi-geo-alt-fill text-dark me-1"></i>RAK: {{ $koleksi->lokasi_rak }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginasi Flat Presisi --}}
        @if($koleksis->hasPages())
            <div class="d-flex justify-content-center border-top pt-4">
                {{ $koleksis->appends(request()->query())->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Membatasi judul panjang maksimal 2 baris agar seragam secara vertikal --}}
<style>
    .line-clamp-2 { 
        display: -webkit-box; 
        -webkit-line-clamp: 2; 
        -webkit-box-orient: vertical; 
        overflow: hidden; 
    }
</style>
@endsection