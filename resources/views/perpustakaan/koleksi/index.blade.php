@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Manajemen Koleksi Buku</h2>
            <p class="text-muted mb-0">Kelola katalog buku perpustakaan.</p>
        </div>
        <a href="{{ route('perpustakaan.koleksi.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Buku Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Tabel Buku --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 text-center" style="width: 50px;">No</th>
                            <th style="width: 80px;">Sampul</th>
                            <th>Judul & Pengarang</th>
                            <th>Penerbit</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Tersedia</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($koleksis as $koleksi)
                        <tr>
                            <td class="ps-4 text-center fw-bold text-secondary">{{ $loop->iteration + $koleksis->firstItem() - 1 }}</td>
                            <td>
                                @if($koleksi->gambar_sampul)
                                    <img src="{{ Storage::url($koleksi->gambar_sampul) }}" class="rounded shadow-sm" width="50" height="70" style="object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted border" style="width: 50px; height: 70px;">
                                        <i class="bi bi-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $koleksi->judul }}</div>
                                <small class="text-muted"><i class="bi bi-person me-1"></i> {{ $koleksi->pengarang }}</small>
                            </td>
                            <td>
                                <span class="d-block text-dark">{{ $koleksi->penerbit }}</span>
                                <small class="text-muted">{{ $koleksi->tahun_terbit }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $koleksi->jumlah_stok }}</span>
                            </td>
                            <td class="text-center">
                                @if($koleksi->jumlah_tersedia > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">{{ $koleksi->jumlah_tersedia }}</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Habis</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('perpustakaan.koleksi.show', $koleksi) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('perpustakaan.koleksi.edit', $koleksi) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('perpustakaan.koleksi.destroy', $koleksi) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                                Tidak ada koleksi buku yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($koleksis->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $koleksis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection