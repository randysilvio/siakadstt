@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Tahun Akademik</h3>
            <span class="text-muted small uppercase">Pengaturan Siklus Periode Perkuliahan & Aktivasi Akses KRS</span>
        </div>
        <div>
            <a href="{{ route('admin.tahun-akademik.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Baru
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

    {{-- Tabel Utama Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Tahun Akademik
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 15%;">TAHUN AKADEMIK</th>
                            <th style="width: 15%;">SEMESTER</th>
                            <th style="width: 15%;">STATUS AKTIF</th>
                            <th class="text-start" style="width: 35%;">PERIODE PENGISIAN KRS</th>
                            <th style="width: 20%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($tahun_akademiks as $ta)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-dark">
                                    {{ $ta->tahun }}
                                </td>
                                <td class="text-center uppercase fw-bold text-muted">
                                    {{ $ta->semester }}
                                </td>
                                <td class="text-center">
                                    @if ($ta->is_active)
                                        <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            AKTIF
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            TIDAK AKTIF
                                        </span>
                                    @endif
                                </td>
                                <td class="text-start ps-3 font-monospace text-muted">
                                    @if($ta->tanggal_mulai_krs && $ta->tanggal_selesai_krs)
                                        <strong class="text-dark">{{ \Carbon\Carbon::parse($ta->tanggal_mulai_krs)->isoFormat('D MMM Y') }}</strong>
                                        <span> s/d </span>
                                        <strong class="text-dark">{{ \Carbon\Carbon::parse($ta->tanggal_selesai_krs)->isoFormat('D MMM Y') }}</strong>
                                    @else
                                        <span class="text-danger small fw-bold font-monospace uppercase">BELUM DITENTUKAN</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Tombol Aktifkan --}}
                                        @if(!$ta->is_active)
                                            <form action="{{ route('admin.tahun-akademik.set-active', $ta->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Set Aktif">
                                                    <i class="bi bi-power"></i> Aktifkan
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Group Tombol Edit & Hapus --}}
                                        <div class="btn-group rounded-0" role="group">
                                            <a href="{{ route('admin.tahun-akademik.edit', $ta->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('admin.tahun-akademik.destroy', $ta->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus tahun akademik ini? Data terkait mungkin akan terpengaruh.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada data tahun akademik di pangkalan data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginasi Flat --}}
        @if(method_exists($tahun_akademiks, 'hasPages') && $tahun_akademiks->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $tahun_akademiks->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection