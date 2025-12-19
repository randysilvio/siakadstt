@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-teal-700">Pengaturan Gelombang PMB</h3>
        <a href="{{ route('admin.pmb-periods.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Gelombang
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Gelombang</th>
                        <th>Tanggal Buka</th>
                        <th>Tanggal Tutup</th>
                        <th>Biaya</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $period)
                        <tr class="{{ $period->is_active ? 'table-success bg-opacity-10' : '' }}">
                            <td class="fw-bold">{{ $period->nama_gelombang }}</td>
                            <td>{{ $period->tanggal_buka->format('d M Y') }}</td>
                            <td>{{ $period->tanggal_tutup->format('d M Y') }}</td>
                            <td>Rp {{ number_format($period->biaya_pendaftaran, 0, ',', '.') }}</td>
                            <td>
                                @if($period->is_active)
                                    <span class="badge bg-success">AKTIF (DIBUKA)</span>
                                @else
                                    <span class="badge bg-secondary">NON-AKTIF</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$period->is_active)
                                    <form action="{{ route('admin.pmb-periods.set-active', $period->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-success" title="Aktifkan">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('admin.pmb-periods.edit', $period->id) }}" class="btn btn-sm btn-warning text-dark">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('admin.pmb-periods.destroy', $period->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus gelombang ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada gelombang pendaftaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection