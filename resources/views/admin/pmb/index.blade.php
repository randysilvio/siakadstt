@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h3 class="fw-bold text-teal-700 mb-0">Data Pendaftar PMB</h3>
        
        {{-- TOMBOL CETAK LAPORAN (OPSI C) --}}
        <div class="btn-group shadow-sm">
            <a href="{{ route('admin.pmb.export.excel', request()->all()) }}" class="btn btn-success fw-bold">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <a href="{{ route('admin.pmb.export.pdf', request()->all()) }}" target="_blank" class="btn btn-danger fw-bold">
                <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
            </a>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- BARIS SMART FILTER & PENCARIAN --}}
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-3">
            <form action="{{ route('admin.pmb.index') }}" method="GET" id="pmbFilterForm">
                <div class="row g-2 align-items-center">
                    
                    {{-- Filter Status (Pills) --}}
                    <div class="col-md-12 mb-2">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.pmb.index') }}" class="btn btn-outline-secondary btn-sm {{ !request('status') ? 'active' : '' }}">Semua Status</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'menunggu_verifikasi'])) }}" class="btn btn-warning btn-sm text-dark {{ request('status') == 'menunggu_verifikasi' ? 'active fw-bold' : '' }}">Perlu Verifikasi</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'lulus'])) }}" class="btn btn-success btn-sm {{ request('status') == 'lulus' ? 'active fw-bold' : '' }}">Sudah Lulus</a>
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        </div>
                    </div>

                    {{-- Dropdown Gelombang --}}
                    <div class="col-md-3">
                        <select class="form-select form-select-sm shadow-sm" name="pmb_period_id" onchange="document.getElementById('pmbFilterForm').submit()">
                            <option value="">-- Semua Gelombang --</option>
                            @foreach($periods as $per)
                                <option value="{{ $per->id }}" {{ request('pmb_period_id') == $per->id ? 'selected' : '' }}>{{ $per->nama_gelombang }} ({{ $per->tahun_akademik }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Program Studi --}}
                    <div class="col-md-3">
                        <select class="form-select form-select-sm shadow-sm" name="pilihan_prodi_1_id" onchange="document.getElementById('pmbFilterForm').submit()">
                            <option value="">-- Semua Prodi Pilihan 1 --</option>
                            @foreach($prodis as $prd)
                                <option value="{{ $prd->id }}" {{ request('pilihan_prodi_1_id') == $prd->id ? 'selected' : '' }}>{{ $prd->nama_prodi }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kolom Smart Search Teks --}}
                    <div class="col-md-5">
                        <div class="input-group input-group-sm shadow-sm">
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama / No. Pendaftaran..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                        </div>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-1">
                        <a href="{{ route('admin.pmb.index') }}" class="btn btn-outline-danger btn-sm w-100 shadow-sm"><i class="bi bi-x-circle"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA PENDAFTAR --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">No Pendaftaran</th>
                            <th>Nama Lengkap</th>
                            <th>Prodi Pilihan 1</th>
                            <th>Gelombang</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendaftars as $item)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-secondary">{{ $item->no_pendaftaran ?? '-' }}</span>
                                    <div class="small text-muted">{{ $item->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->user->name ?? 'Tanpa Nama' }}</div>
                                    <div class="small text-muted"><i class="bi bi-whatsapp"></i> {{ $item->no_hp ?? '-' }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->prodi1->nama_prodi ?? '-' }}</span></td>
                                <td>{{ $item->period->nama_gelombang ?? '-' }}</td>
                                <td>
                                    @if($item->status_pendaftaran == 'lulus')
                                        <span class="badge bg-success">DITERIMA</span>
                                    @elseif($item->status_pendaftaran == 'tidak_lulus')
                                        <span class="badge bg-danger">DITOLAK</span>
                                    @elseif($item->status_pendaftaran == 'menunggu_verifikasi')
                                        <span class="badge bg-warning text-dark">BUTUH CEK</span>
                                    @else
                                        <span class="badge bg-secondary">DRAFT</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.pmb.show', $item->id) }}" class="btn btn-sm btn-primary" title="Detail / Verifikasi">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.pmb.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('HAPUS PERMANEN?\nSemua dokumen dan tagihan pendaftar ini akan ikut terhapus dan tidak bisa dikembalikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Bersih">
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
                                    Tidak ada data pendaftar yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- PAGINASI --}}
        @if($pendaftars->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3 pb-3 px-4">
                {{ $pendaftars->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection