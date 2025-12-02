@extends('layouts.app')
@section('content')
    <h1>Tambah Dosen Baru</h1>

    {{-- Form Action ke rute admin --}}
    <form action="{{ route('admin.dosen.store') }}" method="POST" class="mt-4">
        @csrf
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">Data Identitas</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nidn" class="form-label">NIDN / NIDK</label>
                        <input type="text" class="form-control @error('nidn') is-invalid @enderror" id="nidn" name="nidn" value="{{ old('nidn') }}" required>
                        @error('nidn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap (dengan Gelar)</label>
                        <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jabatan_akademik" class="form-label">Jabatan Akademik</label>
                        <input type="text" class="form-control" id="jabatan_akademik" name="jabatan_akademik" value="{{ old('jabatan_akademik') }}" placeholder="Contoh: Lektor Kepala">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="bidang_keahlian" class="form-label">Bidang Keahlian</label>
                        <input type="text" class="form-control" id="bidang_keahlian" name="bidang_keahlian" value="{{ old('bidang_keahlian') }}" placeholder="Contoh: Teologi Sistematika">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email_institusi" class="form-label">Email Institusi (Opsional)</label>
                    <input type="email" class="form-control @error('email_institusi') is-invalid @enderror" id="email_institusi" name="email_institusi" value="{{ old('email_institusi') }}">
                    @error('email_institusi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">Akun Login</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Login</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection