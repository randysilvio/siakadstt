@extends('layouts.app')

@push('styles')
<style>
    .book-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 0;
    }
    .book-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15)!important;
    }
    .book-cover {
        width: 100%;
        height: 250px;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="text-center mb-5">
        <h1>Katalog Perpustakaan</h1>
        <p class="lead text-muted">Cari koleksi buku yang tersedia di perpustakaan STT GPI Papua.</p>
    </div>

    {{-- Form Pencarian --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <form action="{{ route('perpustakaan.index') }}" method="GET">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan judul, pengarang, atau penerbit..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Daftar Koleksi Buku --}}
    @if($koleksis->isEmpty())
        <div class="text-center py-5">
            <p class="lead">Buku tidak ditemukan.</p>
            <a href="{{ route('perpustakaan.index') }}">Lihat semua koleksi</a>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach($koleksis as $koleksi)
                <div class="col">
                    <div class="card h-100 shadow-sm book-card">
                        {{-- PENTING: Pastikan Anda sudah menjalankan `php artisan storage:link` agar gambar sampul bisa tampil --}}
                        <img src="{{ $koleksi->gambar_sampul ? Storage::url($koleksi->gambar_sampul) : 'https://via.placeholder.com/150x250.png?text=No+Cover' }}" class="card-img-top book-cover" alt="Sampul {{ $koleksi->judul }}">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ Str::limit($koleksi->judul, 45) }}</h6>
                            <p class="card-text text-muted small flex-grow-1">{{ $koleksi->pengarang }}</p>
                            <span class="badge {{ $koleksi->jumlah_tersedia > 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $koleksi->jumlah_tersedia > 0 ? 'Tersedia' : 'Dipinjam' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{-- Kode ini sudah benar, akan mempertahankan query pencarian saat berpindah halaman --}}
            {{ $koleksis->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
