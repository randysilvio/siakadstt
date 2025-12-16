@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Edit Program Studi</h2>
                    <p class="text-muted mb-0">Perbarui informasi program studi.</p>
                </div>
                <a href="{{ route('admin.program-studi.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.program-studi.update', $programStudi->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="nama_prodi" class="form-label fw-bold">Nama Program Studi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                <input type="text" class="form-control @error('nama_prodi') is-invalid @enderror" id="nama_prodi" name="nama_prodi" value="{{ old('nama_prodi', $programStudi->nama_prodi) }}" required>
                                @error('nama_prodi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="kaprodi_dosen_id" class="form-label fw-bold">Ketua Program Studi (Kaprodi)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                <select class="form-select @error('kaprodi_dosen_id') is-invalid @enderror" id="kaprodi_dosen_id" name="kaprodi_dosen_id">
                                    <option value="">-- Pilih Dosen Kaprodi --</option>
                                    @foreach ($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('kaprodi_dosen_id', $programStudi->kaprodi_dosen_id) == $dosen->id ? 'selected' : '' }}>
                                            {{ $dosen->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kaprodi_dosen_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text text-muted small mt-1">
                                <i class="bi bi-info-circle me-1"></i> Dosen yang dipilih akan memiliki akses ke dashboard Kaprodi.
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('admin.program-studi.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection