@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Slideshow</h1>
        <a href="{{ route('admin.slideshows.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Slide Baru
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
            <form action="{{ route('admin.slideshows.index') }}" method="GET" id="filterForm">
                <div class="row g-2">
                    {{-- Pencarian Judul --}}
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Cari Judul Slide..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-md-4">
                        <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>-- Semua Status --</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-2">
                        <a href="{{ route('admin.slideshows.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Tabel Slideshow --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%">Gambar</th>
                            <th style="width: 40%">Judul</th>
                            <th style="width: 10%" class="text-center">Urutan</th>
                            <th style="width: 15%" class="text-center">Status</th>
                            <th style="width: 15%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($slides as $slide)
                            <tr>
                                <td>
                                    <img src="{{ asset('storage/' . $slide->gambar) }}" alt="{{ $slide->judul }}" class="img-thumbnail" style="width: 120px; height: 80px; object-fit: cover;">
                                </td>
                                <td>{{ $slide->judul ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-circle">{{ $slide->urutan }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($slide->is_aktif)
                                        <span class="badge bg-success bg-opacity-75 text-white">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-75 text-white">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.slideshows.edit', $slide->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.slideshows.destroy', $slide->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus slide ini?');" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-images fs-1 d-block mb-2"></i>
                                    Belum ada slide yang ditambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    @if($slides->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $slides->links() }}
    </div>
    @endif
</div>
@endsection