@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Program Studi</h3>
            <span class="text-muted small uppercase">Kelola Data Program Studi & Struktur Kepemimpinan Kaprodi</span>
        </div>
        <div>
            <a href="{{ route('admin.program-studi.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Prodi Baru
            </a>
        </div>
    </div>

    {{-- Notifikasi Sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Notifikasi Error --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tabel Utama Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Program Studi
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 8%;">NO</th>
                            <th class="text-start" style="width: 42%;">NAMA PROGRAM STUDI</th>
                            <th class="text-start" style="width: 35%;">KETUA PRODI (KAPRODI)</th>
                            <th style="width: 15%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($program_studis as $prodi)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="text-start ps-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-building me-2 text-dark"></i>
                                        <span class="uppercase fw-bold text-dark">{{ $prodi->nama_prodi }}</span>
                                    </div>
                                </td>
                                <td class="text-start ps-3">
                                    @if($prodi->kaprodi)
                                        <div class="d-flex align-items-center uppercase fw-bold text-dark">
                                            <i class="bi bi-person-badge me-2 text-muted"></i>
                                            <span>{{ $prodi->kaprodi->nama_lengkap }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            BELUM DIATUR
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('admin.program-studi.edit', $prodi->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.program-studi.destroy', $prodi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program studi ini? Seluruh kaitan master data yang merujuk pada prodi ini akan terpengaruh.');">
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
                                <td colspan="4" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-building-slash fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Data program studi belum tersedia di pangkalan data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginasi Flat --}}
        @if(method_exists($program_studis, 'hasPages') && $program_studis->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $program_studis->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection