@extends('layouts.app')

@section('content')
    <h1>Edit Program Studi</h1>

    <form action="{{ route('program-studi.update', $programStudi->id) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_prodi" class="form-label">Nama Program Studi</label>
            <input type="text" class="form-control @error('nama_prodi') is-invalid @enderror" id="nama_prodi" name="nama_prodi" value="{{ old('nama_prodi', $programStudi->nama_prodi) }}">
            @error('nama_prodi')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="kaprodi_dosen_id" class="form-label">Ketua Program Studi (Kaprodi)</label>
            <select class="form-select @error('kaprodi_dosen_id') is-invalid @enderror" id="kaprodi_dosen_id" name="kaprodi_dosen_id">
                <option value="">-- Pilih Kaprodi --</option>
                @foreach ($dosens as $dosen)
                    <option value="{{ $dosen->id }}" {{ old('kaprodi_dosen_id', $programStudi->kaprodi_dosen_id) == $dosen->id ? 'selected' : '' }}>
                        {{ $dosen->nama_lengkap }}
                    </option>
                @endforeach
            </select>
            @error('kaprodi_dosen_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('program-studi.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection