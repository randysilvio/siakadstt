@extends('layouts.app')
@section('content')
    <h1>Buat Pengumuman Baru</h1>
    <form action="{{ route('pengumuman.store') }}" method="POST" class="mt-4">
        @csrf
        <div class="mb-3">
            <label for="judul" class="form-label">Judul</label>
            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}">
            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="konten" class="form-label">Isi Pengumuman</label>
            <textarea class="form-control @error('konten') is-invalid @enderror" id="konten" name="konten" rows="5">{{ old('konten') }}</textarea>
            @error('konten')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="target_role" class="form-label">Tujukan Kepada</label>
            <select class="form-select @error('target_role') is-invalid @enderror" id="target_role" name="target_role">
                <option value="semua" {{ old('target_role') == 'semua' ? 'selected' : '' }}>Semua Pengguna</option>
                <option value="admin" {{ old('target_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="dosen" {{ old('target_role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                <option value="mahasiswa" {{ old('target_role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
            </select>
            @error('target_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection