@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Kegiatan Akademik</h2>
    <hr>

    <div class="card">
        <div class="card-body">
            {{-- PERBAIKAN: Menggunakan nama rute yang benar --}}
            <form action="{{ route('admin.kalender.update', $kalender->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="judul_kegiatan" class="form-label">Judul Kegiatan</label>
                    <input type="text" class="form-control @error('judul_kegiatan') is-invalid @enderror" id="judul_kegiatan" name="judul_kegiatan" value="{{ old('judul_kegiatan', $kalender->judul_kegiatan) }}" required>
                    @error('judul_kegiatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $kalender->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        {{-- PERBAIKAN: Format tanggal untuk input type date --}}
                        <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $kalender->tanggal_mulai->format('Y-m-d')) }}" required>
                         @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        {{-- PERBAIKAN: Format tanggal untuk input type date --}}
                        <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $kalender->tanggal_selesai->format('Y-m-d')) }}" required>
                         @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- PERBAIKAN: Mengganti select menjadi checkbox untuk multi-peran --}}
                <div class="mb-3">
                    <label class="form-label">Target Kegiatan</label>
                    @php
                        $selectedRoles = old('target_roles', $kalender->roles->pluck('id')->toArray());
                    @endphp
                    <div class="@error('target_roles') is-invalid @enderror">
                        @foreach($roles as $role)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="target_roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                {{ (is_array($selectedRoles) && in_array($role->id, $selectedRoles)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_{{ $role->id }}">{{ ucfirst($role->name) }}</label>
                        </div>
                        @endforeach
                    </div>
                    @error('target_roles')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <a href="{{ route('admin.kalender.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Update Kegiatan</button>
            </form>
        </div>
    </div>
</div>
@endsection
