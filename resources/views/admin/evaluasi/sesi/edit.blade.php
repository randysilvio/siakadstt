@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Sesi Evaluasi</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('evaluasi-sesi.update', $sesi->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="nama_sesi" class="form-label">Nama Sesi</label>
                    <input type="text" class="form-control @error('nama_sesi') is-invalid @enderror" id="nama_sesi" name="nama_sesi" value="{{ old('nama_sesi', $sesi->nama_sesi) }}" required>
                    @error('nama_sesi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                 <div class="mb-3">
                    <label for="tahun_akademik_id" class="form-label">Tahun Akademik</label>
                    <select class="form-select @error('tahun_akademik_id') is-invalid @enderror" id="tahun_akademik_id" name="tahun_akademik_id" required>
                        <option value="">Pilih Tahun Akademik</option>
                        @foreach ($tahunAkademik as $ta)
                            <option value="{{ $ta->id }}" {{ old('tahun_akademik_id', $sesi->tahun_akademik_id) == $ta->id ? 'selected' : '' }}>{{ $ta->nama_tahun_akademik }}</option>
                        @endforeach
                    </select>
                    @error('tahun_akademik_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $sesi->tanggal_mulai) }}" required>
                         @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $sesi->tanggal_selesai) }}" required>
                         @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $sesi->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Aktifkan Sesi Ini?
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('evaluasi-sesi.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection