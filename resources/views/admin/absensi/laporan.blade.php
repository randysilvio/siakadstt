@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Rekapitulasi Kehadiran Pegawai</h3>
            <span class="text-muted small">Laporan periode absensi harian staf dan dosen</span>
        </div>
        <a href="{{ route('admin.absensi.laporan.cetak', request()->query()) }}" target="_blank" class="btn btn-primary">
            <i class="bi bi-printer me-2"></i>Cetak Laporan
        </a>
    </div>

    {{-- Filter Bar Formal --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.absensi.laporan.index') }}">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold mb-1">PENCARIAN PEGAWAI</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Ketik nama pegawai atau dosen...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">BULAN</label>
                        <select name="bulan" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ sprintf('%02d', $i) }}" {{ request('bulan', date('m')) == sprintf('%02d', $i) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold mb-1">TAHUN</label>
                        <select name="tahun" class="form-select">
                            @for($i = date('Y'); $i >= date('Y')-2; $i--)
                                <option value="{{ $i }}" {{ request('tahun', date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 mt-4 text-end">
                        <button type="submit" class="btn btn-dark px-4">Terapkan</button>
                        <a href="{{ route('admin.absensi.laporan.index') }}" class="btn btn-light border">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="text-center text-muted small" style="width: 60px;">NO</th>
                            <th class="text-muted small">NAMA PEGAWAI / DOSEN</th>
                            <th class="text-muted small">TANGGAL</th>
                            <th class="text-muted small">WAKTU MASUK</th>
                            <th class="text-muted small">WAKTU KELUAR</th>
                            <th class="text-center text-muted small">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporan as $item)
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration + $laporan->firstItem() - 1 }}</td>
                                <td class="fw-bold text-dark">{{ $item->user->name ?? 'N/A' }}</td>
                                <td>{{ $item->tanggal_absensi->translatedFormat('d M Y') }}</td>
                                <td>
                                    @if ($item->waktu_check_in)
                                        <span class="text-dark">{{ $item->waktu_check_in->format('H:i') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                     @if ($item->waktu_check_out)
                                        <span class="text-dark">{{ $item->waktu_check_out->format('H:i') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->status_kehadiran == 'Hadir')
                                        <span class="badge bg-success rounded-1 px-2 py-1">HADIR</span>
                                    @elseif($item->status_kehadiran == 'Izin')
                                        <span class="badge bg-warning text-dark rounded-1 px-2 py-1">IZIN</span>
                                    @else
                                        <span class="badge bg-danger rounded-1 px-2 py-1">ALPHA</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Data absensi tidak ditemukan untuk periode yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($laporan->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $laporan->links() }}
            </div>
        @endif
    </div>
</div>
@endsection