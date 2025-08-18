@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h1>Edit Slide</h1></div>
                <div class="card-body">
                    <form action="{{ route('slideshows.update', $slideshow->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul (Opsional)</label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $slideshow->judul) }}">
                            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="gambar" class="form-label">Ganti Gambar (Opsional)</label>
                            <input type="file" class="form-control @error('gambar') is-invalid @enderror" id="gambar" name="gambar">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah gambar.</div>
                            @error('gambar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <img src="{{ asset('storage/' . $slideshow->gambar) }}" alt="Preview" class="img-thumbnail mt-2" style="max-width: 200px;">
                        </div>

                        <div class="mb-3">
                            <label for="urutan" class="form-label">Urutan Tampil <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $slideshow->urutan) }}" required>
                            <div class="form-text">Angka lebih kecil akan tampil lebih dulu.</div>
                            @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" @if(old('is_aktif', $slideshow->is_aktif)) checked @endif>
                            <label class="form-check-label" for="is_aktif">Aktifkan slide ini</label>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Slide</button>
                            <a href="{{ route('slideshows.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection