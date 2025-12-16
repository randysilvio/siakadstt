@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Manajemen Lokasi Kerja</h2>
            <p class="text-muted mb-0">Atur titik koordinat untuk absensi mobile.</p>
        </div>
        <a href="{{ route('admin.absensi.lokasi.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-geo-alt-fill me-1"></i> Tambah Lokasi
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 text-center" style="width: 50px;">No</th>
                            <th>Nama Lokasi</th>
                            <th>Koordinat (Lat, Long)</th>
                            <th class="text-center">Radius Toleransi</th>
                            <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lokasi as $item)
                            <tr>
                                <td class="ps-4 text-center fw-bold text-secondary">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $item->nama_lokasi }}</span>
                                    </div>
                                </td>
                                <td class="small font-monospace text-muted">
                                    {{ $item->latitude }}, {{ $item->longitude }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $item->radius_toleransi_meter }} meter
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.absensi.lokasi.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.absensi.lokasi.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-geo-alt fs-1 d-block mb-2 opacity-50"></i>
                                    Belum ada lokasi kerja yang ditambahkan.
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