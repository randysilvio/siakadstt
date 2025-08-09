@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Dashboard Mahasiswa</h1>

    @if($memiliki_tagihan)
        <div class="alert alert-danger">
            <strong>Peringatan!</strong> Anda memiliki tagihan yang belum lunas. Silakan selesaikan pembayaran untuk dapat mengakses halaman KRS.
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    Selamat Datang, {{ $mahasiswa->nama_lengkap ?? 'Mahasiswa' }}!
                </div>
                <div class="card-body">
                    @if($mahasiswa)
                        <p><strong>Nama:</strong> {{ $mahasiswa->nama_lengkap }}</p>
                        <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
                        <p><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
                    @else
                        <p>Data mahasiswa Anda tidak ditemukan.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total SKS Ditempuh</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $total_sks }} SKS</h5>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Indeks Prestasi Kumulatif (IPK)</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $ipk }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">Pengumuman Terbaru</div>
        <div class="card-body">
            @forelse($pengumumans as $pengumuman)
                <h5>{{ $pengumuman->judul }}</h5>
                <p>{!! nl2br(e($pengumuman->konten)) !!}</p>
                <small class="text-muted">Diposting pada {{ $pengumuman->created_at->format('d M Y') }}</small>
                @if(!$loop->last) <hr> @endif
            @empty
                <p>Tidak ada pengumuman saat ini.</p>
            @endforelse
        </div>
    </div>
@endsection