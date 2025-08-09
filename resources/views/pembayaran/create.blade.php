@extends('layouts.app')
@section('content')
    <h1>Buat Tagihan Baru</h1>
    <form action="{{ route('pembayaran.store') }}" method="POST" class="mt-4">
        @csrf
        <div class="mb-3">
            <label for="mahasiswa_id" class="form-label">Mahasiswa</label>
            <select class="form-select @error('mahasiswa_id') is-invalid @enderror" id="mahasiswa_id" name="mahasiswa_id">
                <option selected disabled>Pilih Mahasiswa</option>
                @foreach ($mahasiswas as $mahasiswa)
                    <option value="{{ $mahasiswa->id }}" {{ old('mahasiswa_id') == $mahasiswa->id ? 'selected' : '' }}>{{ $mahasiswa->nim }} - {{ $mahasiswa->nama_lengkap }}</option>
                @endforeach
            </select>
            @error('mahasiswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah Tagihan (tanpa titik atau koma)</label>
            <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" name="jumlah" value="{{ old('jumlah') }}">
            @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="semester" class="form-label">Untuk Semester</label>
            <input type="text" class="form-control @error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ old('semester') }}" placeholder="Contoh: Gasal 2024/2025">
            @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection