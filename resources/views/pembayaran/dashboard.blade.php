@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Dasbor Keuangan</h2>
            <p class="lead text-muted">Selamat datang kembali, {{ Auth::user()->name }}</p>
        </div>
        <a href="{{ route('pembayaran.index') }}" class="btn btn-primary">Lihat & Kelola Semua Tagihan</a>
    </div>

    {{-- STATISTIK UTAMA --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-secondary">
                <div class="card-header">Total Tagihan</div>
                <div class="card-body"><h5 class="card-title display-4">{{ $totalTagihan ?? 0 }}</h5></div>
            </div>
        </div>
        <div class="col-md-4">
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

    {{-- DAFTAR & PENGUMUMAN --}}
    <div class="row">
        <div class="col-lg-8">
            {{-- PEMBAYARAN TERBARU --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">5 Pembayaran Terbaru (Lunas)</div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead><tr><th>Mahasiswa</th><th>Jumlah</th><th>Tgl Bayar</th></tr></thead>
                        <tbody>
                            @forelse($pembayaranTerbaru as $p)
                            <tr>
                                <td>{{ $p->mahasiswa->nama_lengkap ?? 'N/A' }} <small class="text-muted d-block">{{$p->mahasiswa->nim ?? ''}}</small></td>
                                <td>Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                                <td>{{ $p->tanggal_bayar->isoFormat('D MMM YYYY') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">Belum ada pembayaran lunas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAGIHAN TERLAMA --}}
            <div class="card">
                <div class="card-header fw-bold">5 Tagihan Terlama (Belum Lunas)</div>
                 <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead><tr><th>Mahasiswa</th><th>Jumlah</th><th>Semester</th></tr></thead>
                        <tbody>
                             @forelse($tagihanTerlama as $t)
                            <tr>
                                <td>{{ $t->mahasiswa->nama_lengkap ?? 'N/A' }} <small class="text-muted d-block">{{$t->mahasiswa->nim ?? ''}}</small></td>
                                <td>Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                                <td>{{ $t->semester }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">Tidak ada tagihan yang belum lunas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PENGUMUMAN --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-bold">Pengumuman Terbaru</div>
                <div class="list-group list-group-flush">
                    @forelse($pengumumans as $pengumuman)
                        <a href="{{ route('pengumuman.public.show', $pengumuman) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $pengumuman->judul }}</h6>
                                <small>{{ $pengumuman->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small text-muted">{{ Str::limit(strip_tags($pengumuman->konten), 80) }}</p>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Tidak ada pengumuman.</div>
                    @endforelse
                </div>
                 <div class="card-footer text-center">
                    <a href="{{ route('berita.index') }}">Lihat semua pengumuman</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection