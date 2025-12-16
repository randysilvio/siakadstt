@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Sirkulasi Peminjaman</h2>
            <p class="text-muted mb-0">Daftar buku yang sedang dipinjam saat ini.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Peminjaman Baru
            </a>
            <a href="{{ route('perpustakaan.peminjaman.returnForm') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-arrow-return-left me-1"></i> Pengembalian
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Tabel Peminjaman --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 text-center" style="width: 50px;">No</th>
                            <th>Judul Buku</th>
                            <th>Peminjam</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjamans as $key => $peminjaman)
                            @php
                                $isLate = now()->gt($peminjaman->jatuh_tempo);
                            @endphp
                            <tr class="{{ $isLate ? 'bg-danger bg-opacity-10' : '' }}">
                                <td class="ps-4 text-center fw-bold text-secondary">{{ $peminjamans->firstItem() + $key }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $peminjaman->koleksi->judul ?? 'Judul Tidak Ditemukan' }}</div>
                                    <small class="text-muted">Kode: {{ $peminjaman->koleksi->id ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2 text-secondary border">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-dark">{{ $peminjaman->user->name ?? 'User Tidak Ditemukan' }}</span>
                                            <small class="text-muted">{{ $peminjaman->user->email ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->translatedFormat('d M Y') }}</td>
                                <td>
                                    <span class="{{ $isLate ? 'text-danger fw-bold' : 'text-dark' }}">
                                        {{ \Carbon\Carbon::parse($peminjaman->jatuh_tempo)->translatedFormat('d M Y') }}
                                    </span>
                                    @if($isLate)
                                        <div class="small text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> Lewat {{ now()->diffInDays($peminjaman->jatuh_tempo) }} hari</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isLate)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3">Terlambat</span>
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3">Dipinjam</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    Tidak ada transaksi peminjaman aktif saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($peminjamans->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection