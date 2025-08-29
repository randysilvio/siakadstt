@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Kegiatan Akademik Baru</h2>
    <hr>

    <div class="card">
        <div class="card-body">
            {{-- PERBAIKAN: Menggunakan nama rute yang benar --}}
            <form action="{{ route('admin.kalender.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="judul_kegiatan" class="form-label">Judul Kegiatan</label>
                    <input type="text" class="form-control @error('judul_kegiatan') is-invalid @enderror" id="judul_kegiatan" name="judul_kegiatan" value="{{ old('judul_kegiatan') }}" required>
                    @error('judul_kegiatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                         @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                         @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- PERBAIKAN: Mengganti select menjadi checkbox untuk multi-peran --}}
                <div class="mb-3">
                    <label class="form-label">Target Kegiatan</label>
                    <div class="@error('target_roles') is-invalid @enderror">
                        @foreach($roles as $role)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="target_roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                {{ (is_array(old('target_roles')) && in_array($role->id, old('target_roles'))) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_{{ $role->id }}">{{ ucfirst($role->name) }}</label>
                        </div>
                        @endforeach
                    </div>
                    @error('target_roles')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <a href="{{ route('admin.kalender.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Kegiatan</button>
            </form>
        </div>
    </div>
</div>
@endsection
