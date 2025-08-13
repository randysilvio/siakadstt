@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard Keuangan</h1>
        <a href="{{ route('pembayaran.index') }}" class="btn btn-primary">Lihat & Kelola Semua Tagihan</a>
    </div>

    <div class="row">
        {{-- Panel Utama (kiri) --}}
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Ringkasan Keuangan</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card text-white bg-secondary">
                                <div class="card-header">Total Tagihan</div>
                                <div class="card-body"><h5 class="card-title display-4">{{ $totalTagihan ?? 0 }}</h5></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card text-white bg-success">
                                <div class="card-header">Tagihan Lunas</div>
                                <div class="card-body"><h5 class="card-title display-4">{{ $totalLunas ?? 0 }}</h5></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-danger">
                                <div class="card-header">Belum Lunas</div>
                                <div class="card-body"><h5 class="card-title display-4">{{ $totalBelumLunas ?? 0 }}</h5></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel Samping (kanan) --}}
        <div class="col-lg-4 mb-4">
            <div class="card">
                {{-- PERBAIKAN: Tombol "+ Buat Baru" dihapus --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    Pengumuman Terbaru
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
                    <a href="{{ route('pengumuman.index') }}">Lihat semua pengumuman</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
