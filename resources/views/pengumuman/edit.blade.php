@extends('layouts.app')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Berita / Pengumuman</h3>
            <span class="text-muted small uppercase">Pembaruan Konten & Spesifikasi Distribusi Target</span>
        </div>
        <div>
            <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-9 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Pembaruan Publikasi
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.pengumuman.update', $pengumuman->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row g-3 mb-3">
                    {{-- Judul --}}
                    <div class="col-md-8">
                        <label for="judul" class="form-label small fw-bold uppercase text-dark">Judul Publikasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 uppercase @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $pengumuman->judul) }}" required>
                        @error('judul')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>

                    {{-- Kategori --}}
                    <div class="col-md-4">
                        <label for="kategori" class="form-label small fw-bold uppercase text-dark">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select rounded-0 uppercase @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                            <option value="berita" {{ old('kategori', $pengumuman->kategori) == 'berita' ? 'selected' : '' }}>Berita</option>
                            <option value="pengumuman" {{ old('kategori', $pengumuman->kategori) == 'pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                        </select>
                        @error('kategori')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    {{-- Target Role --}}
                    <div class="col-md-6">
                        <label for="target_role" class="form-label small fw-bold uppercase text-dark">Tujukan Kepada <span class="text-danger">*</span></label>
                        <select class="form-select rounded-0 uppercase @error('target_role') is-invalid @enderror" id="target_role" name="target_role">
                            <option value="semua" {{ old('target_role', $pengumuman->target_role) == 'semua' ? 'selected' : '' }}>Semua Pengguna</option>
                            <option value="dosen" {{ old('target_role', $pengumuman->target_role) == 'dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="mahasiswa" {{ old('target_role', $pengumuman->target_role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="tendik" {{ old('target_role', $pengumuman->target_role) == 'tendik' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                        </select>
                        @error('target_role')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>

                    {{-- Foto Sampul --}}
                    <div class="col-md-6">
                        <label for="foto" class="form-label small fw-bold uppercase text-dark">Foto Sampul (Opsional)</label>
                        @if($pengumuman->foto)
                            <div class="mb-2 border border-dark border-opacity-25 p-1 rounded-0" style="max-width: 150px;">
                                <img src="{{ asset('storage/' . $pengumuman->foto) }}" class="img-fluid d-block rounded-0" alt="Sampul">
                            </div>
                        @endif
                        <input class="form-control rounded-0 @error('foto') is-invalid @enderror" type="file" id="foto" name="foto">
                        <div class="form-text text-muted small uppercase mt-1">* Kosongkan jika tidak ingin mengubah foto saat ini.</div>
                        @error('foto')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Konten WYSIWYG --}}
                <div class="mb-4">
                    <label for="konten" class="form-label small fw-bold uppercase text-dark">Isi Konten Lengkap <span class="text-danger">*</span></label>
                    <input id="konten" type="hidden" name="konten" value="{{ old('konten', $pengumuman->konten) }}">
                    <trix-editor input="konten" class="form-control rounded-0 @error('konten') is-invalid @enderror" style="min-height: 250px;"></trix-editor>
                    @error('konten')<div class="text-danger font-monospace small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
@endpush