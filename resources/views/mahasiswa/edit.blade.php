@extends('layouts.app')

@section('content')
    <h1>Edit Mahasiswa</h1>

    <form action="/mahasiswa/{{ $mahasiswa->id }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')

        <h5>Data Akademik</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nim" class="form-label">NIM</label>
                <input type="text" class="form-control @error('nim') is-invalid @enderror" id="nim" name="nim" value="{{ old('nim', $mahasiswa->nim) }}">
                @error('nim')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $mahasiswa->nama_lengkap) }}">
                @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="program_studi_id" class="form-label">Program Studi</label>
            <select class="form-select @error('program_studi_id') is-invalid @enderror" id="program_studi_id" name="program_studi_id">
                @foreach ($program_studis as $prodi)
                    <option value="{{ $prodi->id }}" {{ old('program_studi_id', $mahasiswa->program_studi_id) == $prodi->id ? 'selected' : '' }}>
                        {{ $prodi->nama_prodi }}
                    </option>
                @endforeach
            </select>
            @error('program_studi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="dosen_wali_id" class="form-label">Dosen Wali</label>
            <select class="form-select @error('dosen_wali_id') is-invalid @enderror" id="dosen_wali_id" name="dosen_wali_id">
                <option value="">-- Pilih Dosen Wali --</option>
                @foreach ($dosens as $dosen)
                    <option value="{{ $dosen->id }}" {{ old('dosen_wali_id', $mahasiswa->dosen_wali_id) == $dosen->id ? 'selected' : '' }}>
                        {{ $dosen->nama_lengkap }}
                    </option>
                @endforeach
            </select>
            @error('dosen_wali_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <hr>
        <h5>Data Akun Login</h5>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $mahasiswa->user->email ?? '') }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="/mahasiswa" class="btn btn-secondary">Batal</a>
    </form>
@endsection