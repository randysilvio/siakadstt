@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Kegiatan Akademik</h2>
    <hr>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('kalender.update', $kalender->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="judul_kegiatan" class="form-label">Judul Kegiatan</label>
                    <input type="text" class="form-control @error('judul_kegiatan') is-invalid @enderror" id="judul_kegiatan" name="judul_kegiatan" value="{{ old('judul_kegiatan', $kalender->judul_kegiatan) }}" required>
                    @error('judul_kegiatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $kalender->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $kalender->tanggal_mulai) }}" required>
                         @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $kalender->tanggal_selesai) }}" required>
                         @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="target_role" class="form-label">Target Kegiatan</label>
                    <select class="form-select @error('target_role') is-invalid @enderror" id="target_role" name="target_role" required>
                        <option value="semua" {{ old('target_role', $kalender->target_role) == 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="mahasiswa" {{ old('target_role', $kalender->target_role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="dosen" {{ old('target_role', $kalender->target_role) == 'dosen' ? 'selected' : '' }}>Dosen</option>
                    </select>
                    @error('target_role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <a href="{{ route('kalender.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Update Kegiatan</button>
            </form>
        </div>
    </div>
</div>
@endsection