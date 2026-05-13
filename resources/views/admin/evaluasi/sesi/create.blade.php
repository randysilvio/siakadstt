@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Registrasi Sesi Evaluasi</h3>
                    <span class="text-muted small">Pembukaan periode baru untuk kuesioner mahasiswa</span>
                </div>
                <a href="{{ route('admin.evaluasi-sesi.index') }}" class="btn btn-outline-dark btn-sm rounded-1">Batal</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.evaluasi-sesi.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="nama_sesi" class="form-label text-dark fw-semibold">Identifikasi Sesi (Nama Sesi) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-1 @error('nama_sesi') is-invalid @enderror" id="nama_sesi" name="nama_sesi" value="{{ old('nama_sesi') }}" placeholder="Contoh: Evaluasi Kinerja Dosen Semester Ganjil 2026/2027" required autofocus>
                            @error('nama_sesi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="tahun_akademik_id" class="form-label text-dark fw-semibold">Tahun Akademik Rujukan <span class="text-danger">*</span></label>
                            <select class="form-select rounded-1 @error('tahun_akademik_id') is-invalid @enderror" id="tahun_akademik_id" name="tahun_akademik_id" required>
                                <option value="">-- Pilih Tahun Akademik Terkait --</option>
                                @foreach ($tahunAkademik as $ta)
                                    <option value="{{ $ta->id }}" {{ old('tahun_akademik_id') == $ta->id ? 'selected' : '' }}>
                                        Tahun Akademik: {{ $ta->tahun }} (Semester {{ $ta->semester }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text mt-2 text-muted small">
                                <i class="bi bi-info-circle me-1"></i> Data evaluasi yang masuk akan direkapitulasi secara otomatis ke dalam mata kuliah pada tahun akademik rujukan ini.
                            </div>
                            @error('tahun_akademik_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4 g-4">
                            <div class="col-md-6">
                                <label for="tanggal_mulai" class="form-label text-dark fw-semibold">Tanggal Mulai (Pembukaan) <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-1 @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_selesai" class="form-label text-dark fw-semibold">Tanggal Selesai (Penutupan) <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-1 @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                                @error('tanggal_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 bg-light p-3 border rounded-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-bold ms-2" for="is_active">
                                    Aktifkan Publikasi Sesi
                                </label>
                            </div>
                            <div class="form-text mt-2">Apabila dicentang, portal pengisian kuesioner akan secara otomatis terbuka bagi mahasiswa pada rentang tanggal yang telah ditentukan di atas.</div>
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5">Simpan Jadwal Sesi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection