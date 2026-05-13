@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Unggah Slideshow Baru</h3>
                    <span class="text-muted small">Penambahan materi visual untuk halaman utama portal</span>
                </div>
                <a href="{{ route('admin.slideshows.index') }}" class="btn btn-outline-dark btn-sm rounded-1">Batal</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.slideshows.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="judul" class="form-label text-dark fw-semibold">Judul Publikasi (Opsional)</label>
                            <input type="text" class="form-control rounded-1 @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}" placeholder="Teks yang akan melayang di atas gambar (jika diperlukan)..." autofocus>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="gambar" class="form-label text-dark fw-semibold">Material Visual (File Gambar) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control rounded-1 @error('gambar') is-invalid @enderror" id="gambar" name="gambar" accept="image/*" required>
                            <div class="form-text mt-2 text-muted small">
                                <i class="bi bi-info-circle me-1"></i> Standar resolusi optimal: <strong>1920x1080 piksel</strong> (Format lanskap). Ukuran berkas maksimal: <strong>2MB</strong>.
                            </div>
                            @error('gambar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 w-50">
                            <label for="urutan" class="form-label text-dark fw-semibold">Hierarki Tampil (Nomor Urut) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control rounded-1 font-monospace @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', 0) }}" required>
                            <div class="form-text mt-1 text-muted small">Indeks numerik. Nilai terkecil akan ditayangkan lebih awal.</div>
                            @error('urutan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 bg-light p-3 border rounded-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" checked>
                                <label class="form-check-label text-dark fw-bold ms-2" for="is_aktif">
                                    Aktifkan Publikasi Slide
                                </label>
                            </div>
                            <div class="form-text mt-2">Gambar yang diaktifkan akan langsung terlihat oleh pengunjung (Publik).</div>
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-1">Unggah & Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection