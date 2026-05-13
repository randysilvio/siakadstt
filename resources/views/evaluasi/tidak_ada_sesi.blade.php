@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row justify-content-center text-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-0 p-5 border-top border-warning border-4">
                <div class="mb-4 text-warning">
                    <i class="bi bi-calendar-x fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark uppercase mb-3">Sesi Evaluasi Tidak Aktif</h4>
                <p class="text-muted mb-4 lead">Saat ini portal kuesioner evaluasi dosen (EDOM) sedang ditutup. Harap periksa kembali jadwal kalender akademik atau hubungi Biro Administrasi Akademik (BAAK) untuk informasi lebih lanjut.</p>
                <hr class="my-4">
                <a href="{{ route('dashboard') }}" class="btn btn-dark rounded-0 px-4 uppercase small fw-bold">Kembali ke Dashboard Utama</a>
            </div>
        </div>
    </div>
</div>
@endsection