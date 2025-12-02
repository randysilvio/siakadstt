@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Berita & Pengumuman</h1>
        <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Buat Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Smart Filter Bar --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body bg-light">
            <form action="{{ route('admin.pengumuman.index') }}" method="GET" id="filterForm">
                <div class="row g-2">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Cari Judul..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Kategori --}}
                    <div class="col-md-3">
                        <select class="form-select" name="kategori" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('kategori') == '' ? 'selected' : '' }}>-- Semua Kategori --</option>
                            <option value="berita" {{ request('kategori') == 'berita' ? 'selected' : '' }}>Berita</option>
                            <option value="pengumuman" {{ request('kategori') == 'pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                        </select>
                    </div>

                    {{-- Filter Target Role --}}
                    <div class="col-md-3">
                        <select class="form-select" name="target_role" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('target_role') == '' ? 'selected' : '' }}>-- Semua Target --</option>
                            <option value="semua" {{ request('target_role') == 'semua' ? 'selected' : '' }}>Semua Pengguna</option>
                            <option value="dosen" {{ request('target_role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="mahasiswa" {{ request('target_role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="tendik" {{ request('target_role') == 'tendik' ? 'selected' : '' }}>Tendik</option>
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-2">
                        <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Judul & Konten</th>
                            <th style="width: 15%">Kategori</th>
                            <th style="width: 15%">Target</th>
                            <th style="width: 15%">Tanggal</th>
                            <th class="text-end" style="width: 15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengumumans as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $item->judul }}</div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 300px;">
                                        {{ Str::limit(strip_tags($item->konten), 60) }}
                                    </small>
                                </td>
                                <td>
                                    @if($item->kategori == 'berita')
                                        <span class="badge bg-primary bg-opacity-75">Berita</span>
                                    @else
                                        <span class="badge bg-info bg-opacity-75 text-dark">Pengumuman</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($item->target_role) }}</span>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $item->created_at->format('d M Y') }}
                                    </div>
                                    <div class="small text-muted">{{ $item->created_at->format('H:i') }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.pengumuman.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.pengumuman.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-megaphone fs-1 d-block mb-2"></i>
                                    Belum ada berita atau pengumuman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4 d-flex justify-content-center">
        {{ $pengumumans->appends(request()->query())->links() }}
    </div>
</div>
@endsection