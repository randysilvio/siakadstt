@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Berita & Pengumuman</h3>
            <span class="text-muted small uppercase">Pusat Distribusi Informasi & Komunikasi Terpusat</span>
        </div>
        <div>
            <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Buat Publikasi Baru
            </a>
        </div>
    </div>

    {{-- Notifikasi Sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3" role="alert">
            <div class="d-flex align-items-center small fw-bold uppercase">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Smart Filter Bar Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.pengumuman.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" class="form-control rounded-0 font-monospace uppercase" placeholder="CARI JUDUL ATAU KONTEN..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Kategori --}}
                    <div class="col-md-3">
                        <select class="form-select rounded-0 uppercase small fw-bold" name="kategori" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('kategori') == '' ? 'selected' : '' }}>-- SEMUA KATEGORI --</option>
                            <option value="berita" {{ request('kategori') == 'berita' ? 'selected' : '' }}>Berita</option>
                            <option value="pengumuman" {{ request('kategori') == 'pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                        </select>
                    </div>

                    {{-- Filter Target Role --}}
                    <div class="col-md-3">
                        <select class="form-select rounded-0 uppercase small fw-bold" name="target_role" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('target_role') == '' ? 'selected' : '' }}>-- SEMUA TARGET --</option>
                            <option value="semua" {{ request('target_role') == 'semua' ? 'selected' : '' }}>Semua Pengguna</option>
                            <option value="dosen" {{ request('target_role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="mahasiswa" {{ request('target_role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="tendik" {{ request('target_role') == 'tendik' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-2">
                        <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-dark rounded-0 w-100 uppercase fw-bold small py-2">Reset Filter</a>
                    </div>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Tabel Utama Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Publikasi
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th class="text-start ps-3" style="width: 45%;">JUDUL & KONTEN RINGKAS</th>
                            <th style="width: 12%;">KATEGORI</th>
                            <th style="width: 15%;">DISTRIBUSI TARGET</th>
                            <th style="width: 15%;">WAKTU PUBLIKASI</th>
                            <th style="width: 13%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($pengumumans as $item)
                            <tr>
                                <td class="text-start ps-3">
                                    <div class="uppercase fw-bold text-dark mb-1">{{ $item->judul }}</div>
                                    <span class="text-muted d-block text-truncate" style="max-width: 450px; font-size: 11px;">
                                        {{ Str::limit(strip_tags($item->konten), 90) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($item->kategori == 'berita')
                                        <span class="badge bg-dark rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">BERITA</span>
                                    @else
                                        <span class="badge bg-primary rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">PENGUMUMAN</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge border border-dark text-dark rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                        {{ $item->target_role === 'tendik' ? 'TENDIK' : strtoupper($item->target_role) }}
                                    </span>
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    <strong class="text-dark d-block">{{ $item->created_at->format('d/m/Y') }}</strong>
                                    <span style="font-size: 10px;">JAM {{ $item->created_at->format('H:i') }} WIT</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        {{-- Meneruskan $item utuh untuk mencegah 404 Route Binding --}}
                                        <a href="{{ route('admin.pengumuman.edit', $item) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.pengumuman.destroy', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" onclick="return confirm('Hapus publikasi ini secara permanen?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-megaphone fs-2 d-block mb-2"></i>
                                    Belum ada data publikasi berita atau pengumuman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginasi Flat --}}
        @if($pengumumans->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $pengumumans->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection