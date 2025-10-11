@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Riwayat Peminjaman</h1>
        <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-secondary">Kembali ke Peminjaman Aktif</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Judul Buku</th>
                            <th>Peminjam</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjamans as $key => $peminjaman)
                            <tr>
                                <td>{{ $peminjamans->firstItem() + $key }}</td>
                                <td>{{ $peminjaman->koleksi->judul ?? 'Buku tidak ditemukan' }}</td>
                                <td>{{ $peminjaman->user->name ?? 'Pengguna tidak ditemukan' }}</td>
                                <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->isoFormat('D MMMM Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->isoFormat('D MMMM Y') }}</td>
                                <td><span class="badge bg-secondary">{{ $peminjaman->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada riwayat peminjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $peminjamans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection