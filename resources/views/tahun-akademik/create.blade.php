@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Tambah Tahun Akademik Baru</h3>
            <span class="text-muted small uppercase">Entri Master Data Periode Perkuliahan & Batas Pengisian KRS</span>
        </div>
        <div>
            <a href="{{ route('admin.tahun-akademik.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Parameter Tahun Akademik
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.tahun-akademik.store') }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="tahun" class="form-label small fw-bold uppercase text-dark">Tahun Akademik <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 font-monospace uppercase @error('tahun') is-invalid @enderror" id="tahun" name="tahun" value="{{ old('tahun', $nextTahun ?? '') }}" placeholder="CONTOH: 2025/2026" required>
                        @error('tahun')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="semester" class="form-label small fw-bold uppercase text-dark">Semester <span class="text-danger">*</span></label>
                        <select class="form-select rounded-0 uppercase @error('semester') is-invalid @enderror" id="semester" name="semester" required>
                            <option value="Ganjil" {{ old('semester', $nextSemester ?? '') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ old('semester', $nextSemester ?? '') == 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                        @error('semester')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-4">Jadwal Pengisian KRS</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="tanggal_mulai_krs" class="form-label small fw-bold uppercase text-dark">Tanggal Mulai KRS</label>
                        <input type="date" class="form-control rounded-0 font-monospace @error('tanggal_mulai_krs') is-invalid @enderror" id="tanggal_mulai_krs" name="tanggal_mulai_krs" value="{{ old('tanggal_mulai_krs') }}">
                        @error('tanggal_mulai_krs')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_selesai_krs" class="form-label small fw-bold uppercase text-dark">Tanggal Selesai KRS</label>
                        <input type="date" class="form-control rounded-0 font-monospace @error('tanggal_selesai_krs') is-invalid @enderror" id="tanggal_selesai_krs" name="tanggal_selesai_krs" value="{{ old('tanggal_selesai_krs') }}">
                        @error('tanggal_selesai_krs')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.tahun-akademik.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection