@extends('layouts.app')
@section('content')
    <h1>Tambah Dosen Baru</h1>

    {{-- [PERBAIKAN FINAL] Menggunakan rute admin --}}
    <form action="{{ route('admin.dosen.store') }}" method="POST" class="mt-4">
        @csrf
        <div class="mb-3">
            <label for="nidn" class="form-label">NIDN</label>
            <input type="text" class="form-control @error('nidn') is-invalid @enderror" id="nidn" name="nidn" value="{{ old('nidn') }}">
            @error('nidn')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}">
            @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <hr>
        <h5>Data Akun Login</h5>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password Awal</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        {{-- [PERBAIKAN FINAL] Menggunakan rute admin --}}
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection