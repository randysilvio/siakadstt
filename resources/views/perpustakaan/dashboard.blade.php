@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Bagian Header (mengikuti struktur asli Anda) --}}
    <h2 class="mb-4">Dasbor Perpustakaan</h2>
    <p class="lead">Selamat datang kembali, {{ Auth::user()->name }}</p>
    <hr class="mb-4">

    {{-- =============================================== --}}
    {{-- BAGIAN BARU: Tombol Aksi Cepat --}}
    {{-- =============================================== --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-primary btn-lg w-100 py-3 shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>Catat Peminjaman Baru
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('perpustakaan.peminjaman.returnForm') }}" class="btn btn-success btn-lg w-100 py-3 shadow-sm">
                <i class="fas fa-undo-alt me-2"></i>Proses Pengembalian
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-info btn-lg w-100 py-3 shadow-sm">
                <i class="fas fa-book me-2"></i>Kelola Koleksi Buku
            </a>
        </div>
    </div>

    {{-- =============================================== --}}
    {{-- BAGIAN BARU: Kartu Statistik Lengkap --}}
    {{-- =============================================== --}}
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card text-white bg-primary shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Judul Buku</h5>
                    <p class="card-text fs-2 fw-bold">{{ $totalJudul ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card text-white bg-secondary shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Eksemplar</h5>
                    <p class="card-text fs-2 fw-bold">{{ $totalEksemplar ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card text-white bg-success shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">Peminjaman Aktif</h5>
                    <p class="card-text fs-2 fw-bold">{{ $peminjamanAktif ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card text-white bg-danger shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">Buku Terlambat</h5>
                    <p class="card-text fs-2 fw-bold">{{ $terlambat ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- =============================================== --}}
    {{-- BAGIAN BARU: Aktivitas & Laporan --}}
    {{-- =============================================== --}}
    <div class="row">
        {{-- Panel Kiri: Aktivitas --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Aktivitas Sirkulasi Terakhir</div>
                <div class="list-group list-group-flush">
                    @forelse ($aktivitasTerakhir as $aktivitas)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $aktivitas->user->name ?? 'N/A' }}</h6>
                                <small>{{ \Carbon\Carbon::parse($aktivitas->created_at)->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">
                                @if($aktivitas->status == 'Dipinjam')
                                    Meminjam buku: <strong>{{ $aktivitas->koleksi->judul ?? 'N/A' }}</strong>
                                @else
                                    Mengembalikan buku: <strong>{{ $aktivitas->koleksi->judul ?? 'N/A' }}</strong>
                                @endif
                            </p>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted">Belum ada aktivitas.</div>
                    @endforelse
                </div>
            </div>
        </div>
        {{-- Panel Kanan: Laporan & Riwayat --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Laporan & Riwayat</div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('perpustakaan.peminjaman.index') }}" class="list-group-item list-group-item-action">Lihat Semua Peminjaman Aktif</a>
                    <a href="{{ route('perpustakaan.peminjaman.history') }}" class="list-group-item list-group-item-action">Lihat Riwayat Peminjaman</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endpush
@endsection