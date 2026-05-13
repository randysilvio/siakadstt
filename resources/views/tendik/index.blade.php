@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Daftar Pegawai & Tendik</h3>
            <span class="text-muted small uppercase">Rekapitulasi Master Data Tenaga Kependidikan & Alokasi Hak Akses</span>
        </div>
        <div>
            <a href="{{ route('admin.tendik.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Pegawai Baru
            </a>
        </div>
    </div>

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Bilah Pencarian Flat UI --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.tendik.index') }}" method="GET">
                <div class="row g-2 justify-content-between">
                    <div class="col-md-6">
                        <div class="input-group rounded-0">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" class="form-control rounded-0 font-monospace uppercase" name="search" placeholder="CARI NAMA LENGKAP / NIP / JABATAN..." value="{{ request('search') }}">
                            <button class="btn btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm" type="submit">Cari Data</button>
                        </div>
                    </div>
                    @if(request()->has('search') && request('search') != '')
                        <div class="col-md-2 text-end">
                            <a href="{{ route('admin.tendik.index') }}" class="btn btn-outline-dark rounded-0 w-100 uppercase fw-bold small py-2">Reset Pencarian</a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Utama Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Sistem
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th class="text-start" style="width: 25%;">NAMA LENGKAP & NIP</th>
                            <th style="width: 15%;">UNIT KERJA</th>
                            <th class="text-start" style="width: 20%;">JABATAN</th>
                            <th style="width: 13%;">JENIS TENDIK</th>
                            <th style="width: 12%;">HAK AKSES</th>
                            <th style="width: 10%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($tendiks as $key => $tendik)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">
                                    {{ $tendiks->firstItem() + $key }}
                                </td>
                                <td class="text-start ps-3">
                                    <div class="uppercase fw-bold text-dark">{{ $tendik->nama_lengkap }}</div>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">
                                        NIP: {{ $tendik->nip_yayasan ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center font-monospace fw-bold text-dark">
                                    {{ $tendik->unit_kerja }}
                                </td>
                                <td class="text-start ps-3 uppercase">
                                    {{ $tendik->jabatan }}
                                </td>
                                <td class="text-center uppercase">
                                    {{ $tendik->jenis_tendik }}
                                </td>
                                <td class="text-center">
                                    @if($tendik->user && $tendik->user->roles->count() > 0)
                                        @foreach($tendik->user->roles as $role)
                                            <span class="badge bg-dark text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                                {{ $role->display_name ?? $role->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted font-monospace small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('admin.tendik.edit', $tendik->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.tendik.destroy', $tendik->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data pegawai ini secara permanen? Akun login yang tertaut juga akan terhapus dari sistem.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-person-slash fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Data tenaga kependidikan belum tersedia di pangkalan data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginasi Flat --}}
        @if(method_exists($tendiks, 'hasPages') && $tendiks->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $tendiks->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection