@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Koleksi Buku</h3>
            <span class="text-muted small uppercase">Kelola Katalog Inventaris & Sirkulasi Perpustakaan Terpusat</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.koleksi.create') }}" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Master Buku
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-white border-bottom border-dark border-2 rounded-0 py-3 uppercase fw-bold small d-flex justify-content-between align-items-center">
            <span class="text-dark">Daftar Rekapitulasi Katalog Buku</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th style="width: 7%;">SAMPUL</th>
                            <th class="text-start" style="width: 33%;">JUDUL & PENGARANG</th>
                            <th class="text-start" style="width: 20%;">PENERBIT</th>
                            <th style="width: 20%;">KAPASITAS STOK FISIK</th>
                            <th style="width: 15%;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($koleksis as $koleksi)
                        @php
                            $stokRatio = $koleksi->jumlah_stok > 0 ? ($koleksi->jumlah_tersedia / $koleksi->jumlah_stok) * 100 : 0;
                            $barColor = $stokRatio > 50 ? 'bg-success' : ($stokRatio > 0 ? 'bg-warning' : 'bg-danger');
                        @endphp
                        <tr>
                            <td class="text-center font-monospace fw-bold text-muted">
                                {{ $loop->iteration + $koleksis->firstItem() - 1 }}
                            </td>
                            <td class="text-center">
                                @if($koleksi->gambar_sampul)
                                    <img src="{{ Storage::url($koleksi->gambar_sampul) }}" class="rounded-0 border border-dark border-opacity-25 d-block mx-auto" width="40" height="55" style="object-fit: cover;" alt="Sampul">
                                @else
                                    <div class="bg-light rounded-0 border border-dark border-opacity-25 d-flex align-items-center justify-content-center text-muted mx-auto" style="width: 40px; height: 55px;">
                                        <i class="bi bi-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="text-start ps-3">
                                <div class="uppercase fw-bold text-dark mb-1 line-clamp-1" title="{{ $koleksi->judul }}">{{ $koleksi->judul }}</div>
                                <span class="text-muted font-monospace" style="font-size: 11px;">
                                    PENULIS: {{ $koleksi->pengarang }}
                                </span>
                            </td>
                            <td class="text-start uppercase">
                                <span class="fw-bold text-dark d-block line-clamp-1">{{ $koleksi->penerbit }}</span>
                                <span class="text-muted font-monospace" style="font-size: 11px;">RILIS: {{ $koleksi->tahun_terbit }}</span>
                            </td>
                            <td class="px-3">
                                <div class="d-flex justify-content-between font-monospace mb-1" style="font-size: 11px;">
                                    <span class="fw-bold {{ $koleksi->jumlah_tersedia > 0 ? 'text-success' : 'text-danger' }}">{{ $koleksi->jumlah_tersedia }} SISA</span>
                                    <span class="text-muted">{{ $koleksi->jumlah_stok }} TOTAL</span>
                                </div>
                                <div class="progress rounded-0" style="height: 6px; background-color: #e9ecef;">
                                    <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $stokRatio }}%;"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group rounded-0 shadow-sm" role="group">
                                    <a href="{{ route('perpustakaan.koleksi.show', $koleksi) }}" class="btn btn-sm btn-light border-dark border-opacity-25 py-1 px-2" title="Detail & Barcode">
                                        <i class="bi bi-upc-scan text-dark"></i>
                                    </a>
                                    <a href="{{ route('perpustakaan.koleksi.edit', $koleksi) }}" class="btn btn-sm btn-light border-dark border-opacity-25 py-1 px-2" title="Edit Master">
                                        <i class="bi bi-pencil-square text-dark"></i>
                                    </a>
                                    <form action="{{ route('perpustakaan.koleksi.destroy', $koleksi) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Hapus master buku akan menghapus seluruh data barcode fisiknya. Lanjutkan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger rounded-0 py-1 px-2 border-dark border-opacity-25" title="Hapus Permanen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                <i class="bi bi-journal-x fs-2 d-block mb-2 opacity-50"></i>
                                Pangkalan data master buku masih kosong.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
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

@push('styles')
<style>
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endpush