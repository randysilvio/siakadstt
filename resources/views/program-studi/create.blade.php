@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Tambah Program Studi Baru</h3>
            <span class="text-muted small uppercase">Entri Master Data Nomenklatur & Struktur Akademik Prodi</span>
        </div>
        <div>
            <a href="{{ route('admin.program-studi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Spesifikasi Program Studi
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.program-studi.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="nama_prodi" class="form-label small fw-bold uppercase text-dark">Nama Program Studi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-building text-dark"></i></span>
                        <input type="text" class="form-control rounded-0 uppercase @error('nama_prodi') is-invalid @enderror" id="nama_prodi" name="nama_prodi" value="{{ old('nama_prodi') }}" placeholder="CONTOH: TEKNIK INFORMATIKA" required>
                        @error('nama_prodi')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.program-studi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection