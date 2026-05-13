@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Manajemen Kurikulum Akademik</h3>
            <span class="text-muted small">Pengaturan basis standar pembelajaran dan mata kuliah institusi</span>
        </div>
        <a href="{{ route('admin.kurikulum.create') }}" class="btn btn-primary">
            <i class="bi bi-journal-plus me-2"></i>Registrasi Kurikulum
        </a>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-1 mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger border-0 bg-danger text-white py-2 px-3 rounded-1 mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="text-center text-muted small" style="width: 60px;">NO</th>
                            <th class="text-muted small">IDENTITAS KURIKULUM</th>
                            <th class="text-center text-muted small" style="width: 150px;">TAHUN BERLAKU</th>
                            <th class="text-center text-muted small" style="width: 150px;">STATUS PENGGUNAAN</th>
                            <th class="text-end text-muted small pe-4" style="width: 250px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kurikulums as $index => $kurikulum)
                            <tr>
                                <td class="text-center text-muted font-monospace">{{ $index + 1 }}</td>
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-journal-bookmark text-secondary me-2"></i>{{ $kurikulum->nama_kurikulum }}
                                </td>
                                <td class="text-center font-monospace">{{ $kurikulum->tahun }}</td>
                                <td class="text-center">
                                    @if ($kurikulum->is_active)
                                        <span class="badge bg-success rounded-1 px-2">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary rounded-1 px-2">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if (!$kurikulum->is_active)
                                        <form action="{{ route('admin.kurikulum.setActive', $kurikulum->id) }}" method="POST" class="d-inline me-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-dark rounded-1" title="Set Kurikulum Ini Sebagai Aktif">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.kurikulum.edit', $kurikulum->id) }}" class="btn btn-sm btn-light border text-dark me-1" title="Ubah Data">Edit</a>
                                    
                                    <form action="{{ route('admin.kurikulum.destroy', $kurikulum->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Menghapus kurikulum ini berpotensi memengaruhi relasi mata kuliah yang bernaung di bawahnya. Lanjutkan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger rounded-1" title="Hapus Permanen">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum terdapat data kurikulum akademik yang didaftarkan ke dalam sistem.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection