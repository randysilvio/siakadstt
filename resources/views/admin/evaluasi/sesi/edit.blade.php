@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Edit Sesi Evaluasi</h2>
                    <p class="text-muted mb-0">Perbarui informasi periode evaluasi.</p>
                </div>
                <a href="{{ route('admin.evaluasi-sesi.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.evaluasi-sesi.update', $sesi->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="nama_sesi" class="form-label fw-bold">Nama Sesi</label>
                            <input type="text" class="form-control @error('nama_sesi') is-invalid @enderror" id="nama_sesi" name="nama_sesi" value="{{ old('nama_sesi', $sesi->nama_sesi) }}" required>
                            @error('nama_sesi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="tahun_akademik_id" class="form-label fw-bold">Tahun Akademik</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-mortarboard"></i></span>
                                <select class="form-select @error('tahun_akademik_id') is-invalid @enderror" id="tahun_akademik_id" name="tahun_akademik_id" required>
                                    <option value="">-- Pilih Tahun Akademik --</option>
                                    @foreach ($tahunAkademik as $ta)
                                        <option value="{{ $ta->id }}" {{ old('tahun_akademik_id', $sesi->tahun_akademik_id) == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->tahun }} (Semester {{ $ta->semester }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('tahun_akademik_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="tanggal_mulai" class="form-label fw-bold">Tanggal Mulai</label>
                                <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $sesi->tanggal_mulai) }}" required>
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_selesai" class="form-label fw-bold">Tanggal Selesai</label>
                                <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $sesi->tanggal_selesai) }}" required>
                                @error('tanggal_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $sesi->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    Status Aktif
                                </label>
                            </div>
                            <div class="form-text">Pastikan hanya ada satu sesi aktif dalam satu waktu untuk menghindari konflik.</div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('admin.evaluasi-sesi.index') }}" class="btn btn-light border">Batal</a>
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