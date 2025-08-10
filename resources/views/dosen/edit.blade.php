@extends('layouts.app')
@section('content')
    <h1>Edit Dosen</h1>
    <form action="{{ route('dosen.update', $dosen->id) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nidn" class="form-label">NIDN</label>
            <input type="text" class="form-control @error('nidn') is-invalid @enderror" id="nidn" name="nidn" value="{{ old('nidn', $dosen->nidn) }}">
            @error('nidn')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $dosen->nama_lengkap) }}">
            @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3 form-check">
            <input type="hidden" name="is_keuangan" value="0">
            <input class="form-check-input" type="checkbox" value="1" id="is_keuangan" name="is_keuangan" {{ old('is_keuangan', $dosen->is_keuangan) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_keuangan">
                Jadikan sebagai Bagian Keuangan
            </label>
        </div>
        <hr>
        <h5>Data Akun Login</h5>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $dosen->user->email ?? '') }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('dosen.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection