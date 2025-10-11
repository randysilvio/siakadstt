@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Daftar Peminjaman Aktif</h1>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-primary">Catat Peminjaman Baru</a>
            <a href="{{ route('perpustakaan.peminjaman.returnForm') }}" class="btn btn-success">Proses Pengembalian</a>
        </div>
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
                            <th>Jatuh Tempo</th>
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
                                <td>{{ \Carbon\Carbon::parse($peminjaman->jatuh_tempo)->isoFormat('D MMMM Y') }}</td>
                                <td>
                                    @if(now()->gt($peminjaman->jatuh_tempo))
                                        <span class="badge bg-danger">Terlambat</span>
                                    @else
                                        <span class="badge bg-success">{{ $peminjaman->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada buku yang sedang dipinjam.</td>
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