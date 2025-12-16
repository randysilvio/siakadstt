@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Detail Buku</h2>
            <p class="text-muted mb-0">Informasi lengkap koleksi perpustakaan.</p>
        </div>
        <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="card shadow-lg border-0 overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0">
                {{-- Kolom Kiri: Gambar Sampul --}}
                <div class="col-md-4 bg-light d-flex align-items-center justify-content-center p-4 border-end">
                    @if($koleksi->gambar_sampul)
                        <img src="{{ Storage::url($koleksi->gambar_sampul) }}" alt="Sampul Buku" class="img-fluid rounded shadow" style="max-height: 400px;">
                    @else
                        <div class="text-center text-muted opacity-50">
                            <i class="bi bi-book fs-1 display-1"></i>
                            <p class="mt-2">Tidak ada sampul</p>
                        </div>
                    @endif
                </div>

                {{-- Kolom Kanan: Detail Informasi --}}
                <div class="col-md-8 p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill">
                            <i class="bi bi-upc-scan me-1"></i> ISBN: {{ $koleksi->isbn ?? 'Tidak ada' }}
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear-fill text-secondary"></i> Opsi
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="{{ route('perpustakaan.koleksi.edit', $koleksi) }}"><i class="bi bi-pencil me-2"></i>Edit Buku</a></li>
                                <li>
                                    <form action="{{ route('perpustakaan.koleksi.destroy', $koleksi) }}" method="POST" onsubmit="return confirm('Hapus buku ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Hapus Buku</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <h1 class="fw-bold text-dark mb-3">{{ $koleksi->judul }}</h1>
                    
                    <div class="row mb-4">
                        <div class="col-sm-6 mb-2">
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Pengarang</p>
                            <p class="fw-bold text-dark fs-5">{{ $koleksi->pengarang }}</p>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Penerbit & Tahun</p>
                            <p class="fw-bold text-dark fs-5">{{ $koleksi->penerbit }}, {{ $koleksi->tahun_terbit }}</p>
                        </div>
                    </div>

                    <div class="alert alert-light border d-flex align-items-center mb-4">
                        <div class="me-4 text-center">
                            <small class="text-muted d-block">Stok Total</small>
                            <span class="fw-bold fs-4">{{ $koleksi->jumlah_stok }}</span>
                        </div>
                        <div class="me-4 text-center border-start ps-4">
                            <small class="text-muted d-block">Tersedia</small>
                            <span class="fw-bold fs-4 {{ $koleksi->jumlah_tersedia > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $koleksi->jumlah_tersedia }}
                            </span>
                        </div>
                        <div class="ms-auto text-end">
                            <small class="text-muted d-block">Lokasi Rak</small>
                            <span class="badge bg-secondary">{{ $koleksi->lokasi_rak }}</span>
                        </div>
                    </div>

                    <h5 class="fw-bold text-secondary mb-2"><i class="bi bi-text-paragraph me-2"></i>Sinopsis</h5>
                    <p class="text-muted" style="line-height: 1.8;">
                        {{ $koleksi->sinopsis ?? 'Belum ada sinopsis untuk buku ini.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection