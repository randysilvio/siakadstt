@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dasbor Perpustakaan</h2>
    <p class="lead">Selamat datang kembali, {{ Auth::user()->name }}</p>
    <hr>

    <div class="row">
        {{-- Panel Utama (kiri) --}}
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Statistik Koleksi</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="card text-white bg-primary">
                                <div class="card-header">Total Judul Buku</div>
                                <div class="card-body"><h5 class="card-title display-4">{{ $totalJudul ?? 0 }}</h5></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card text-white bg-success">
                                <div class="card-header">Total Eksemplar</div>
                                <div class="card-body"><h5 class="card-title display-4">{{ $totalEksemplar ?? 0 }}</h5></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                     <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-lg btn-info w-100">Kelola Koleksi Buku</a>
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
