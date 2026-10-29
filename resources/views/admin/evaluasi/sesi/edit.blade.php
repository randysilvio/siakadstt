@extends('layouts.app')

@section('content')
@php $kendaliEdom = \App\Services\KalenderPengendali::pengendaliEdom($sesi); @endphp
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Edit Sesi Evaluasi</h3>
            <span class="text-muted small uppercase">Modifikasi Rentang Waktu & Status Periode Evaluasi</span>
        </div>
        <div>
            <a href="{{ route('admin.evaluasi-sesi.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Pembaruan Sesi Evaluasi
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.evaluasi-sesi.update', $sesi->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="nama_sesi" class="form-label text-dark small fw-bold uppercase">Identifikasi Sesi (Nama Sesi) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-0 uppercase @error('nama_sesi') is-invalid @enderror" id="nama_sesi" name="nama_sesi" value="{{ old('nama_sesi', $sesi->nama_sesi) }}" required autofocus>
                    @error('nama_sesi')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="tahun_akademik_id" class="form-label text-dark small fw-bold uppercase">Tahun Akademik Rujukan <span class="text-danger">*</span></label>
                    <select class="form-select rounded-0 uppercase @error('tahun_akademik_id') is-invalid @enderror" id="tahun_akademik_id" name="tahun_akademik_id" required>
                        <option value="">-- PILIH TAHUN AKADEMIK TERKAIT --</option>
                        @foreach ($tahunAkademik as $ta)
                            <option value="{{ $ta->id }}" {{ old('tahun_akademik_id', $sesi->tahun_akademik_id) == $ta->id ? 'selected' : '' }}>
                                TAHUN AKADEMIK: {{ $ta->tahun }} (SEMESTER {{ strtoupper($ta->semester) }})
                            </option>
                        @endforeach
                    </select>
                    @error('tahun_akademik_id')
                        <div class="invalid-feedback d-block font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-4 g-4">
                    @if($kendaliEdom)
                    <div class="col-12"><div class="alert alert-secondary border-0 border-start border-dark border-4 rounded-0 small mb-0 py-2"><i class="bi bi-calendar-check me-1"></i> Tanggal diatur dari <a href="{{ route('admin.kalender.edit', $kendaliEdom) }}" class="fw-bold text-dark">Kalender Akademik: {{ $kendaliEdom->judul_kegiatan }}</a>. Ubah tanggal di sana. <x-info-aturan kunci="kalender_pengendali" /></div></div>
                    @endif
                    <div class="col-md-6">
                        <label for="tanggal_mulai" class="form-label text-dark small fw-bold uppercase">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-0 font-monospace @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" @if($kendaliEdom) readonly title="Diatur dari Kalender Akademik" @endif value="{{ old('tanggal_mulai', \Carbon\Carbon::parse($sesi->tanggal_mulai)->format('Y-m-d')) }}" required>
                        @error('tanggal_mulai')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_selesai" class="form-label text-dark small fw-bold uppercase">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-0 font-monospace @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" @if($kendaliEdom) readonly title="Diatur dari Kalender Akademik" @endif value="{{ old('tanggal_selesai', \Carbon\Carbon::parse($sesi->tanggal_selesai)->format('Y-m-d')) }}" required>
                        @error('tanggal_selesai')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 bg-light p-3 border rounded-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $sesi->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label text-dark fw-bold uppercase small ms-2" for="is_active">
                            Status Publikasi (Aktif)
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <a href="{{ route('admin.evaluasi-sesi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Pembaruan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection