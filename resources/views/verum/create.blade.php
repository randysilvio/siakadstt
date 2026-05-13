@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Buat Kelas Baru Verum</h3>
            <span class="text-muted small uppercase">Siapkan Ruang Pembelajaran Daring & Repositori Kelas Virtual</span>
        </div>
        <div>
            <a href="{{ route('verum.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    {{-- Notifikasi Error --}}
    @if(session('error'))
        <div class="alert alert-danger border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
            <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Form Card Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5 bg-white">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Parameter Ruang Kelas Virtual
        </div>
        <div class="card-body p-4">
            <form action="{{ route('verum.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="mata_kuliah_id" class="form-label small fw-bold uppercase text-dark">Mata Kuliah <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-book text-dark"></i></span>
                        <select class="form-select rounded-0 uppercase @error('mata_kuliah_id') is-invalid @enderror" id="mata_kuliah_id" name="mata_kuliah_id" required>
                            <option value="" disabled selected>-- PILIH MATA KULIAH --</option>
                            @foreach($mataKuliahs as $matkul)
                                <option value="{{ $matkul->id }}" {{ old('mata_kuliah_id') == $matkul->id ? 'selected' : '' }}>
                                    {{ $matkul->nama_mk }} ({{ $matkul->kode_mk }}) - SEM. {{ $matkul->semester }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text text-muted small uppercase mt-1">Hanya mata kuliah yang Anda ampu di semester aktif yang muncul pada pilihan.</div>
                    @error('mata_kuliah_id')
                        <div class="invalid-feedback font-monospace small d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="nama_kelas" class="form-label small fw-bold uppercase text-dark">Nama Kelas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-0 uppercase @error('nama_kelas') is-invalid @enderror" id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" placeholder="CONTOH: TEOLOGI SISTEMATIKA I - KELAS A" required>
                    @error('nama_kelas')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="deskripsi" class="form-label small fw-bold uppercase text-dark">Deskripsi Uraian Kelas (Opsional)</label>
                    <textarea class="form-control rounded-0 uppercase @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4" placeholder="BERIKAN GAMBARAN SINGKAT TENTANG KELAS INI...">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('verum.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection