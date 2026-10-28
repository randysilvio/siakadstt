@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Konfigurasi Aturan Absensi</h3>
            <span class="text-muted small uppercase">Pengaturan Parameter Waktu Operasional Kerja Institusi</span>
        </div>
        <div>
            <a href="{{ route('admin.absensi.laporan.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Lihat Laporan Absensi
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-white border-bottom py-3 rounded-0">
            <h6 class="mb-0 text-dark fw-bold uppercase small">Parameter Sistem Kehadiran Pegawai</h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.absensi.pengaturan.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4 g-4">
                    <div class="col-md-6">
                        <label class="form-label text-dark small fw-bold uppercase">Waktu Masuk Kerja <span class="text-danger">*</span></label>
                        <input type="time" name="jam_masuk" class="form-control rounded-0 font-monospace fw-bold" value="{{ $pengaturan->jam_masuk }}" required>
                        <div class="form-text text-muted small uppercase mt-1">Acuan waktu dimulainya jam operasional.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-dark small fw-bold uppercase">Waktu Pulang Kerja <span class="text-danger">*</span></label>
                        <input type="time" name="jam_pulang" class="form-control rounded-0 font-monospace fw-bold" value="{{ $pengaturan->jam_pulang }}" required>
                        <div class="form-text text-muted small uppercase mt-1">Batas waktu absen keluar pegawai.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-dark small fw-bold uppercase">Durasi Toleransi Keterlambatan <x-info-aturan kunci="absensi" /> <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="toleransi_terlambat_menit" class="form-control rounded-0 font-monospace fw-bold" value="{{ $pengaturan->toleransi_terlambat_menit }}" min="0" required>
                        <span class="input-group-text rounded-0 bg-light uppercase fw-bold small font-monospace">Menit</span>
                    </div>
                </div>

                <div class="alert bg-light border border-dark border-opacity-25 text-dark rounded-0 mb-4 p-3">
                    <h6 class="fw-bold mb-1 small uppercase font-monospace"><i class="bi bi-info-circle-fill me-1"></i> Catatan Sistem:</h6>
                    <p class="mb-0 small text-muted uppercase">
                        Pegawai yang melakukan check-in melewati batas <strong>(Waktu Masuk + Toleransi Keterlambatan)</strong> tetap tercatat hadir namun secara otomatis ditandai sebagai <strong>TERLAMBAT</strong> pada rekapitulasi bulanan.
                    </p>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 py-2 uppercase fw-bold small shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Konfigurasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection