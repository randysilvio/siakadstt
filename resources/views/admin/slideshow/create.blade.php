@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h1>Tambah Slide Baru</h1></div>
                <div class="card-body">
                    {{-- PERBAIKAN: Menggunakan nama rute yang benar --}}
                    <form action="{{ route('admin.slideshows.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul (Opsional)</label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}">
                            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="gambar" class="form-label">File Gambar <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('gambar') is-invalid @enderror" id="gambar" name="gambar" required>
                            <div class="form-text">Rekomendasi ukuran: 1920x1080 piksel. Maksimal 2MB.</div>
                            @error('gambar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="urutan" class="form-label">Urutan Tampil <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', 0) }}" required>
                            <div class="form-text">Angka lebih kecil akan tampil lebih dulu.</div>
                            @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" checked>
                            <label class="form-check-label" for="is_aktif">Aktifkan slide ini</label>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Slide</button>
                            {{-- PERBAIKAN: Menggunakan nama rute yang benar --}}
                            <a href="{{ route('admin.slideshows.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection