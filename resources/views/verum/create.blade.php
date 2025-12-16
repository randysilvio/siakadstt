@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Buat Kelas Baru</h2>
                    <p class="text-muted mb-0">Siapkan ruang pembelajaran daring untuk mahasiswa.</p>
                </div>
                <a href="{{ route('verum.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
                </div>
            @endif

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('verum.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="mata_kuliah_id" class="form-label fw-bold">Mata Kuliah</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-book"></i></span>
                                <select class="form-select @error('mata_kuliah_id') is-invalid @enderror" id="mata_kuliah_id" name="mata_kuliah_id" required>
                                    <option value="" disabled selected>-- Pilih Mata Kuliah --</option>
                                    @foreach($mataKuliahs as $matkul)
                                        <option value="{{ $matkul->id }}" {{ old('mata_kuliah_id') == $matkul->id ? 'selected' : '' }}>
                                            {{ $matkul->nama_mk }} ({{ $matkul->kode_mk }}) - Sem. {{ $matkul->semester }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-text text-muted small">Hanya mata kuliah yang Anda ampu di semester aktif yang muncul.</div>
                            @error('mata_kuliah_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="nama_kelas" class="form-label fw-bold">Nama Kelas</label>
                            <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" placeholder="Contoh: Teologi Sistematika I - Kelas A" required>
                            @error('nama_kelas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="deskripsi" class="form-label fw-bold">Deskripsi (Opsional)</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4" placeholder="Berikan gambaran singkat tentang kelas ini...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('verum.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Simpan Kelas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection