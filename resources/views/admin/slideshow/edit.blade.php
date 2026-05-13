@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Modifikasi Slideshow</h3>
                    <span class="text-muted small">Pembaruan parameter data visual portal utama</span>
                </div>
                <a href="{{ route('admin.slideshows.index') }}" class="btn btn-outline-dark btn-sm rounded-1">Batal</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.slideshows.update', $slideshow->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="judul" class="form-label text-dark fw-semibold">Judul Publikasi (Opsional)</label>
                            <input type="text" class="form-control rounded-1 @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $slideshow->judul) }}" autofocus>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="gambar" class="form-label text-dark fw-semibold">Pembaruan Material Visual (Opsional)</label>
                            <input type="file" class="form-control rounded-1 mb-3 @error('gambar') is-invalid @enderror" id="gambar" name="gambar" accept="image/*">
                            @error('gambar')
                                <div class="invalid-feedback mb-3">{{ $message }}</div>
                            @enderror
                            
                            @if($slideshow->gambar)
                                <div class="bg-light border rounded-1 p-3">
                                    <span class="d-block text-muted small fw-bold mb-2">PREVIEW MEDIA SAAT INI:</span>
                                    <img src="{{ asset('storage/' . $slideshow->gambar) }}" alt="Preview Terkini" class="img-fluid border" style="max-height: 200px; object-fit: contain;">
                                </div>
                            @endif
                        </div>

                        <div class="mb-4 w-50">
                            <label for="urutan" class="form-label text-dark fw-semibold">Hierarki Tampil (Nomor Urut) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control rounded-1 font-monospace @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $slideshow->urutan) }}" required>
                            @error('urutan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 bg-light p-3 border rounded-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" {{ $slideshow->is_aktif ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-bold ms-2" for="is_aktif">
                                    Status Tampil (Aktif)
                                </label>
                            </div>
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-1">Simpan Pembaruan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection