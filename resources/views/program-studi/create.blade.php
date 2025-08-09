@extends('layouts.app')

@section('content')
    <h1>Tambah Program Studi Baru</h1>

    <form action="/program-studi" method="POST" class="mt-4">
        @csrf

        <div class="mb-3">
            <label for="nama_prodi" class="form-label">Nama Program Studi</label>
            <input type="text" class="form-control @error('nama_prodi') is-invalid @enderror" id="nama_prodi" name="nama_prodi" value="{{ old('nama_prodi') }}">

            @error('nama_prodi')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="/program-studi" class="btn btn-secondary">Batal</a>
    </form>
@endsection