@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Pembaruan Kurikulum Akademik</h3>
                    <span class="text-muted small">Modifikasi informasi pada basis standar pembelajaran institusi</span>
                </div>
                <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-outline-dark btn-sm rounded-1">Batal</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.kurikulum.update', $kurikulum->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="nama_kurikulum" class="form-label text-dark fw-semibold">Identifikasi Kurikulum <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-1 @error('nama_kurikulum') is-invalid @enderror" id="nama_kurikulum" name="nama_kurikulum" value="{{ old('nama_kurikulum', $kurikulum->nama_kurikulum) }}" required autofocus>
                            @error('nama_kurikulum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="tahun" class="form-label text-dark fw-semibold">Tahun Berlaku / Penetapan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control rounded-1 font-monospace @error('tahun') is-invalid @enderror" id="tahun" name="tahun" value="{{ old('tahun', $kurikulum->tahun) }}" required>
                            @error('tahun')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-1">Simpan Pembaruan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection