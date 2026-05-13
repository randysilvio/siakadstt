@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Mata Kuliah</h3>
            <span class="text-muted small uppercase">Rekapitulasi Kurikulum & Alokasi Pengampu</span>
        </div>
        <div>
            <a href="{{ route('admin.mata-kuliah.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small me-2">
                <i class="bi bi-plus-lg me-1"></i> Tambah Baru
            </a>
            
            <div class="btn-group rounded-0">
                <button type="button" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor/Impor
                </button>
                <ul class="dropdown-menu rounded-0">
                    <li><a class="dropdown-item uppercase small fw-bold" href="{{ route('admin.mata-kuliah.export') }}">Ekspor ke Excel</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item uppercase small fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#importMataKuliahModal">Impor dari Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm small fw-bold uppercase" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm small fw-bold uppercase" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Smart Filter Bar Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.mata-kuliah.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" name="search" class="form-control rounded-0 font-monospace uppercase" placeholder="CARI KODE ATAU NAMA MK..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Kurikulum --}}
                    <div class="col-md-3">
                        <select name="kurikulum_id" class="form-select rounded-0 uppercase small fw-bold" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('kurikulum_id') == '' ? 'selected' : '' }}>-- SEMUA KURIKULUM --</option>
                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id }}" {{ request('kurikulum_id') == $kurikulum->id ? 'selected' : '' }}>
                                    {{ $kurikulum->nama_kurikulum }} ({{ $kurikulum->tahun }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Semester --}}
                    <div class="col-md-3">
                        <select name="semester" class="form-select rounded-0 font-monospace" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('semester') == '' ? 'selected' : '' }}>-- SEMUA SEMESTER --</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>SEMESTER {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-2">
                        <a href="{{ route('admin.mata-kuliah.index') }}" class="btn btn-outline-dark rounded-0 w-100 uppercase fw-bold small py-2">Reset Filter</a>
                    </div>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Tabel Mata Kuliah Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Sistem
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-dark text-white small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 12%;">KODE MK</th>
                            <th class="text-start">NAMA MATA KULIAH</th>
                            <th style="width: 8%;">SKS</th>
                            <th style="width: 12%;">SEMESTER</th>
                            <th class="text-start" style="width: 20%;">KURIKULUM</th>
                            <th class="text-start" style="width: 20%;">DOSEN PENGAMPU</th>
                            <th style="width: 10%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($mata_kuliahs as $matkul)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-primary">{{ $matkul->kode_mk }}</td>
                                <td class="text-start uppercase fw-bold text-dark">{{ $matkul->nama_mk }}</td>
                                <td class="text-center">
                                    <span class="badge bg-dark rounded-0 font-monospace px-2 py-1" style="font-size: 11px;">{{ $matkul->sks }} SKS</span>
                                </td>
                                <td class="text-center font-monospace">SEM {{ $matkul->semester }}</td>
                                <td class="text-start uppercase text-muted">{{ $matkul->kurikulum->nama_kurikulum ?? '-' }}</td>
                                <td class="text-start uppercase">
                                    @if($matkul->dosen)
                                        <span class="fw-bold text-dark">{{ $matkul->dosen->nama_lengkap }}</span>
                                    @else
                                        <span class="text-danger small fw-bold font-monospace">BELUM DITENTUKAN</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('admin.mata-kuliah.edit', $matkul->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.mata-kuliah.destroy', $matkul->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" onclick="return confirm('Yakin ingin menghapus mata kuliah ini?');" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                    Data tidak ditemukan dengan filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginasi Flat --}}
        @if($mata_kuliahs->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $mata_kuliahs->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Import Siku --}}
    <div class="modal fade" id="importMataKuliahModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog rounded-0">
            <div class="modal-content rounded-0 border-dark border-2">
                <div class="modal-header bg-dark text-white rounded-0">
                    <h6 class="modal-title uppercase fw-bold small">Impor Data Mata Kuliah</h6>
                    <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.mata-kuliah.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">Gunakan template resmi untuk menghindari kesalahan format data.</p>
                        <a href="{{ route('admin.mata-kuliah.import.template') }}" class="btn btn-outline-dark rounded-0 btn-sm mb-4 w-100 uppercase fw-bold small py-2">
                            <i class="bi bi-download me-1"></i> Unduh Template Excel
                        </a>
                        <div class="mb-3">
                            <label for="file" class="form-label uppercase fw-bold small text-dark">Pilih File Excel (.xlsx, .xls)</label>
                            <input class="form-control rounded-0" type="file" id="file" name="file" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-0">
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">Mulai Impor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection