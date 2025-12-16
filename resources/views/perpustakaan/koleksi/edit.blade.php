@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Edit Data Buku</h2>
                    <p class="text-muted mb-0">Perbarui informasi koleksi perpustakaan.</p>
                </div>
                <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('perpustakaan.koleksi.update', $koleksi) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="judul" class="form-label fw-bold">Judul Buku</label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $koleksi->judul) }}" required>
                                    @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pengarang" class="form-label fw-bold">Pengarang</label>
                                        <input type="text" class="form-control @error('pengarang') is-invalid @enderror" id="pengarang" name="pengarang" value="{{ old('pengarang', $koleksi->pengarang) }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="penerbit" class="form-label fw-bold">Penerbit</label>
                                        <input type="text" class="form-control @error('penerbit') is-invalid @enderror" id="penerbit" name="penerbit" value="{{ old('penerbit', $koleksi->penerbit) }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tahun_terbit" class="form-label fw-bold">Tahun Terbit</label>
                                        <input type="number" class="form-control @error('tahun_terbit') is-invalid @enderror" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit', $koleksi->tahun_terbit) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="isbn" class="form-label fw-bold">ISBN</label>
                                        <input type="text" class="form-control @error('isbn') is-invalid @enderror" id="isbn" name="isbn" value="{{ old('isbn', $koleksi->isbn) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="jumlah_stok" class="form-label fw-bold">Jumlah Stok</label>
                                        <input type="number" class="form-control @error('jumlah_stok') is-invalid @enderror" id="jumlah_stok" name="jumlah_stok" value="{{ old('jumlah_stok', $koleksi->jumlah_stok) }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="sinopsis" class="form-label fw-bold">Sinopsis</label>
                                    <textarea class="form-control @error('sinopsis') is-invalid @enderror" id="sinopsis" name="sinopsis" rows="4">{{ old('sinopsis', $koleksi->sinopsis) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="lokasi_rak" class="form-label fw-bold">Lokasi Rak</label>
                                    <input type="text" class="form-control @error('lokasi_rak') is-invalid @enderror" id="lokasi_rak" name="lokasi_rak" value="{{ old('lokasi_rak', $koleksi->lokasi_rak) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Gambar Sampul</label>
                                    @if($koleksi->gambar_sampul)
                                        <div class="mb-2 position-relative">
                                            <img src="{{ Storage::url($koleksi->gambar_sampul) }}" alt="Sampul" class="img-fluid rounded border shadow-sm w-100">
                                            <div class="badge bg-dark position-absolute bottom-0 start-0 m-2 opacity-75">Saat Ini</div>
                                        </div>
                                    @endif
                                    <input class="form-control @error('gambar_sampul') is-invalid @enderror" type="file" id="gambar_sampul" name="gambar_sampul">
                                    <div class="form-text text-muted small">Upload baru untuk mengganti.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-2">
                            <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-light border px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection