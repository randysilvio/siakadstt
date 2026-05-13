@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Sirkulasi Peminjaman Aktif</h3>
            <span class="text-muted small uppercase">Monitoring Status Pengeluaran Fisik & Pengendalian Jatuh Tempo</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-sm btn-primary rounded-0 px-3 uppercase fw-bold small shadow-sm me-2">
                <i class="bi bi-plus-lg me-1"></i> Catat Pinjaman Baru
            </a>
            <a href="{{ route('perpustakaan.peminjaman.returnForm') }}" class="btn btn-sm btn-success rounded-0 px-3 uppercase fw-bold small text-white shadow-sm">
                <i class="bi bi-arrow-return-left me-1"></i> Proses Pengembalian
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TABEL DATA PEMINJAMAN ENTERPRISE --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Sirkulasi Aktif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-dark text-white small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th class="text-start" style="width: 32%;">SPESIFIKASI BUKU</th>
                            <th class="text-start" style="width: 25%;">DATA PEMINJAM</th>
                            <th style="width: 12%;">TGL PINJAM</th>
                            <th style="width: 13%;">JATUH TEMPO</th>
                            <th style="width: 12%;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($peminjamans as $key => $peminjaman)
                            @php
                                $isLate = now()->gt($peminjaman->jatuh_tempo);
                            @endphp
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">
                                    {{ $peminjamans->firstItem() + $key }}
                                </td>
                                <td class="text-start ps-3">
                                    <div class="uppercase fw-bold text-dark mb-0">
                                        {{ $peminjaman->koleksi->judul ?? 'JUDUL BUKU TIDAK DITEMUKAN' }}
                                    </div>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">
                                        KODE BUKU: {{ str_pad($peminjaman->koleksi->id ?? 0, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="text-start ps-3">
                                    <div class="uppercase fw-bold text-dark mb-0">
                                        {{ $peminjaman->user->name ?? 'USER TIDAK DITEMUKAN' }}
                                    </div>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">
                                        {{ $peminjaman->user->email ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    {{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->translatedFormat('d M Y') }}
                                </td>
                                <td class="text-center font-monospace">
                                    <span class="d-block {{ $isLate ? 'text-danger fw-bold' : 'text-dark' }}">
                                        {{ \Carbon\Carbon::parse($peminjaman->jatuh_tempo)->translatedFormat('d M Y') }}
                                    </span>
                                    @if($isLate)
                                        <span class="text-danger uppercase fw-bold" style="font-size: 10px;">
                                            LEWAT {{ now()->diffInDays($peminjaman->jatuh_tempo) }} HARI
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isLate)
                                        <span class="badge bg-danger text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            TERLAMBAT
                                        </span>
                                    @else
                                        <span class="badge bg-primary text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            AKTIF (DIPINJAM)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada transaksi peminjaman pustaka aktif saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginasi Flat --}}
        @if($peminjamans->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $peminjamans->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection