@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Rekapitulasi Kehadiran Pegawai</h3>
            <span class="text-muted small uppercase">Monitoring & Laporan Periode Absensi Harian Staf dan Dosen</span>
        </div>
        <div>
            <a href="{{ route('admin.absensi.laporan.cetak', request()->query()) }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-printer-fill me-2"></i>Cetak Rekap Bulanan
            </a>
        </div>
    </div>

    {{-- Filter Bar Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.absensi.laporan.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Pencarian Pegawai / Dosen</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" name="search" class="form-control rounded-0 font-monospace uppercase" value="{{ request('search') }}" placeholder="KETIK NAMA PEGAWAI...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Bulan</label>
                        <select name="bulan" class="form-select rounded-0 uppercase small fw-bold">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ sprintf('%02d', $i) }}" {{ request('bulan', date('m')) == sprintf('%02d', $i) ? 'selected' : '' }}>
                                    {{ strtoupper(\Carbon\Carbon::create()->month($i)->translatedFormat('F')) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Tahun</label>
                        <select name="tahun" class="form-select rounded-0 font-monospace fw-bold">
                            @for($i = date('Y'); $i >= date('Y')-3; $i--)
                                <option value="{{ $i }}" {{ request('tahun', date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2 shadow-sm">
                            Filter
                        </button>
                        <a href="{{ route('admin.absensi.laporan.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small" title="Reset Filter">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Log Kehadiran Harian
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th class="text-start" style="width: 28%;">NAMA PEGAWAI / DOSEN</th>
                            <th style="width: 16%;">TANGGAL</th>
                            <th style="width: 14%;">JAM MASUK</th>
                            <th style="width: 14%;">JAM PULANG</th>
                            <th style="width: 12%;">STATUS</th>
                            <th style="width: 10%;">CATATAN</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($laporan as $item)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $loop->iteration + $laporan->firstItem() - 1 }}</td>
                                <td class="text-start ps-3 fw-bold text-dark uppercase">{{ $item->user->name ?? 'N/A' }}</td>
                                <td class="text-center font-monospace">{{ $item->tanggal_absensi->translatedFormat('d M Y') }}</td>
                                <td class="text-center font-monospace fw-bold">
                                    @if ($item->waktu_check_in)
                                        <span class="text-success">{{ $item->waktu_check_in->format('H:i:s') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace fw-bold">
                                     @if ($item->waktu_check_out)
                                        <span class="text-primary">{{ $item->waktu_check_out->format('H:i:s') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->status_kehadiran == 'Hadir')
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">HADIR</span>
                                    @elseif($item->status_kehadiran == 'Izin')
                                        <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">IZIN</span>
                                    @elseif($item->status_kehadiran == 'Sakit')
                                        <span class="badge bg-info text-dark rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">SAKIT</span>
                                    @else
                                        <span class="badge bg-danger text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">ALPHA</span>
                                    @endif
                                </td>
                                <td class="text-center small text-muted uppercase font-monospace" style="font-size: 10px;">
                                    {{ $item->keterangan ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Data absensi tidak ditemukan untuk periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($laporan->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $laporan->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection