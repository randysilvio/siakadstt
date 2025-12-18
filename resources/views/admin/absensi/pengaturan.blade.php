@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-gear-fill me-2"></i> Pengaturan Aturan Absensi
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('admin.absensi.pengaturan.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jam Masuk Kerja</label>
                                <input type="time" name="jam_masuk" class="form-control" value="{{ $pengaturan->jam_masuk }}">
                                <div class="form-text">Waktu mulai absensi dihitung.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jam Pulang Kerja</label>
                                <input type="time" name="jam_pulang" class="form-control" value="{{ $pengaturan->jam_pulang }}">
                                <div class="form-text">Waktu minimal untuk bisa absen pulang.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Toleransi Terlambat (Menit)</label>
                            <div class="input-group">
                                <input type="number" name="toleransi_terlambat_menit" class="form-control" value="{{ $pengaturan->toleransi_terlambat_menit }}">
                                <span class="input-group-text">Menit</span>
                            </div>
                            <div class="alert alert-warning mt-2 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>Logika Sistem:</strong><br>
                                Jika pegawai absen lewat dari (Jam Masuk + Toleransi), maka sistem akan otomatis mencatatnya sebagai <strong>ALPHA / TIDAK DIHITUNG HADIR</strong>.
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection