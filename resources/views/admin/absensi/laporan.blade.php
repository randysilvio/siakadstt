@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Laporan Absensi Pegawai</h2>
            <p class="text-muted mb-0">Rekapitulasi kehadiran pegawai & dosen.</p>
        </div>
        {{-- TOMBOL CETAK DIPERBARUI --}}
        {{-- Menggunakan route() dan request()->query() agar filter ikut tercetak --}}
        <a href="{{ route('admin.absensi.laporan.cetak', request()->query()) }}" target="_blank" class="btn btn-outline-primary shadow-sm">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </a>
    </div>

    {{-- Filter Data --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded">
            <form method="GET" action="{{ route('admin.absensi.laporan.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="search" class="form-label fw-bold text-secondary small text-uppercase">Cari Nama Pegawai</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control border-start-0 ps-0" value="{{ request('search') }}" placeholder="Ketik nama...">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label for="tanggal" class="form-label fw-bold text-secondary small text-uppercase">Filter Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-calendar-date"></i></span>
                            <input type="date" id="tanggal" name="tanggal" class="form-control border-start-0 ps-0" value="{{ request('tanggal') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i> Filter</button>
                            <a href="{{ route('admin.absensi.laporan.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Laporan --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 text-center" style="width: 50px;">No</th>
                            <th>Nama Pegawai</th>
                            <th>Tanggal</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporan as $item)
                            <tr>
                                <td class="ps-4 text-center fw-bold text-secondary">{{ $loop->iteration + $laporan->firstItem() - 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $item->user->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>{{ $item->tanggal_absensi->translatedFormat('d F Y') }}</td>
                                <td>
                                    @if ($item->waktu_check_in)
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold me-2">{{ $item->waktu_check_in->format('H:i') }}</span>
                                            <a href="{{ asset('storage/' . $item->foto_check_in) }}" target="_blank" class="btn btn-sm btn-light border" title="Lihat Foto">
                                                <i class="bi bi-image text-primary"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                     @if ($item->waktu_check_out)
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold me-2">{{ $item->waktu_check_out->format('H:i') }}</span>
                                            <a href="{{ asset('storage/' . $item->foto_check_out) }}" target="_blank" class="btn btn-sm btn-light border" title="Lihat Foto">
                                                <i class="bi bi-image text-primary"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->status_kehadiran == 'Hadir')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 border border-success">Hadir</span>
                                    @elseif($item->status_kehadiran == 'Izin')
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 border border-warning">Izin</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 border border-danger">Alpha</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    Tidak ada data absensi ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($laporan->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $laporan->links() }}
            </div>
        @endif
    </div>
</div>
@endsection