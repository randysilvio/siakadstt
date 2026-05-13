@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Tambah Koleksi Buku Baru</h3>
            <span class="text-muted small uppercase">Entri Master Data Katalog & Lokasi Penyimpanan Fisik</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-10 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Spesifikasi Buku
        </div>
        <div class="card-body p-4">
            <form action="{{ route('perpustakaan.koleksi.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row g-4">
                    {{-- Kolom Kiri: Info Utama --}}
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="judul" class="form-label small fw-bold uppercase text-dark">Judul Buku <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-0 uppercase @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}" required placeholder="JUDUL LENGKAP...">
                            @error('judul') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="pengarang" class="form-label small fw-bold uppercase text-dark">Pengarang <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light rounded-0"><i class="bi bi-person text-dark"></i></span>
                                    <input type="text" class="form-control rounded-0 uppercase @error('pengarang') is-invalid @enderror" id="pengarang" name="pengarang" value="{{ old('pengarang') }}" required>
                                </div>
                                @error('pengarang') <div class="invalid-feedback d-block font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="penerbit" class="form-label small fw-bold uppercase text-dark">Penerbit <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light rounded-0"><i class="bi bi-building text-dark"></i></span>
                                    <input type="text" class="form-control rounded-0 uppercase @error('penerbit') is-invalid @enderror" id="penerbit" name="penerbit" value="{{ old('penerbit') }}" required>
                                </div>
                                @error('penerbit') <div class="invalid-feedback d-block font-monospace small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="tahun_terbit" class="form-label small fw-bold uppercase text-dark">Tahun Terbit <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rounded-0 font-monospace @error('tahun_terbit') is-invalid @enderror" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit') }}" min="1900" max="{{ date('Y') + 1 }}" required>
                                @error('tahun_terbit') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="isbn" class="form-label small fw-bold uppercase text-dark">ISBN</label>
                                <input type="text" class="form-control rounded-0 font-monospace @error('isbn') is-invalid @enderror" id="isbn" name="isbn" value="{{ old('isbn') }}" placeholder="978-...">
                                @error('isbn') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="jumlah_stok" class="form-label small fw-bold uppercase text-dark">Jumlah Stok Awal <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rounded-0 font-monospace @error('jumlah_stok') is-invalid @enderror" id="jumlah_stok" name="jumlah_stok" value="{{ old('jumlah_stok') }}" min="1" required>
                                @error('jumlah_stok') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sinopsis" class="form-label small fw-bold uppercase text-dark">Sinopsis / Deskripsi Ringkas</label>
                            <textarea class="form-control rounded-0 uppercase @error('sinopsis') is-invalid @enderror" id="sinopsis" name="sinopsis" rows="4" placeholder="SINOPSIS BUKU...">{{ old('sinopsis') }}</textarea>
                            @error('sinopsis') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Kolom Kanan: Fisik & Lokasi --}}
                    <div class="col-md-4">
                        <div class="card bg-light border border-dark border-opacity-25 rounded-0 mb-4">
                            <div class="card-header bg-light border-bottom rounded-0 uppercase fw-bold small text-dark">
                                Alokasi Penyimpanan Fisik
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label for="lokasi_rak" class="form-label small fw-bold uppercase text-dark">Nomor Rak / Lemari <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-0 font-monospace uppercase @error('lokasi_rak') is-invalid @enderror" id="lokasi_rak" name="lokasi_rak" value="{{ old('lokasi_rak') }}" placeholder="RAK-A-01" required>
                                    @error('lokasi_rak') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="gambar_sampul" class="form-label small fw-bold uppercase text-dark">Gambar Sampul</label>
                            <input class="form-control rounded-0 @error('gambar_sampul') is-invalid @enderror" type="file" id="gambar_sampul" name="gambar_sampul" accept="image/*">
                            <div class="form-text text-muted small uppercase mt-1">Format: JPG, PNG (Maks. 2MB)</div>
                            @error('gambar_sampul') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Buku
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection