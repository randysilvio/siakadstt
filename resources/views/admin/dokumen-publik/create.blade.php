@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Unggah Dokumen Publik Baru</h3>
            <span class="text-muted small uppercase">Publikasi Dokumen Resmi untuk Diakses oleh Civitas Akademika</span>
        </div>
        <div>
            <a href="{{ route('admin.dokumen-publik.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Unggah Berkas Publik
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.dokumen-publik.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label for="judul_dokumen" class="form-label text-dark small fw-bold uppercase">Judul Dokumen <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-0 uppercase @error('judul_dokumen') is-invalid @enderror" id="judul_dokumen" name="judul_dokumen" value="{{ old('judul_dokumen') }}" placeholder="CONTOH: PEDOMAN AKADEMIK TAHUN 2026" required autofocus>
                    @error('judul_dokumen')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="kategori" class="form-label text-dark small fw-bold uppercase">Kategori Dokumen <span class="text-danger">*</span></label>
                    <select class="form-select rounded-0 uppercase @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                        <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>-- PILIH KATEGORI --</option>
                        <option value="Akademik" {{ old('kategori') == 'Akademik' ? 'selected' : '' }}>Akademik & Pedoman</option>
                        <option value="Kemahasiswaan" {{ old('kategori') == 'Kemahasiswaan' ? 'selected' : '' }}>Kemahasiswaan</option>
                        <option value="Keuangan" {{ old('kategori') == 'Keuangan' ? 'selected' : '' }}>Keuangan & Biaya</option>
                        <option value="Formulir" {{ old('kategori') == 'Formulir' ? 'selected' : '' }}>Formulir & Template</option>
                        <option value="Umum" {{ old('kategori') == 'Umum' ? 'selected' : '' }}>Umum / Lainnya</option>
                    </select>
                    @error('kategori')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="deskripsi" class="form-label text-dark small fw-bold uppercase">Deskripsi Singkat (Opsional)</label>
                    <textarea class="form-control rounded-0 uppercase @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3" placeholder="JELASKAN SECARA RINGKAS ISI DARI DOKUMEN INI...">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="file_dokumen" class="form-label text-dark small fw-bold uppercase">Pilih Berkas Dokumen <span class="text-danger">*</span></label>
                    <input class="form-control rounded-0 @error('file_dokumen') is-invalid @enderror" type="file" id="file_dokumen" name="file_dokumen" required>
                    <div class="form-text mt-2 text-muted small uppercase font-monospace">
                        <i class="bi bi-info-circle me-1"></i> Format diizinkan: <strong>PDF, DOC, DOCX, XLS, XLSX</strong>. Maksimal: <strong>10MB</strong>.
                    </div>
                    @error('file_dokumen')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.dokumen-publik.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan & Publikasikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection