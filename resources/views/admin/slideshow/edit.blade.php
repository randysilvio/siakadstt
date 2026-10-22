@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Edit Slideshow</h3>
            <span class="text-muted small uppercase">Pembaruan Parameter Data Visual Portal Utama</span>
        </div>
        <div>
            <a href="{{ route('admin.slideshows.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Pembaruan Spanduk Visual
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.slideshows.update', $slideshow->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="judul" class="form-label text-dark small fw-bold uppercase">Judul Publikasi (Opsional)</label>
                    <input type="text" class="form-control rounded-0 uppercase @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $slideshow->judul) }}" autofocus>
                    @error('judul')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="gambar" class="form-label text-dark small fw-bold uppercase">Pembaruan Material Visual (Opsional)</label>
                    <input type="file" class="form-control rounded-0 mb-3 @error('gambar') is-invalid @enderror" id="gambar" name="gambar" accept="image/*">
                    @error('gambar')
                        <div class="invalid-feedback font-monospace small mb-3">{{ $message }}</div>
                    @enderror
                    
                    @if($slideshow->gambar)
                        <div class="bg-light border rounded-0 p-3">
                            <span class="d-block text-muted small font-monospace fw-bold uppercase mb-2">PREVIEW MEDIA SAAT INI:</span>
                            <img src="{{ asset('storage/' . $slideshow->gambar) }}" alt="Preview Terkini" class="img-fluid border rounded-0" style="max-height: 200px; object-fit: contain;">
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="urutan" class="form-label text-dark small fw-bold uppercase">Nomor Urut Tampil <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-0 font-monospace @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $slideshow->urutan) }}" min="0" required>
                        @error('urutan')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="bg-light p-3 border rounded-0 w-100">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" {{ $slideshow->is_aktif ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-bold uppercase small ms-2" for="is_aktif">
                                    Status Tampil (Aktif)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.slideshows.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Pembaruan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection