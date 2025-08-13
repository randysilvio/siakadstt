@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard Admin</h1>

    {{-- Kartu Statistik --}}
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Mahasiswa</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalMahasiswa }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Dosen</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalDosen }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Prodi</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalProdi }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Total Mata Kuliah</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalMatkul }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            {{-- Di sini bisa ditambahkan grafik atau data lainnya --}}
            <div class="card">
                <div class="card-header">
                    Grafik Mahasiswa per Program Studi (Contoh)
                </div>
                <div class="card-body">
                    <p>Grafik akan ditampilkan di sini.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            {{-- Panel Pengumuman --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Pengumuman Terbaru
                    <a href="{{ route('pengumuman.create') }}" class="btn btn-sm btn-outline-success">+ Buat Baru</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($pengumumans as $pengumuman)
                        <a href="{{ route('pengumuman.show', $pengumuman) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $pengumuman->judul }}</h6>
                                <small>{{ $pengumuman->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small text-muted">{{ Str::limit($pengumuman->konten, 80) }}</p>
                        </a>
                    @empty
                        <div class="list-group-item">Tidak ada pengumuman.</div>
                    @endforelse
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('pengumuman.index') }}">Kelola semua pengumuman</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
