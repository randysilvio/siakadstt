@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Unggah Dokumen Publik</h3>
                    <span class="text-muted small">Publikasi dokumen resmi untuk diakses oleh civitas akademika</span>
                </div>
                <a href="{{ route('admin.dokumen-publik.index') }}" class="btn btn-outline-dark btn-sm">Batal</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.dokumen-publik.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="judul_dokumen" class="form-label text-dark fw-semibold">Judul Dokumen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-1 @error('judul_dokumen') is-invalid @enderror" id="judul_dokumen" name="judul_dokumen" value="{{ old('judul_dokumen') }}" placeholder="Contoh: Pedoman Akademik Tahun 2026" required autofocus>
                            @error('judul_dokumen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="deskripsi" class="form-label text-dark fw-semibold">Deskripsi Singkat (Opsional)</label>
                            <textarea class="form-control rounded-1 @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3" placeholder="Jelaskan secara ringkas isi dari dokumen ini...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="file_dokumen" class="form-label text-dark fw-semibold">Pilih File <span class="text-danger">*</span></label>
                            <input class="form-control rounded-1 @error('file_dokumen') is-invalid @enderror" type="file" id="file_dokumen" name="file_dokumen" required>
                            <div class="form-text mt-2 text-muted">
                                <i class="bi bi-info-circle me-1"></i> Format yang diizinkan: <strong>PDF, DOC, DOCX, XLS, XLSX</strong>. Ukuran maksimal: <strong>5MB</strong>.
                            </div>
                            @error('file_dokumen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5">Simpan & Publikasikan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection