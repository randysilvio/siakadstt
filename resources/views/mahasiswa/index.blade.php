@extends('layouts.app')

{{-- [PERBAIKAN 1] Menambahkan CDN Bootstrap Icons agar icon pensil dan sampah muncul --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Daftar Mahasiswa</h1>
        <div>
             <a href="{{ route('admin.mahasiswa.create') }}" class="btn btn-primary me-2"><i class="bi bi-plus-lg"></i> Tambah Baru</a>
             <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Ekspor/Impor
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.mahasiswa.export', request()->query()) }}">Ekspor ke Excel</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">Impor dari Excel</a></li>
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
            <form action="{{ route('admin.mahasiswa.index') }}" method="GET" id="filterForm">
                <div class="row g-2">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Cari Nama / NIM..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Program Studi --}}
                    <div class="col-md-3">
                        <select class="form-select" name="program_studi_id" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('program_studi_id') == '' ? 'selected' : '' }}>-- Semua Prodi --</option>
                            @foreach($program_studis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- [PERBAIKAN 2] Filter Angkatan - Memastikan opsi default terpilih --}}
                    <div class="col-md-2">
                        <select class="form-select" name="angkatan" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('angkatan') == '' ? 'selected' : '' }}>-- Angkatan --</option>
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
                        <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>-- Status --</option>
                            @foreach(['Aktif', 'Cuti', 'Lulus', 'Keluar', 'DO'] as $st)
                                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                    {{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-2">
                        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
                
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Tabel Mahasiswa --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Program Studi</th>
                            <th>Angkatan</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mahasiswas as $mahasiswa)
                            <tr>
                                <td class="fw-bold">{{ $mahasiswa->nim }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $mahasiswa->nama_lengkap }}</div>
                                    <small class="text-muted" style="font-size: 0.85em;">{{ $mahasiswa->user->email ?? '-' }}</small>
                                </td>
                                <td>{{ $mahasiswa->programStudi->nama_prodi }}</td>
                                <td>{{ $mahasiswa->tahun_masuk ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($mahasiswa->status_mahasiswa) {
                                            'Aktif' => 'bg-success',
                                            'Cuti' => 'bg-warning text-dark',
                                            'Lulus' => 'bg-info text-dark',
                                            'Keluar' => 'bg-danger',
                                            'DO' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $mahasiswa->status_mahasiswa }}</span>
                                </td>
                                <td class="text-end">
                                    {{-- Tombol Aksi --}}
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.mahasiswa.edit', $mahasiswa->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.mahasiswa.destroy', $mahasiswa->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus mahasiswa ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
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
    <div class="d-flex justify-content-center mt-4">
        {{ $mahasiswas->appends(request()->query())->links() }}
    </div>

    {{-- Modal Import --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Impor Data Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">Gunakan template resmi untuk menghindari error.</p>
                        <a href="{{ route('admin.mahasiswa.import.template') }}" class="btn btn-outline-primary btn-sm mb-3 w-100"><i class="bi bi-download"></i> Unduh Template Excel</a>
                        <div class="mb-3">
                            <label for="file" class="form-label">Upload File Excel</label>
                            <input class="form-control" type="file" name="file" id="file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Mulai Impor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection