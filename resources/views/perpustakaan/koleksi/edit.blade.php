@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Edit Buku: {{ $koleksi->judul }}</h2>
            <hr>
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('perpustakaan.koleksi.update', $koleksi) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Buku</label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $koleksi->judul) }}">
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pengarang" class="form-label">Pengarang</label>
                            <input type="text" class="form-control @error('pengarang') is-invalid @enderror" id="pengarang" name="pengarang" value="{{ old('pengarang', $koleksi->pengarang) }}">
                            @error('pengarang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="penerbit" class="form-label">Penerbit</label>
                            <input type="text" class="form-control @error('penerbit') is-invalid @enderror" id="penerbit" name="penerbit" value="{{ old('penerbit', $koleksi->penerbit) }}">
                            @error('penerbit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                            <input type="number" class="form-control @error('tahun_terbit') is-invalid @enderror" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit', $koleksi->tahun_terbit) }}">
                            @error('tahun_terbit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control @error('isbn') is-invalid @enderror" id="isbn" name="isbn" value="{{ old('isbn', $koleksi->isbn) }}">
                            @error('isbn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="jumlah_stok" class="form-label">Jumlah Stok</label>
                            <input type="number" class="form-control @error('jumlah_stok') is-invalid @enderror" id="jumlah_stok" name="jumlah_stok" value="{{ old('jumlah_stok', $koleksi->jumlah_stok) }}">
                            @error('jumlah_stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="lokasi_rak" class="form-label">Lokasi Rak</label>
                            <input type="text" class="form-control @error('lokasi_rak') is-invalid @enderror" id="lokasi_rak" name="lokasi_rak" value="{{ old('lokasi_rak', $koleksi->lokasi_rak) }}">
                            @error('lokasi_rak')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sinopsis" class="form-label">Sinopsis</label>
                            <textarea class="form-control @error('sinopsis') is-invalid @enderror" id="sinopsis" name="sinopsis" rows="3">{{ old('sinopsis', $koleksi->sinopsis) }}</textarea>
                            @error('sinopsis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="gambar_sampul" class="form-label">Gambar Sampul</label>
                            <input class="form-control @error('gambar_sampul') is-invalid @enderror" type="file" id="gambar_sampul" name="gambar_sampul">
                            @error('gambar_sampul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($koleksi->gambar_sampul)
                                <div class="mt-2">
                                    <img src="{{ Storage::url($koleksi->gambar_sampul) }}" alt="Sampul Saat Ini" width="150">
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection