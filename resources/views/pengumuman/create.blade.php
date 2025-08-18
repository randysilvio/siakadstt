@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1>Buat Pengumuman Baru</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('pengumuman.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul</label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}" required>
                            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- PENINGKATAN: Mengganti textarea dengan Trix Editor --}}
                        <div class="mb-3">
                            <label for="konten" class="form-label">Isi Pengumuman</label>
                            <input id="konten" type="hidden" name="konten" value="{{ old('konten') }}">
                            <trix-editor input="konten" class="form-control @error('konten') is-invalid @enderror" style="min-height: 200px;"></trix-editor>
                            @error('konten')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="target_role" class="form-label">Tujukan Kepada</label>
                            <select class="form-select @error('target_role') is-invalid @enderror" id="target_role" name="target_role">
                                <option value="semua" {{ old('target_role') == 'semua' ? 'selected' : '' }}>Semua Pengguna</option>
                                <option value="admin" {{ old('target_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="dosen" {{ old('target_role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                                <option value="mahasiswa" {{ old('target_role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                {{-- PERBAIKAN: Menambahkan opsi Tendik yang hilang --}}
                                <option value="tendik" {{ old('target_role') == 'tendik' ? 'selected' : '' }}>Tendik</option>
                            </select>
                            @error('target_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Pengumuman</button>
                        <a href="{{ route('pengumuman.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Tambahkan section ini untuk memuat aset Trix Editor --}}
@push('styles')
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
@endpush

@push('scripts')
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
@endpush