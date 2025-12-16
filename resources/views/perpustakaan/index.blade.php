@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Hero Section Sederhana --}}
    <div class="text-center mb-5">
        <h1 class="fw-bold display-6 mb-3">Katalog Perpustakaan</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="{{ route('perpustakaan.index') }}" method="GET">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0 ps-4"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="search" placeholder="Cari judul, pengarang, atau penerbit..." value="{{ request('search') }}">
                        <button class="btn btn-primary px-4 fw-bold" type="submit">Cari Buku</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar Buku --}}
    @if($koleksis->isEmpty())
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="opacity-50 mb-3">
            <h5 class="text-muted fw-bold">Buku Tidak Ditemukan</h5>
            <p class="text-muted">Coba kata kunci lain atau <a href="{{ route('perpustakaan.index') }}">lihat semua koleksi</a>.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            @foreach($koleksis as $koleksi)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        {{-- Gambar Sampul --}}
                        <div class="position-relative overflow-hidden bg-light" style="height: 280px;">
                            @if($koleksi->gambar_sampul)
                                <img src="{{ Storage::url($koleksi->gambar_sampul) }}" class="card-img-top h-100 w-100" style="object-fit: cover;" alt="{{ $koleksi->judul }}">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                    <div class="text-center">
                                        <i class="bi bi-book fs-1 mb-2"></i>
                                        <p class="small mb-0">No Cover</p>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Badge Status (Overlay) --}}
                            <div class="position-absolute top-0 end-0 m-3">
                                @if($koleksi->jumlah_tersedia > 0)
                                    <span class="badge bg-success shadow-sm"><i class="bi bi-check-circle me-1"></i> Tersedia</span>
                                @else
                                    <span class="badge bg-danger shadow-sm"><i class="bi bi-x-circle me-1"></i> Habis</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="card-title fw-bold text-dark mb-1 line-clamp-2" style="min-height: 2.5em;">
                                {{ Str::limit($koleksi->judul, 50) }}
                            </h6>
                            <p class="card-text text-muted small mb-2 flex-grow-1">
                                <i class="bi bi-person me-1"></i> {{ Str::limit($koleksi->pengarang, 30) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                <span class="badge bg-light text-secondary border fw-normal">{{ $koleksi->tahun_terbit }}</span>
                                <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $koleksi->lokasi_rak }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $koleksis->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<style>
    .hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
    .transition-all { transition: all 0.3s ease; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection