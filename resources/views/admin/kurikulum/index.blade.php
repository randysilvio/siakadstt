@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Kurikulum</h1>
        <a href="{{ route('admin.kurikulum.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Kurikulum Baru
        </a>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kurikulum</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kurikulums as $kurikulum)
                            <tr>
                                <td class="fw-semibold">{{ $kurikulum->nama_kurikulum }}</td>
                                <td>{{ $kurikulum->tahun }}</td>
                                <td>
                                    @if ($kurikulum->is_active)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{-- Tombol Aktifkan --}}
                                    @if (!$kurikulum->is_active)
                                        <form action="{{ route('admin.kurikulum.setActive', $kurikulum->id) }}" method="POST" class="d-inline me-2">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Set Aktif">
                                                <i class="bi bi-power"></i> Aktifkan
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Group Tombol Edit & Hapus --}}
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.kurikulum.edit', $kurikulum->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.kurikulum.destroy', $kurikulum->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kurikulum ini? Data mata kuliah terkait mungkin akan terpengaruh.')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-album fs-1 d-block mb-2"></i>
                                    Belum ada data kurikulum.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection