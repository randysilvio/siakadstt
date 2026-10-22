@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Unggah Slideshow Baru</h3>
            <span class="text-muted small uppercase">Penambahan Materi Visual untuk Halaman Utama Portal</span>
        </div>
        <div>
            <a href="{{ route('admin.slideshows.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Parameter Spanduk Visual
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.slideshows.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label for="judul" class="form-label text-dark small fw-bold uppercase">Judul Publikasi (Opsional)</label>
                    <input type="text" class="form-control rounded-0 uppercase @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}" placeholder="TEKS YANG TAMPIL DI ATAS GAMBAR SLIDESHOW..." autofocus>
                    @error('judul')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="gambar" class="form-label text-dark small fw-bold uppercase">Material Visual (File Gambar) <span class="text-danger">*</span></label>
                    <input type="file" class="form-control rounded-0 @error('gambar') is-invalid @enderror" id="gambar" name="gambar" accept="image/*" required>
                    <div class="form-text mt-2 text-muted small uppercase font-monospace">
                        <i class="bi bi-info-circle me-1"></i> Resolusi optimal: <strong>1920x1080 piksel</strong> (Lanskap). Maksimal: <strong>2MB</strong>.
                    </div>
                    @error('gambar')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="urutan" class="form-label text-dark small fw-bold uppercase">Nomor Urut Tampil <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-0 font-monospace @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', 0) }}" min="0" required>
                        <div class="form-text mt-1 text-muted small uppercase">Nilai terkecil akan ditayangkan lebih awal.</div>
                        @error('urutan')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="bg-light p-3 border rounded-0 w-100 mt-2">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" checked>
                                <label class="form-check-label text-dark fw-bold uppercase small ms-2" for="is_aktif">
                                    Aktifkan Publikasi Slide
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.slideshows.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Unggah & Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection