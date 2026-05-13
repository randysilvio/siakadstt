@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Arsip Riwayat Sirkulasi</h3>
            <span class="text-muted small uppercase">Rekapitulasi Histori Peminjaman Pustaka yang Telah Diselesaikan</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Sirkulasi Aktif
            </a>
        </div>
    </div>

    {{-- TABEL RIWAYAT ENTERPRISE --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5 border-top border-dark border-4">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Arsip Peminjaman Selesai
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th class="text-start" style="width: 34%;">SPESIFIKASI BUKU</th>
                            <th class="text-start" style="width: 24%;">DATA PEMINJAM</th>
                            <th style="width: 12%;">TGL PINJAM</th>
                            <th style="width: 12%;">TGL KEMBALI</th>
                            <th style="width: 12%;">STATUS AKHIR</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($peminjamans as $key => $peminjaman)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">
                                    {{ $peminjamans->firstItem() + $key }}
                                </td>
                                <td class="text-start ps-3">
                                    <div class="uppercase fw-bold text-dark mb-0">
                                        {{ $peminjaman->koleksi->judul ?? 'MASTER BUKU DIHAPUS' }}
                                    </div>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">
                                        KODE BUKU: {{ str_pad($peminjaman->koleksi->id ?? 0, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="text-start ps-3 uppercase">
                                    <strong class="text-dark d-block mb-0">
                                        {{ $peminjaman->user->name ?? 'USER DIHAPUS' }}
                                    </strong>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">
                                        {{ $peminjaman->user->email ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    {{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->translatedFormat('d M Y') }}
                                </td>
                                <td class="text-center font-monospace text-success fw-bold">
                                    @if($peminjaman->tanggal_kembali)
                                        {{ \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->translatedFormat('d M Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                        DIKEMBALIKAN
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-clock-history fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada catatan arsip riwayat peminjaman yang diselesaikan.
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