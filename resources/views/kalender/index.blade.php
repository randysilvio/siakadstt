@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0 fw-bold">Manajemen Kalender Akademik</h1>
            <p class="text-muted mb-0">Atur jadwal dan kegiatan akademik kampus.</p>
        </div>
        <a href="{{ route('admin.kalender.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kegiatan
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Statistik Ringkas & Filter --}}
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 display-4">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1">Kegiatan Bulan Ini</h5>
                        <p class="card-text mb-0 op-75">
                            Total ada <strong>{{ $kegiatans->count() }}</strong> kegiatan yang terdaftar dalam daftar di bawah ini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <label class="form-label text-muted small fw-bold text-uppercase">Cari Kegiatan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Ketik judul kegiatan...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Kegiatan --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 5%" class="text-center py-3">#</th>
                            <th style="width: 40%" class="py-3">Nama Kegiatan</th>
                            <th style="width: 25%" class="py-3">Waktu Pelaksanaan</th>
                            <th style="width: 20%" class="py-3">Target Peserta</th>
                            <th class="text-end py-3 px-4" style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kegiatans as $kegiatan)
                            <tr>
                                <td class="text-center fw-bold text-secondary">{{ $loop->iteration + ($kegiatans->currentPage() - 1) * $kegiatans->perPage() }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $kegiatan->judul_kegiatan }}</div>
                                    @if($kegiatan->deskripsi)
                                        <div class="text-muted small text-truncate" style="max-width: 350px;">
                                            {{ Str::limit($kegiatan->deskripsi, 60) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-2 me-2 text-center" style="min-width: 50px;">
                                            <span class="d-block fw-bold text-primary" style="font-size: 1.1rem; line-height: 1;">{{ $kegiatan->tanggal_mulai->format('d') }}</span>
                                            <span class="d-block text-uppercase small text-muted" style="font-size: 0.7rem;">{{ $kegiatan->tanggal_mulai->isoFormat('MMM') }}</span>
                                        </div>
                                        <div class="small text-secondary">
                                            @if($kegiatan->tanggal_mulai->eq($kegiatan->tanggal_selesai))
                                                <span class="d-block">Satu Hari</span>
                                                <span class="text-muted">{{ $kegiatan->tanggal_mulai->format('Y') }}</span>
                                            @else
                                                <span class="d-block">s/d {{ $kegiatan->tanggal_selesai->isoFormat('D MMM') }}</span>
                                                <span class="text-muted">{{ $kegiatan->tanggal_selesai->format('Y') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @foreach($kegiatan->roles as $role)
                                        @php
                                            $badgeColor = match($role->name) {
                                                'mahasiswa' => 'bg-warning text-dark',
                                                'dosen' => 'bg-info text-dark',
                                                default => 'bg-secondary text-white'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeColor }} bg-opacity-75 me-1 mb-1">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="text-end px-4">
                                    {{-- [PERBAIKAN] Mengganti dropdown dengan tombol langsung --}}
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.kalender.edit', $kegiatan->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.kalender.destroy', $kegiatan->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kegiatan ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" alt="Empty" style="width: 64px; opacity: 0.5;" class="mb-3">
                                    <p class="text-muted fw-bold">Belum ada kegiatan akademik.</p>
                                    <a href="{{ route('admin.kalender.create') }}" class="btn btn-sm btn-outline-primary">Buat Kegiatan Sekarang</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $kegiatans->links() }}
    </div>
</div>
@endsection