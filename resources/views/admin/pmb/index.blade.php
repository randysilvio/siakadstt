@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h3 class="fw-bold text-teal-700 mb-0">Data Pendaftar PMB</h3>
    </div>

    {{-- BARIS FILTER & PENCARIAN --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                {{-- Filter Status --}}
                <div class="col-md-7">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.pmb.index') }}" class="btn btn-outline-secondary btn-sm {{ !request('status') ? 'active' : '' }}">Semua</a>
                        <a href="{{ route('admin.pmb.index', ['status' => 'menunggu_verifikasi', 'search' => request('search')]) }}" class="btn btn-warning btn-sm text-dark {{ request('status') == 'menunggu_verifikasi' ? 'active fw-bold' : '' }}">Perlu Verifikasi</a>
                        <a href="{{ route('admin.pmb.index', ['status' => 'lulus', 'search' => request('search')]) }}" class="btn btn-success btn-sm {{ request('status') == 'lulus' ? 'active fw-bold' : '' }}">Sudah Lulus</a>
                    </div>
                </div>

                {{-- Kolom Smart Search --}}
                <div class="col-md-5">
                    <form action="{{ route('admin.pmb.index') }}" method="GET" class="input-group input-group-sm">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama / No. Pendaftaran..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.pmb.index', ['status' => request('status')]) }}" class="btn btn-danger">
                                <i class="bi bi-x"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
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
                            <th>Waktu Mendaftar</th> {{-- Kolom Baru --}}
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendaftars as $item)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-secondary">{{ $item->no_pendaftaran ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->user->name ?? 'Tanpa Nama' }}</div>
                                    <div class="small text-muted">{{ $item->no_hp ?? '-' }}</div>
                                </td>
                                <td>{{ $item->prodi1->nama_prodi ?? '-' }}</td>
                                <td>
                                    {{-- Menampilkan Tanggal dan Jam mendaftar --}}
                                    <div class="small fw-bold">{{ $item->created_at->translatedFormat('d M Y') }}</div>
                                    <div class="small text-muted">{{ $item->created_at->format('H:i') }} WIT</div>
                                </td>
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
                                    <a href="{{ route('admin.pmb.show', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada data pendaftar ditemukan.
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
                {{ $pendaftars->links() }}
            </div>
        @endif
    </div>
</div>
@endsection