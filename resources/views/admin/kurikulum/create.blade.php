@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Registrasi Kurikulum Akademik</h3>
            <span class="text-muted small uppercase">Penambahan Basis Standar Pembelajaran Baru Institusi</span>
        </div>
        <div>
            <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Parameter Kurikulum
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.kurikulum.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="program_studi_id" class="form-label small fw-bold uppercase text-dark">Program Studi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-building text-dark"></i></span>
                        <select class="form-select rounded-0 uppercase @error('program_studi_id') is-invalid @enderror" id="program_studi_id" name="program_studi_id" required autofocus>
                            <option value="" disabled {{ old('program_studi_id') ? '' : 'selected' }}>-- PILIH PROGRAM STUDI --</option>
                            @foreach($program_studis as $prodi)
                                <option value="{{ $prodi->id }}" {{ old('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                        @error('program_studi_id')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label for="nama_kurikulum" class="form-label small fw-bold uppercase text-dark">Identifikasi / Nama Kurikulum <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light rounded-0"><i class="bi bi-journal-bookmark text-dark"></i></span>
                            <input type="text" class="form-control rounded-0 uppercase @error('nama_kurikulum') is-invalid @enderror" id="nama_kurikulum" name="nama_kurikulum" value="{{ old('nama_kurikulum') }}" placeholder="CONTOH: KURIKULUM KKNI / OBE 2025" required>
                            @error('nama_kurikulum')
                                <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="tahun" class="form-label small fw-bold uppercase text-dark">Tahun Berlaku <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light rounded-0"><i class="bi bi-calendar-check text-dark"></i></span>
                            <input type="number" class="form-control rounded-0 font-monospace @error('tahun') is-invalid @enderror" id="tahun" name="tahun" value="{{ old('tahun', date('Y')) }}" min="2000" required>
                            @error('tahun')
                                <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Kurikulum
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection