@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard Dosen</h1>
    <p class="lead">Selamat datang kembali, {{ $dosen->nama_lengkap }}</p>
    <hr>

    <div class="row">
        {{-- Panel Utama (kiri) --}}
        <div class="col-lg-7">
            @if($prodiYangDikepalai)
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Portal Kepala Program Studi: {{ $prodiYangDikepalai->nama_prodi }}</h5>
                </div>
                <div class="card-body">
                    <p>Anda memiliki akses sebagai Kaprodi untuk melakukan validasi KRS dan manajemen lainnya.</p>
                    <a href="{{ route('kaprodi.dashboard') }}" class="btn btn-primary">Masuk ke Portal Kaprodi</a>
                </div>
            </div>
            @endif

            <div class="card mb-4">
                <div class="card-header">Mata Kuliah yang Anda Ajar</div>
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

        {{-- Panel Samping (kanan) --}}
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header">Perwalian</div>
                <div class="card-body text-center">
                    <h5 class="card-title">Mahasiswa Wali</h5>
                    <p class="fs-1 fw-bold">{{ $jumlahMahasiswaWali }}</p>
                    <a href="{{ route('perwalian.index') }}" class="btn btn-outline-primary">Lihat Detail</a>
                </div>
            </div>
            
            {{-- Panel Pengumuman --}}
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
