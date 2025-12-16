@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Riwayat Peminjaman</h2>
            <p class="text-muted mb-0">Arsip seluruh transaksi peminjaman yang telah selesai.</p>
        </div>
        <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Aktif
        </a>
    </div>

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
                            <th>Tanggal Kembali</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjamans as $key => $peminjaman)
                            <tr>
                                <td class="ps-4 text-center fw-bold text-secondary">{{ $peminjamans->firstItem() + $key }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $peminjaman->koleksi->judul ?? 'Buku Dihapus' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2 text-secondary">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <span class="text-dark">{{ $peminjaman->user->name ?? 'User Dihapus' }}</span>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->format('d M Y') }}</td>
                                <td>
                                    @if($peminjaman->tanggal_kembali)
                                        <span class="text-success fw-bold">{{ \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->format('d M Y') }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3">Dikembalikan</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-50"></i>
                                    Belum ada riwayat peminjaman.
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