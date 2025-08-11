@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard Dosen</h1>
    <p class="lead">Selamat datang kembali, {{ $dosen->nama_lengkap }}</p>
    <hr>

    <div class="row">
        {{-- Panel Utama --}}
        <div class="col-md-8">
            {{-- Panel Kaprodi --}}
            @if($prodiYangDikepalai)
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Portal Kepala Program Studi: {{ $prodiYangDikepalai->nama_prodi }}</h5>
                </div>
                <div class="card-body">
                    <p>Anda memiliki akses sebagai Kaprodi.</p>
                    <a href="{{ route('kaprodi.dashboard') }}" class="btn btn-primary">Masuk ke Portal Kaprodi</a>
                </div>
            </div>
            @endif

            {{-- Daftar Mata Kuliah --}}
            <div class="card">
                <div class="card-header">
                    Mata Kuliah yang Anda Ajar
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($mata_kuliahs as $mk)
                        <a href="{{ route('nilai.show', $mk->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $mk->kode_mk }} - {{ $mk->nama_mk }}</h6>
                                <small>{{ $mk->sks }} SKS - Semester {{ $mk->semester }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $mk->mahasiswas_count }} Mahasiswa</span>
                        </a>
                    @empty
                        <div class="list-group-item">Anda belum ditugaskan untuk mengajar mata kuliah apapun.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Panel Samping --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Perwalian</div>
                <div class="card-body text-center">
                    <h5 class="card-title">Mahasiswa Wali</h5>
                    <p class="fs-1 fw-bold">{{ $jumlahMahasiswaWali }}</p>
                    <a href="{{ route('perwalian.index') }}" class="btn btn-outline-primary">Lihat Detail</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
