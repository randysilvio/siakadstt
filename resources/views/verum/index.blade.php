@extends('layouts.app')

@push('styles')
<style>
    .class-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .class-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .card-title a {
        text-decoration: none;
        color: inherit;
    }
    .card-title a:hover {
        color: #0d6efd; /* Bootstrap primary color */
    }
</style>
@endpush

@section('content')
<div class="container">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Kelas Verum (E-Learning)</h2>
            <p class="text-muted mb-0">Kelola pembelajaran daring Anda di sini.</p>
        </div>
        
        @if(Auth::user()->hasRole('dosen'))
            <a href="{{ route('verum.create') }}" class="btn btn-primary shadow-sm px-4">
                <i class="bi bi-plus-lg me-2"></i>Buat Kelas Baru
            </a>
        @endif
    </div>

    {{-- Pesan Notifikasi --}}
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Konten Utama --}}
    @if($semuaKelas->isEmpty() && !session('error'))
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-3 opacity-50" alt="Empty State">
            <h5 class="text-muted fw-bold">Belum Ada Kelas</h5>
            <p class="text-muted">Anda belum terdaftar di kelas Verum manapun pada tahun akademik ini.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($semuaKelas as $kelas)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm class-card overflow-hidden">
                        {{-- Hiasan Header Kartu --}}
                        <div class="card-header border-0 py-3 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25">
                                    {{ optional($kelas->mataKuliah)->kode_mk ?? 'KODE' }}
                                </span>
                                @if($kelas->is_meeting_active)
                                    <span class="badge bg-danger animate-pulse"><i class="bi bi-record-circle me-1"></i> LIVE</span>
                                @endif
                            </div>
                            <h5 class="card-title fw-bold mt-2 mb-0 text-truncate">
                                <a href="{{ route('verum.show', $kelas) }}" class="text-white stretched-link">
                                    {{ $kelas->nama_kelas }}
                                </a>
                            </h5>
                        </div>

                        <div class="card-body">
                            <h6 class="text-primary fw-bold mb-2">{{ optional($kelas->mataKuliah)->nama_mk }}</h6>
                            <p class="card-text text-muted small mb-0 line-clamp-2">
                                {{ $kelas->deskripsi ? Str::limit($kelas->deskripsi, 80) : 'Tidak ada deskripsi kelas.' }}
                            </p>
                        </div>
                        
                        <div class="card-footer bg-white border-top py-3 d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-2 text-secondary">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="small text-truncate">
                                @if(Auth::user()->hasRole('mahasiswa'))
                                    <span class="text-muted d-block" style="font-size: 0.7rem;">PENGAJAR</span>
                                    <span class="fw-bold text-dark">{{ optional(optional($kelas->dosen)->user)->name ?? 'Dosen Pengampu' }}</span>
                                @else
                                    <span class="text-muted d-block" style="font-size: 0.7rem;">KODE KELAS</span>
                                    <span class="fw-bold text-dark">{{ $kelas->kode_kelas }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection