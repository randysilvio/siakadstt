@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Titik Lokasi</h3>
            <span class="text-muted small uppercase">Pengaturan Koordinat Geografis untuk Absensi Mobile Terintegrasi</span>
        </div>
        <div>
            <a href="{{ route('admin.absensi.lokasi.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Lokasi Baru
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Titik Koordinat Lokasi Kerja
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 7%;">NO</th>
                            <th class="text-start" style="width: 33%;">NAMA LOKASI</th>
                            <th style="width: 30%;">KOORDINAT (LATITUDE, LONGITUDE)</th>
                            <th style="width: 15%;">RADIUS TOLERANSI</th>
                            <th style="width: 15%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($lokasi as $item)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $loop->iteration }}</td>
                                <td class="text-start ps-3 fw-bold text-dark uppercase">
                                    <i class="bi bi-pin-map-fill text-dark me-2"></i>{{ $item->nama_lokasi }}
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    {{ $item->latitude }}, {{ $item->longitude }}
                                </td>
                                <td class="text-center font-monospace fw-bold">
                                    <span class="badge bg-dark text-white rounded-0 px-2 py-1">{{ $item->radius_toleransi_meter }} METER</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('admin.absensi.lokasi.edit', $item) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.absensi.lokasi.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus titik lokasi ini secara permanen?');">
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
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-geo-alt fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada pengaturan titik lokasi kerja.
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