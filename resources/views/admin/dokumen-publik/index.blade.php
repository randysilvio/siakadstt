@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Dokumen Publik</h1>
        <a href="{{ route('admin.dokumen-publik.create') }}" class="btn btn-primary">
            <i class="bi bi-upload me-1"></i> Unggah Dokumen
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body bg-light">
            <form action="{{ route('admin.dokumen-publik.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Cari Judul Dokumen..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%">Judul Dokumen</th>
                            <th style="width: 35%">Deskripsi</th>
                            <th style="width: 20%">Tanggal Unggah</th>
                            <th style="width: 15%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dokumens as $dokumen)
                            <tr>
                                <td class="fw-bold">
                                    <i class="bi bi-file-earmark-text me-2 text-secondary"></i>
                                    {{ $dokumen->judul_dokumen }}
                                </td>
                                <td class="text-muted small">
                                    {{ Str::limit($dokumen->deskripsi ?? '-', 80) }}
                                </td>
                                <td>
                                    <small>{{ $dokumen->created_at->isoFormat('D MMMM Y') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ asset('storage/' . $dokumen->file_path) }}" class="btn btn-sm btn-outline-info" target="_blank" title="Lihat / Download">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.dokumen-publik.destroy', $dokumen->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder2-open fs-1 d-block mb-2"></i>
                                    Belum ada dokumen yang diunggah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    @if($dokumens->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $dokumens->links() }}
    </div>
    @endif
</div>
@endsection