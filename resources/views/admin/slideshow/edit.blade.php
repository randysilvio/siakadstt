@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0">Edit Slide</h1>
                </div>
                <div class="card-body">
                    {{-- PERBAIKAN 1: Route disesuaikan dengan Controller (pakai admin.) --}}
                    <form action="{{ route('admin.slideshows.update', $slideshow->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Input Judul --}}
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul (Opsional)</label>
                            {{-- Mengambil value lama --}}
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $slideshow->judul) }}">
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Input Gambar --}}
                        <div class="mb-3">
                            <label for="gambar" class="form-label">Ganti Gambar (Opsional)</label>
                            <input type="file" class="form-control @error('gambar') is-invalid @enderror" id="gambar" name="gambar" accept="image/*">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah gambar.</div>
                            
                            @error('gambar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            {{-- Preview Gambar Saat Ini --}}
                            @if($slideshow->gambar)
                                <div class="mt-3 p-2 border rounded bg-light">
                                    <p class="mb-1 fw-bold small text-muted">Gambar Saat Ini:</p>
                                    <img src="{{ asset('storage/' . $slideshow->gambar) }}" alt="Preview Slide" class="img-thumbnail" style="max-height: 150px; width: auto;">
                                </div>
                            @endif
                        </div>

                        {{-- Input Urutan --}}
                        <div class="mb-3">
                            <label for="urutan" class="form-label">Urutan Tampil <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $slideshow->urutan) }}" required>
                            <div class="form-text">Angka lebih kecil akan tampil lebih dulu.</div>
                            @error('urutan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Switch Aktif/Non-Aktif --}}
                        <div class="mb-3 form-check form-switch">
                            {{-- Checkbox logic: Cek apakah 'is_aktif' bernilai true/1 --}}
                            <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" {{ $slideshow->is_aktif ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_aktif">Aktifkan slide ini</label>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Update Slide
                            </button>
                            
                            {{-- PERBAIKAN 2: Route Batal ke admin.slideshows.index --}}
                            <a href="{{ route('admin.slideshows.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection