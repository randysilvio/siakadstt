@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h3 class="mb-2 text-dark fw-bold">Konfigurasi Aturan Absensi</h3>
            <p class="text-muted small mb-4">Pengaturan parameter waktu operasional kerja institusi.</p>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-dark fw-bold">Parameter Sistem Kehadiran</h5>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-1 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.absensi.pengaturan.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4 g-4">
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-semibold">Waktu Batas Masuk Kerja</label>
                                <input type="time" name="jam_masuk" class="form-control rounded-1" value="{{ $pengaturan->jam_masuk }}">
                                <div class="form-text mt-1">Acuan waktu dimulainya jam operasional.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-semibold">Waktu Mulai Absen Pulang</label>
                                <input type="time" name="jam_pulang" class="form-control rounded-1" value="{{ $pengaturan->jam_pulang }}">
                                <div class="form-text mt-1">Batas minimum pegawai diperkenankan melakukan absen keluar.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-dark fw-semibold">Durasi Toleransi Keterlambatan</label>
                            <div class="input-group">
                                <input type="number" name="toleransi_terlambat_menit" class="form-control rounded-start-1" value="{{ $pengaturan->toleransi_terlambat_menit }}">
                                <span class="input-group-text rounded-end-1 bg-light">Menit</span>
                            </div>
                        </div>

                        <div class="alert bg-light border text-dark rounded-1 mb-4">
                            <h6 class="fw-bold mb-1">Catatan Sistem:</h6>
                            <p class="mb-0 small text-muted">
                                Sistem secara otomatis akan mencatat status kehadiran menjadi <strong>ALPHA</strong> jika pegawai tidak melakukan check-in setelah melewati kalkulasi waktu (Waktu Masuk + Toleransi Keterlambatan).
                            </p>
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                Simpan Konfigurasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection