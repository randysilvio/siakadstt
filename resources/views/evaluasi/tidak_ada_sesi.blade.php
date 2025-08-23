@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Evaluasi Dosen</h1>
    </div>

    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">Periode Evaluasi Belum Tersedia</h4>
        <p>Saat ini belum ada periode evaluasi dosen yang aktif. Silakan periksa kembali nanti atau hubungi bagian administrasi akademik untuk informasi lebih lanjut.</p>
        <hr>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
</div>
@endsection