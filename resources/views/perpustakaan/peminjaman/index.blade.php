@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Sirkulasi Peminjaman Aktif</h3>
            <span class="text-muted small uppercase">Monitoring Status Pengeluaran Fisik & Pengendalian Jatuh Tempo</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-sm btn-primary rounded-0 px-3 uppercase fw-bold small shadow-sm me-2">
                <i class="bi bi-upc-scan me-1"></i> Scan Pinjaman Baru
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

    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">Daftar Rekapitulasi Sirkulasi Aktif</span>
            <a href="{{ route('perpustakaan.peminjaman.history') }}" class="btn btn-sm btn-outline-light rounded-0 py-0 px-2 uppercase fw-bold" style="font-size: 10px;">Arsip Pengembalian</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th class="text-start" style="width: 30%;">SPESIFIKASI BUKU</th>
                            <th class="text-start" style="width: 25%;">DATA PEMINJAM</th>
                            <th style="width: 25%;">VISUALISASI JATUH TEMPO</th>
                            <th style="width: 15%;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($peminjamans as $key => $peminjaman)
                            @php
                                $start = \Carbon\Carbon::parse($peminjaman->tanggal_pinjam);
                                $end = \Carbon\Carbon::parse($peminjaman->jatuh_tempo);
                                $now = now();
                                $isLate = $now->gt($end);
                                
                                // Kalkulasi persentase bar waktu berjalan
                                $totalDurasi = $start->diffInDays($end) > 0 ? $start->diffInDays($end) : 1;
                                $hariBerjalan = $start->diffInDays($now);
                                $persentase = ($hariBerjalan / $totalDurasi) * 100;
                                $persentase = $persentase > 100 ? 100 : ($persentase < 0 ? 0 : $persentase);
                                
                                $barColor = $isLate ? 'bg-danger' : ($persentase > 80 ? 'bg-warning' : 'bg-primary');
                            @endphp
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">
                                    {{ $peminjamans->firstItem() + $key }}
                                </td>
                                <td class="text-start ps-3">
                                    <div class="uppercase fw-bold text-dark mb-0 line-clamp-1" title="{{ $peminjaman->koleksi->judul ?? '-' }}">
                                        {{ $peminjaman->koleksi->judul ?? 'JUDUL BUKU TIDAK DITEMUKAN' }}
                                    </div>
                                    <span class="text-muted font-monospace bg-light border px-1" style="font-size: 10px;">
                                        <i class="bi bi-upc"></i> {{ $peminjaman->eksemplar->kode_barcode ?? 'NO-BARCODE' }}
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
                                <td class="px-3">
                                    <div class="d-flex justify-content-between font-monospace text-muted mb-1" style="font-size: 10px;">
                                        <span>{{ $start->format('d/m/Y') }}</span>
                                        <span class="{{ $isLate ? 'text-danger fw-bold' : 'text-dark' }}">{{ $end->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="progress rounded-0" style="height: 6px; background-color: #e9ecef;">
                                        <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $persentase }}%;"></div>
                                    </div>
                                    <div class="text-center mt-1">
                                        @if($isLate)
                                            <span class="text-danger uppercase fw-bold font-monospace" style="font-size: 10px;">TERLAMBAT {{ $now->diffInDays($end) }} HARI</span>
                                        @else
                                            <span class="text-muted uppercase font-monospace" style="font-size: 10px;">SISA WAKTU: {{ $now->diffInDays($end) }} HARI</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($isLate)
                                        <span class="badge bg-danger text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            OVERDUE
                                        </span>
                                    @else
                                        <span class="badge bg-primary text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            DIPINJAM
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                    Tidak ada transaksi sirkulasi aktif saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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

@push('styles')
<style>
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endpush