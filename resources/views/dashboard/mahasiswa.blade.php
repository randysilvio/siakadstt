@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard Mahasiswa</h1>

    {{-- Notifikasi Tagihan --}}
    @if($memiliki_tagihan)
    <div class="alert alert-danger" role="alert">
        <strong>Perhatian!</strong> Anda memiliki tagihan pembayaran yang belum lunas. Segera selesaikan pembayaran untuk dapat mengisi KRS.
        <a href="{{ route('pembayaran.riwayat') }}" class="alert-link">Lihat Riwayat Pembayaran</a>.
    </div>
    @endif

    <div class="row">
        {{-- Profil & Akademik --}}
        <div class="col-md-7 mb-4">
            <div class="card">
                <div class="card-header">Informasi Akademik</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            {{-- Placeholder untuk foto profil --}}
                            <svg class="img-fluid rounded-circle mb-3" width="120" height="120" xmlns="http://www.w3.org/2000/svg" role="img" preserveAspectRatio="xMidYMid slice" focusable="false"><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em">Foto</text></svg>
                        </div>
                        <div class="col-md-8">
                            <h4>{{ $mahasiswa->nama_lengkap }}</h4>
                            <p class="text-muted mb-1">{{ $mahasiswa->nim }}</p>
                            <p class="mb-2"><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
                            <p><strong>Dosen Wali:</strong> {{ $mahasiswa->dosenWali->nama_lengkap ?? 'Belum Ditentukan' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-muted">IPK Kumulatif</h5>
                            <p class="fs-3 fw-bold">{{ number_format($ipk, 2) }}</p>
                        </div>
                        <div class="col-6">
                            <h5 class="text-muted">Total SKS Ditempuh</h5>
                            <p class="fs-3 fw-bold">{{ $total_sks }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel Pengumuman --}}
        <div class="col-md-5 mb-4">
            <div class="card">
                <div class="card-header">Pengumuman</div>
                <div class="list-group list-group-flush">
                    @forelse($pengumumans as $pengumuman)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $pengumuman->judul }}</h6>
                                <small>{{ $pengumuman->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small">{{ Str::limit($pengumuman->konten, 100) }}</p>
                        </div>
                    @empty
                        <div class="list-group-item">Tidak ada pengumuman.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
