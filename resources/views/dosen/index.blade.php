@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Dosen</h1>
        <div>
            {{-- Tombol Tambah --}}
            <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary me-2"><i class="bi bi-plus-lg"></i> Tambah Baru</a>
            
            {{-- Tombol Dropdown Export/Import --}}
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Ekspor/Impor
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.dosen.export') }}">Ekspor Data Dosen</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importDosenModal">Impor dari Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>@endif

    {{-- Smart Filter Bar --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body bg-light">
            <form action="{{ route('admin.dosen.index') }}" method="GET" id="filterForm">
                <div class="row g-2">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Cari Nama / NIDN / Email..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Jabatan Akademik --}}
                    <div class="col-md-3">
                        <select class="form-select" name="jabatan" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('jabatan') == '' ? 'selected' : '' }}>-- Jabatan Akademik --</option>
                            @foreach($jabatans as $jabatan)
                                <option value="{{ $jabatan }}" {{ request('jabatan') == $jabatan ? 'selected' : '' }}>
                                    {{ $jabatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Keahlian (Input Text) --}}
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Filter Keahlian..." name="keahlian" value="{{ request('keahlian') }}">
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-2">
                        <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
                {{-- Submit button hidden --}}
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Tabel Dosen --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>NIDN</th>
                            <th>Nama Lengkap</th>
                            <th>Jabatan & Keahlian</th>
                            <th>Email Login</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosens as $dosen)
                            <tr>
                                <td class="fw-bold">{{ $dosen->nidn }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{-- Foto Profil Kecil --}}
                                        <img src="{{ $dosen->foto_profil }}" class="rounded-circle me-2 border" width="40" height="40" alt="Foto">
                                        <div>
                                            <div class="fw-semibold">{{ $dosen->nama_lengkap }}</div>
                                            @if($dosen->is_keuangan)
                                                <span class="badge bg-success" style="font-size: 0.7em;">Staff Keuangan</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($dosen->jabatan_akademik)
                                        <div class="text-primary small fw-bold">{{ $dosen->jabatan_akademik }}</div>
                                    @endif
                                    <div class="text-muted small text-truncate" style="max-width: 200px;">
                                        {{ $dosen->bidang_keahlian ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $dosen->user->email ?? '-' }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.dosen.edit', $dosen->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.dosen.destroy', $dosen->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus data dosen ini? Akun login terkait juga akan dihapus.');" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                    Data dosen tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginasi --}}
    @if($dosens->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $dosens->appends(request()->query())->links() }}
    </div>
    @endif

    {{-- Modal Import --}}
    <div class="modal fade" id="importDosenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.dosen.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">Gunakan template resmi untuk menghindari error impor.</p>
                        <a href="{{ route('admin.dosen.import.template') }}" class="btn btn-outline-primary btn-sm mb-3 w-100"><i class="bi bi-download"></i> Unduh Template Excel</a>
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih file Excel (.xlsx, .xls)</label>
                            <input class="form-control" type="file" id="file" name="file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection