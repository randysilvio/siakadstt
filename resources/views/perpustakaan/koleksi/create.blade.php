@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Tambah Buku Baru</h2>
                    <p class="text-muted mb-0">Masukkan data buku ke dalam sistem.</p>
                </div>
                <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('perpustakaan.koleksi.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            {{-- Kolom Kiri: Info Utama --}}
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="judul" class="form-label fw-bold">Judul Buku</label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}" required placeholder="Masukkan judul lengkap...">
                                    @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pengarang" class="form-label fw-bold">Pengarang</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control @error('pengarang') is-invalid @enderror" id="pengarang" name="pengarang" value="{{ old('pengarang') }}" required>
                                        </div>
                                        @error('pengarang') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="penerbit" class="form-label fw-bold">Penerbit</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                            <input type="text" class="form-control @error('penerbit') is-invalid @enderror" id="penerbit" name="penerbit" value="{{ old('penerbit') }}" required>
                                        </div>
                                        @error('penerbit') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tahun_terbit" class="form-label fw-bold">Tahun Terbit</label>
                                        <input type="number" class="form-control @error('tahun_terbit') is-invalid @enderror" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit') }}" min="1900" max="{{ date('Y') + 1 }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="isbn" class="form-label fw-bold">ISBN</label>
                                        <input type="text" class="form-control @error('isbn') is-invalid @enderror" id="isbn" name="isbn" value="{{ old('isbn') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="jumlah_stok" class="form-label fw-bold">Jumlah Stok</label>
                                        <input type="number" class="form-control @error('jumlah_stok') is-invalid @enderror" id="jumlah_stok" name="jumlah_stok" value="{{ old('jumlah_stok') }}" min="1" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="sinopsis" class="form-label fw-bold">Sinopsis</label>
                                    <textarea class="form-control @error('sinopsis') is-invalid @enderror" id="sinopsis" name="sinopsis" rows="4">{{ old('sinopsis') }}</textarea>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Fisik & Lokasi --}}
                            <div class="col-md-4">
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">Lokasi Penyimpanan</h6>
                                        <div class="mb-3">
                                            <label for="lokasi_rak" class="form-label">Nomor Rak / Lemari</label>
                                            <input type="text" class="form-control @error('lokasi_rak') is-invalid @enderror" id="lokasi_rak" name="lokasi_rak" value="{{ old('lokasi_rak') }}" placeholder="Cth: RAK-A-01" required>
                                            @error('lokasi_rak') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="gambar_sampul" class="form-label fw-bold">Gambar Sampul</label>
                                    <input class="form-control @error('gambar_sampul') is-invalid @enderror" type="file" id="gambar_sampul" name="gambar_sampul" accept="image/*">
                                    <div class="form-text small">Format: JPG, PNG (Maks. 2MB)</div>
                                    @error('gambar_sampul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-2">
                            <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-light border px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Buku
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection