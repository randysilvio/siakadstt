@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Edit Tahun Akademik</h1>

    <form action="{{ route('tahun-akademik.update', $tahunAkademik->id) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tahun" class="form-label">Tahun Akademik</label>
                <input type="text" class="form-control @error('tahun') is-invalid @enderror" id="tahun" name="tahun" value="{{ old('tahun', $tahunAkademik->tahun) }}" placeholder="Contoh: 2024/2025">
                @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="semester" class="form-label">Semester</label>
                <select class="form-select @error('semester') is-invalid @enderror" id="semester" name="semester">
                    <option value="Gasal" {{ old('semester', $tahunAkademik->semester) == 'Gasal' ? 'selected' : '' }}>Gasal</option>
                    <option value="Genap" {{ old('semester', $tahunAkademik->semester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                </select>
                @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tanggal_mulai_krs" class="form-label">Tanggal Mulai KRS</label>
                <input type="date" class="form-control @error('tanggal_mulai_krs') is-invalid @enderror" id="tanggal_mulai_krs" name="tanggal_mulai_krs" value="{{ old('tanggal_mulai_krs', $tahunAkademik->tanggal_mulai_krs) }}">
                @error('tanggal_mulai_krs')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="tanggal_selesai_krs" class="form-label">Tanggal Selesai KRS</label>
                <input type="date" class="form-control @error('tanggal_selesai_krs') is-invalid @enderror" id="tanggal_selesai_krs" name="tanggal_selesai_krs" value="{{ old('tanggal_selesai_krs', $tahunAkademik->tanggal_selesai_krs) }}">
                @error('tanggal_selesai_krs')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('tahun-akademik.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection