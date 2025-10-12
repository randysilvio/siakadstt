@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h1>Edit Berita / Pengumuman</h1></div>
                <div class="card-body">
                    <form action="{{ route('admin.pengumuman.update', $pengumuman->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul</label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $pengumuman->judul) }}" required>
                            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                                <option value="berita" {{ old('kategori', $pengumuman->kategori) == 'berita' ? 'selected' : '' }}>Berita</option>
                                <option value="pengumuman" {{ old('kategori', $pengumuman->kategori) == 'pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                            </select>
                            @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Sampul (Opsional)</label>
                            @if($pengumuman->foto)
                                <img src="{{ asset('storage/' . $pengumuman->foto) }}" class="img-preview img-fluid mb-3 col-sm-5 d-block" alt="Foto Sampul">
                            @endif
                            <input class="form-control @error('foto') is-invalid @enderror" type="file" id="foto" name="foto">
                            <small class="form-text">Kosongkan jika tidak ingin mengubah foto.</small>
                            @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="konten" class="form-label">Isi Konten</label>
                            <input id="konten" type="hidden" name="konten" value="{{ old('konten', $pengumuman->konten) }}">
                            <trix-editor input="konten" class="form-control @error('konten') is-invalid @enderror" style="min-height: 200px;"></trix-editor>
                            @error('konten')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="target_role" class="form-label">Tujukan Kepada</label>
                            <select class="form-select @error('target_role') is-invalid @enderror" id="target_role" name="target_role">
                                <option value="semua" {{ old('target_role', $pengumuman->target_role) == 'semua' ? 'selected' : '' }}>Semua Pengguna</option>
                                <option value="dosen" {{ old('target_role', $pengumuman->target_role) == 'dosen' ? 'selected' : '' }}>Dosen</option>
                                <option value="mahasiswa" {{ old('target_role', $pengumuman->target_role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="tendik" {{ old('target_role', $pengumuman->target_role) == 'tendik' ? 'selected' : '' }}>Tendik</option>
                            </select>
                            @error('target_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
@endpush
@push('scripts')
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
@endpush