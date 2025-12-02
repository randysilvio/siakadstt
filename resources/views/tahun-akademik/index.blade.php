@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Tahun Akademik</h1>
        {{-- Tombol Tambah dengan Ikon --}}
        <a href="{{ route('admin.tahun-akademik.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tahun</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Periode KRS</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tahun_akademiks as $ta)
                            <tr>
                                <td class="fw-bold">{{ $ta->tahun }}</td>
                                <td>{{ $ta->semester }}</td>
                                <td>
                                    @if ($ta->is_active)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($ta->tanggal_mulai_krs)->isoFormat('D MMM Y') }} - 
                                        {{ \Carbon\Carbon::parse($ta->tanggal_selesai_krs)->isoFormat('D MMM Y') }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    {{-- Tombol Aktifkan --}}
                                    @if(!$ta->is_active)
                                        <form action="{{ route('admin.tahun-akademik.set-active', $ta->id) }}" method="POST" class="d-inline me-2">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Set Aktif">
                                                <i class="bi bi-power"></i> Aktifkan
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Group Tombol Edit & Hapus --}}
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.tahun-akademik.edit', $ta->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.tahun-akademik.destroy', $ta->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus tahun akademik ini? Data terkait mungkin akan terpengaruh.')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                    Belum ada data tahun akademik.
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