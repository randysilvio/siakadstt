@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Tambah Program Studi</h2>
                    <p class="text-muted mb-0">Input data program studi baru.</p>
                </div>
                <a href="{{ route('admin.program-studi.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.program-studi.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="nama_prodi" class="form-label fw-bold">Nama Program Studi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                <input type="text" class="form-control @error('nama_prodi') is-invalid @enderror" id="nama_prodi" name="nama_prodi" value="{{ old('nama_prodi') }}" placeholder="Contoh: Teknik Informatika" required>
                                @error('nama_prodi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('admin.program-studi.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection