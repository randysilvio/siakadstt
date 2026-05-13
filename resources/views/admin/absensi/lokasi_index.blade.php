@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Manajemen Titik Lokasi</h3>
            <span class="text-muted small">Pengaturan koordinat geografis untuk absensi mobile terintegrasi</span>
        </div>
        <a href="{{ route('admin.absensi.lokasi.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Lokasi Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="text-center text-muted small" style="width: 60px;">NO</th>
                            <th class="text-muted small">NAMA LOKASI</th>
                            <th class="text-muted small">KOORDINAT (LATITUDE, LONGITUDE)</th>
                            <th class="text-center text-muted small">RADIUS TOLERANSI</th>
                            <th class="text-end text-muted small pe-4" style="width: 150px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lokasi as $item)
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-pin-map text-secondary me-2"></i>{{ $item->nama_lokasi }}
                                </td>
                                <td class="font-monospace text-muted small">
                                    {{ $item->latitude }}, {{ $item->longitude }}
                                </td>
                                <td class="text-center">
                                    {{ $item->radius_toleransi_meter }} Meter
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.absensi.lokasi.edit', $item) }}" class="btn btn-sm btn-light border text-dark me-1">Edit</a>
                                    <form action="{{ route('admin.absensi.lokasi.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus titik lokasi ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada pengaturan titik lokasi kerja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection