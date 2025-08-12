@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Buat Kelas Verum Baru</h2>
    <hr>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('verum.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="mata_kuliah_id" class="form-label">Pilih Mata Kuliah</label>
                    <select class="form-select @error('mata_kuliah_id') is-invalid @enderror" id="mata_kuliah_id" name="mata_kuliah_id" required>
                        <option value="" disabled selected>-- Pilih Mata Kuliah yang Diampu --</option>
                        @foreach($mataKuliahs as $matkul)
                            <option value="{{ $matkul->id }}" {{ old('mata_kuliah_id') == $matkul->id ? 'selected' : '' }}>
                                {{ $matkul->nama_mk }} (Semester {{ $matkul->semester }})
                            </option>
                        @endforeach
                    </select>
                    @error('mata_kuliah_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nama_kelas" class="form-label">Nama Kelas</label>
                    <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" placeholder="Contoh: Teologi Sistematika I - Kelas Pagi" required>
                    @error('nama_kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi Singkat (Opsional)</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <a href="{{ route('verum.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Kelas</button>
            </form>
        </div>
    </div>
</div>
@endsection