@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Pengaturan Umum</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('pengaturan.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header">
                Informasi Pimpinan
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="nama_rektor" class="form-label">Nama Rektor</label>
                    <input type="text" class="form-control" id="nama_rektor" name="nama_rektor"
                           value="{{ old('nama_rektor', $pengaturans['nama_rektor']->value ?? '') }}">
                    <div class="form-text">Nama ini akan tampil di bagian tanda tangan dokumen resmi.</div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Simpan Pengaturan</button>
    </form>
@endsection