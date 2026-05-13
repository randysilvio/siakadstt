@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Koleksi Buku</h3>
            <span class="text-muted small uppercase">Kelola Katalog Inventaris & Sirkulasi Perpustakaan Terpusat</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.koleksi.create') }}" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Buku Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Tabel Buku Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Katalog Buku
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-dark text-white small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th style="width: 8%;">SAMPUL</th>
                            <th class="text-start" style="width: 33%;">JUDUL & PENGARANG</th>
                            <th class="text-start" style="width: 20%;">PENERBIT</th>
                            <th style="width: 10%;">STOK TOTAL</th>
                            <th style="width: 10%;">TERSEDIA</th>
                            <th style="width: 13%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($koleksis as $koleksi)
                        <tr>
                            <td class="text-center font-monospace fw-bold text-muted">
                                {{ $loop->iteration + $koleksis->firstItem() - 1 }}
                            </td>
                            <td class="text-center">
                                @if($koleksi->gambar_sampul)
                                    <img src="{{ Storage::url($koleksi->gambar_sampul) }}" class="rounded-0 border d-block mx-auto" width="45" height="60" style="object-fit: cover;" alt="Sampul">
                                @else
                                    <div class="bg-light rounded-0 border d-flex align-items-center justify-content-center text-muted mx-auto" style="width: 45px; height: 60px;">
                                        <i class="bi bi-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="text-start ps-3">
                                <div class="uppercase fw-bold text-dark">{{ $koleksi->judul }}</div>
                                <span class="text-muted uppercase" style="font-size: 11px;">
                                    <i class="bi bi-person me-1 text-dark"></i> {{ $koleksi->pengarang }}
                                </span>
                            </td>
                            <td class="text-start uppercase">
                                <span class="fw-bold text-dark d-block">{{ $koleksi->penerbit }}</span>
                                <span class="text-muted font-monospace" style="font-size: 11px;">THN: {{ $koleksi->tahun_terbit }}</span>
                            </td>
                            <td class="text-center font-monospace fs-6">
                                {{ $koleksi->jumlah_stok }}
                            </td>
                            <td class="text-center">
                                @if($koleksi->jumlah_tersedia > 0)
                                    <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 11px;">
                                        {{ $koleksi->jumlah_tersedia }}
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 11px;">
                                        HABIS
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group rounded-0" role="group">
                                    <a href="{{ route('perpustakaan.koleksi.show', $koleksi) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('perpustakaan.koleksi.edit', $koleksi) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('perpustakaan.koleksi.destroy', $koleksi) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus master buku ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 uppercase fw-bold text-muted">
                                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                Tidak ada koleksi buku yang terdaftar di pangkalan data.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginasi Flat --}}
        @if($koleksis->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $koleksis->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection