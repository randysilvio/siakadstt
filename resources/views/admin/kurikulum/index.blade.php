@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Kurikulum Akademik</h3>
            <span class="text-muted small uppercase">Pengaturan Basis Standar Pembelajaran & Kurikulum per Program Studi</span>
        </div>
        <div>
            <a href="{{ route('admin.kurikulum.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kurikulum
            </a>
        </div>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tabel Utama Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Kurikulum Institusi
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 7%;">NO</th>
                            <th class="text-start" style="width: 33%;">IDENTITAS KURIKULUM</th>
                            <th class="text-start" style="width: 25%;">PROGRAM STUDI</th>
                            <th style="width: 10%;">TAHUN</th>
                            <th style="width: 10%;">STATUS</th>
                            <th style="width: 15%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($kurikulums as $index => $kurikulum)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="text-start ps-3 fw-bold text-dark uppercase">
                                    <i class="bi bi-journal-bookmark text-dark me-2"></i>{{ $kurikulum->nama_kurikulum }}
                                </td>
                                <td class="text-start ps-3 uppercase fw-bold text-muted">
                                    {{ $kurikulum->programStudi->nama_prodi ?? 'UMUM / GLOBAL' }}
                                </td>
                                <td class="text-center font-monospace fw-bold">{{ $kurikulum->tahun }}</td>
                                <td class="text-center">
                                    @if ($kurikulum->is_active)
                                        <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if (!$kurikulum->is_active)
                                            <form action="{{ route('admin.kurikulum.setActive', $kurikulum->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Set Aktif">
                                                    <i class="bi bi-power"></i> Aktifkan
                                                </button>
                                            </form>
                                        @endif

                                        <div class="btn-group rounded-0" role="group">
                                            <a href="{{ route('admin.kurikulum.edit', $kurikulum->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('admin.kurikulum.destroy', $kurikulum->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Menghapus kurikulum ini berpotensi memengaruhi relasi mata kuliah yang bernaung di bawahnya. Lanjutkan?');">
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
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum terdapat data kurikulum akademik yang didaftarkan ke dalam sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection