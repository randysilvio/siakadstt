@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Daftar Mahasiswa</h3>
            <span class="text-muted small uppercase">Rekapitulasi & Master Data Mahasiswa Aktif</span>
        </div>
        <div>
            <a href="{{ route('admin.mahasiswa.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small me-2">
                <i class="bi bi-plus-lg me-1"></i> Tambah Baru
            </a>
            <div class="btn-group rounded-0">
                <button type="button" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor/Impor
                </button>
                <ul class="dropdown-menu rounded-0">
                    <li><a class="dropdown-item uppercase small fw-bold" href="{{ route('admin.mahasiswa.export', request()->query()) }}">Ekspor ke Excel</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item uppercase small fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#importModal">Impor dari Excel</a></li>
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
            <form action="{{ route('admin.mahasiswa.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" class="form-control rounded-0 font-monospace uppercase" placeholder="CARI NAMA / NIM..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Program Studi --}}
                    <div class="col-md-3">
                        <select class="form-select rounded-0 uppercase small fw-bold" name="program_studi_id" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('program_studi_id') == '' ? 'selected' : '' }}>-- SEMUA PRODI --</option>
                            @foreach($program_studis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Angkatan --}}
                    <div class="col-md-2">
                        <select class="form-select rounded-0 font-monospace" name="angkatan" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('angkatan') == '' ? 'selected' : '' }}>-- ANGKATAN --</option>
                            @foreach($angkatans as $thn)
                                @if($thn) 
                                    <option value="{{ $thn }}" {{ request('angkatan') == $thn ? 'selected' : '' }}>
                                        {{ $thn }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-md-2">
                        <select class="form-select rounded-0 uppercase small fw-bold" name="status" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>-- STATUS --</option>
                            @foreach(['Aktif', 'Cuti', 'Lulus', 'Keluar', 'DO'] as $st)
                                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                    {{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-2">
                        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-dark rounded-0 w-100 uppercase fw-bold small py-2">Reset Filter</a>
                    </div>
                </div>
                
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Tabel Mahasiswa Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Sistem
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 15%;">NIM</th>
                            <th class="text-start">NAMA LENGKAP</th>
                            <th class="text-start" style="width: 25%;">PROGRAM STUDI</th>
                            <th style="width: 10%;">ANGKATAN</th>
                            <th style="width: 10%;">STATUS</th>
                            <th style="width: 12%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($mahasiswas as $mahasiswa)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-dark">{{ $mahasiswa->nim }}</td>
                                <td class="text-start">
                                    <div class="uppercase fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</div>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">{{ $mahasiswa->user->email ?? '-' }}</span>
                                </td>
                                <td class="text-start uppercase fw-bold text-muted">{{ $mahasiswa->programStudi->nama_prodi }}</td>
                                <td class="text-center font-monospace">{{ $mahasiswa->tahun_masuk ?? '-' }}</td>
                                <td class="text-center">
                                    @php
                                        $badgeClass = match($mahasiswa->status_mahasiswa) {
                                            'Aktif' => 'bg-success text-white',
                                            'Cuti' => 'bg-warning text-dark',
                                            'Lulus' => 'bg-info text-dark',
                                            'Keluar' => 'bg-danger text-white',
                                            'DO' => 'bg-danger text-white',
                                            default => 'bg-secondary text-white'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-0 uppercase fw-bold" style="font-size: 10px;">{{ $mahasiswa->status_mahasiswa }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('admin.mahasiswa.edit', $mahasiswa->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.mahasiswa.destroy', $mahasiswa->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" onclick="return confirm('Hapus mahasiswa ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Data tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Footer Paginasi Flat --}}
        <div class="card-footer bg-white border-top py-3 rounded-0">
            <div class="d-flex justify-content-center">
                {{ $mahasiswas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Import Siku --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog rounded-0">
            <div class="modal-content rounded-0 border-dark border-2">
                <div class="modal-header bg-dark text-white rounded-0">
                    <h6 class="modal-title uppercase fw-bold small">Impor Data Mahasiswa</h6>
                    <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">Gunakan template resmi untuk menghindari error import.</p>
                        <a href="{{ route('admin.mahasiswa.import.template') }}" class="btn btn-outline-dark rounded-0 btn-sm mb-4 w-100 uppercase fw-bold small py-2">
                            <i class="bi bi-download me-1"></i> Unduh Template Excel
                        </a>
                        <div class="mb-3">
                            <label for="file" class="form-label uppercase fw-bold small text-dark">Upload File Excel</label>
                            <input class="form-control rounded-0" type="file" name="file" id="file" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-0">
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">Mulai Impor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection