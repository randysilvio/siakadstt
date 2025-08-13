@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail Koleksi Buku</h1>
        <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    @if($koleksi->gambar_sampul)
                        <img src="{{ Storage::url($koleksi->gambar_sampul) }}" alt="Sampul Buku" class="img-fluid rounded shadow">
                    @else
                        <div class="bg-light d-flex justify-content-center align-items-center rounded shadow" style="height: 300px;">
                            <span>Tidak ada gambar sampul</span>
                        </div>
                    @endif
                </div>
                <div class="col-md-8">
                    <h3 class="mt-3 mt-md-0">{{ $koleksi->judul }}</h3>
                    <p class="text-muted"><strong>Pengarang:</strong> {{ $koleksi->pengarang }}</p>
                    <p><strong>Penerbit:</strong> {{ $koleksi->penerbit }}</p>
                    <p><strong>Tahun Terbit:</strong> {{ $koleksi->tahun_terbit }}</p>
                    <p><strong>ISBN:</strong> {{ $koleksi->isbn ?? '-' }}</p>
                    <p><strong>Stok Tersedia:</strong> {{ $koleksi->jumlah_tersedia }} dari {{ $koleksi->jumlah_stok }}</p>
                    <p><strong>Lokasi Rak:</strong> {{ $koleksi->lokasi_rak }}</p>
                    <hr>
                    <h4>Sinopsis</h4>
                    <p>{{ $koleksi->sinopsis ?? 'Tidak ada sinopsis.' }}</p>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('perpustakaan.koleksi.edit', $koleksi) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>
</div>
@endsection