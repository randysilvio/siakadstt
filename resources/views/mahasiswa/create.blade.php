@extends('layouts.app')

@section('content')
    <h1>Tambah Mahasiswa Baru</h1>

    {{-- Form Action ke rute admin.mahasiswa.store --}}
    <form action="{{ route('admin.mahasiswa.store') }}" method="POST" class="mt-4">
        @csrf

        <div class="card">
            <div class="card-header fw-bold">Data Akademik</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nim" class="form-label">NIM</label>
                        <input type="text" class="form-control @error('nim') is-invalid @enderror" id="nim" name="nim" value="{{ old('nim') }}" required>
                        @error('nim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tahun_masuk" class="form-label">Tahun Masuk</label>
                        <input type="number" class="form-control @error('tahun_masuk') is-invalid @enderror" id="tahun_masuk" name="tahun_masuk" value="{{ old('tahun_masuk', date('Y')) }}" required>
                        @error('tahun_masuk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="program_studi_id" class="form-label">Program Studi</label>
                        <select class="form-select @error('program_studi_id') is-invalid @enderror" id="program_studi_id" name="program_studi_id" required>
                            <option value="">-- Pilih Prodi --</option>
                            @foreach ($program_studis as $prodi)
                                <option value="{{ $prodi->id }}" {{ old('program_studi_id') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                            @endforeach
                        </select>
                        @error('program_studi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="dosen_wali_id" class="form-label">Dosen Wali</label>
                        <select class="form-select @error('dosen_wali_id') is-invalid @enderror" id="dosen_wali_id" name="dosen_wali_id">
                            <option value="">-- Pilih Dosen Wali --</option>
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosen_wali_id') == $dosen->id ? 'selected' : '' }}>{{ $dosen->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header fw-bold">Data Pribadi</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                            <option value="">Pilih...</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2">{{ old('alamat') }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="nama_ibu_kandung" class="form-label">Nama Ibu Kandung</label>
                    <input type="text" class="form-control" id="nama_ibu_kandung" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung') }}">
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header fw-bold">Data Akun Login</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
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

        <div class="mt-4 mb-5">
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            {{-- Tombol Batal mengarah ke index admin --}}
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection