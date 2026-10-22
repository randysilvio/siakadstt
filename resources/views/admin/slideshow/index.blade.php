@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Manajemen Slideshow (Spanduk)</h3>
            <span class="text-muted small uppercase">Pengaturan Materi Visual Publikasi pada Halaman Utama Portal</span>
        </div>
        <div>
            <a href="{{ route('admin.slideshows.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Unggah Slide Baru
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Bar Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.slideshows.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Pencarian Judul Slide</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" class="form-control rounded-0 font-monospace uppercase" placeholder="KETIK KATA KUNCI JUDUL..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Status Publikasi</label>
                        <select class="form-select rounded-0 uppercase small fw-bold" name="status" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>-- SEMUA STATUS --</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>TAMPIL (AKTIF)</option>
                            <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>DISEMBUNYIKAN (NON-AKTIF)</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2 shadow-sm">Cari</button>
                        <a href="{{ route('admin.slideshows.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Spanduk Visual Beranda
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 18%;">PREVIEW MEDIA</th>
                            <th class="text-start" style="width: 42%;">JUDUL PUBLIKASI</th>
                            <th style="width: 12%;">NO. URUT</th>
                            <th style="width: 13%;">STATUS</th>
                            <th style="width: 15%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($slides as $slide)
                            <tr>
                                <td class="text-center p-2">
                                    <div class="border border-dark border-opacity-25 rounded-0 bg-light p-1 d-inline-block">
                                        <img src="{{ asset('storage/' . $slide->gambar) }}" alt="Thumbnail" style="width: 120px; height: 60px; object-fit: cover;">
                                    </div>
                                </td>
                                <td class="text-start ps-3 fw-bold text-dark uppercase">{{ $slide->judul ?? 'TANPA JUDUL / DESKRIPSI' }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $slide->urutan }}</td>
                                <td class="text-center">
                                    @if ($slide->is_aktif)
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('admin.slideshows.edit', $slide->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.slideshows.destroy', $slide->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tindakan ini akan menghapus permanen file gambar dari sistem. Lanjutkan?');">
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
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-images fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum terdapat data material slideshow yang diunggah ke dalam sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($slides->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $slides->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection