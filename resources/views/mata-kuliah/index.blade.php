@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Mata Kuliah</h1>
        <div>
            <a href="{{ route('admin.mata-kuliah.create') }}" class="btn btn-primary me-2"><i class="bi bi-plus-lg"></i> Tambah Baru</a>
            
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Ekspor/Impor
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.mata-kuliah.export') }}">Export Mata Kuliah</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importMataKuliahModal">Import Mata Kuliah</a></li>
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
            <form action="{{ route('admin.mata-kuliah.index') }}" method="GET" id="filterForm">
                <div class="row g-2">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari Kode atau Nama MK..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Kurikulum --}}
                    <div class="col-md-3">
                        <select name="kurikulum_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('kurikulum_id') == '' ? 'selected' : '' }}>-- Semua Kurikulum --</option>
                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id }}" {{ request('kurikulum_id') == $kurikulum->id ? 'selected' : '' }}>
                                    {{ $kurikulum->nama_kurikulum }} ({{ $kurikulum->tahun }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Semester --}}
                    <div class="col-md-3">
                        <select name="semester" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('semester') == '' ? 'selected' : '' }}>-- Semua Semester --</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-2">
                        <a href="{{ route('admin.mata-kuliah.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Tabel Mata Kuliah --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Semester</th>
                            <th>Kurikulum</th>
                            <th>Dosen Pengampu</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mata_kuliahs as $matkul)
                            <tr>
                                <td class="fw-bold">{{ $matkul->kode_mk }}</td>
                                <td>{{ $matkul->nama_mk }}</td>
                                <td><span class="badge bg-secondary">{{ $matkul->sks }} SKS</span></td>
                                <td>Sem {{ $matkul->semester }}</td>
                                <td><small class="text-muted">{{ $matkul->kurikulum->nama_kurikulum ?? '-' }}</small></td>
                                <td>
                                    @if($matkul->dosen)
                                        <div>{{ $matkul->dosen->nama_lengkap }}</div>
                                    @else
                                        <span class="text-muted fst-italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.mata-kuliah.edit', $matkul->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.mata-kuliah.destroy', $matkul->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus mata kuliah ini?');" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                    Data tidak ditemukan dengan filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginasi --}}
    @if($mata_kuliahs->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $mata_kuliahs->links() }}
    </div>
    @endif

    {{-- Modal Import (Tetap sama) --}}
    <div class="modal fade" id="importMataKuliahModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.mata-kuliah.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih file Excel (.xlsx, .xls)</label>
                            <input class="form-control" type="file" id="file" name="file" required>
                        </div>
                        <a href="{{ route('admin.mata-kuliah.import.template') }}" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-download"></i> Unduh Template Excel</a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection