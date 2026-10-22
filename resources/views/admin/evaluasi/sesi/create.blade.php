@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Registrasi Sesi Evaluasi</h3>
            <span class="text-muted small uppercase">Pembukaan Periode Baru untuk Kuesioner EDOM Mahasiswa</span>
        </div>
        <div>
            <a href="{{ route('admin.evaluasi-sesi.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Parameter Sesi Evaluasi
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.evaluasi-sesi.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="nama_sesi" class="form-label text-dark small fw-bold uppercase">Identifikasi Sesi (Nama Sesi) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-0 uppercase @error('nama_sesi') is-invalid @enderror" id="nama_sesi" name="nama_sesi" value="{{ old('nama_sesi') }}" placeholder="CONTOH: EVALUASI KINERJA DOSEN SEMESTER GANJIL 2026/2027" required autofocus>
                    @error('nama_sesi')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="tahun_akademik_id" class="form-label text-dark small fw-bold uppercase">Tahun Akademik Rujukan <span class="text-danger">*</span></label>
                    <select class="form-select rounded-0 uppercase @error('tahun_akademik_id') is-invalid @enderror" id="tahun_akademik_id" name="tahun_akademik_id" required>
                        <option value="">-- PILIH TAHUN AKADEMIK TERKAIT --</option>
                        @foreach ($tahunAkademik as $ta)
                            <option value="{{ $ta->id }}" {{ old('tahun_akademik_id') == $ta->id ? 'selected' : '' }}>
                                TAHUN AKADEMIK: {{ $ta->tahun }} (SEMESTER {{ strtoupper($ta->semester) }})
                            </option>
                        @endforeach
                    </select>
                    @error('tahun_akademik_id')
                        <div class="invalid-feedback d-block font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-4 g-4">
                    <div class="col-md-6">
                        <label for="tanggal_mulai" class="form-label text-dark small fw-bold uppercase">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-0 font-monospace @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                        @error('tanggal_mulai')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_selesai" class="form-label text-dark small fw-bold uppercase">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-0 font-monospace @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                        @error('tanggal_selesai')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 bg-light p-3 border rounded-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label text-dark fw-bold uppercase small ms-2" for="is_active">
                            Aktifkan Publikasi Sesi
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <a href="{{ route('admin.evaluasi-sesi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Jadwal Sesi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection