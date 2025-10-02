@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Kurikulum</h1>
    {{-- PERBAIKAN: Menggunakan nama rute yang benar --}}
    <form action="{{ route('admin.kurikulum.update', $kurikulum->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_kurikulum" class="form-label">Nama Kurikulum</label>
            <input type="text" class="form-control @error('nama_kurikulum') is-invalid @enderror" id="nama_kurikulum" name="nama_kurikulum" value="{{ old('nama_kurikulum', $kurikulum->nama_kurikulum) }}" required>
            @error('nama_kurikulum')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="tahun" class="form-label">Tahun</label>
            <input type="number" class="form-control @error('tahun') is-invalid @enderror" id="tahun" name="tahun" value="{{ old('tahun', $kurikulum->tahun) }}" required>
            @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Perbarui</button>
        {{-- PERBAIKAN: Menggunakan nama rute yang benar --}}
        <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection