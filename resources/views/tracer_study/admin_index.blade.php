@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER UTAMA --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Rekapitulasi Tracer Study Alumni</h3>
            <span class="text-muted small uppercase">Analisis Masa Tunggu Pelayanan/Kerja & Kesesuaian Bidang Ilmu Lulusan</span>
        </div>
        <div>
            <a href="{{ route('admin.tracer.cetak', request()->query()) }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak Laporan Tracer Study (PDF)
            </a>
        </div>
    </div>

    {{-- METRIK RINGKASAN TRACER STUDY --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">TOTAL RESPONDEN ALUMNI</span>
                    <h3 class="fw-bold font-monospace text-dark mb-0 mt-1">{{ $totalResponden }} <small class="fs-6">ALUMNI</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">RATA-RATA WAKTU TUNGGU</span>
                    <h3 class="fw-bold font-monospace text-primary mb-0 mt-1">{{ $rataWaktuTunggu }} <small class="fs-6">BULAN</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">KESESUAIAN BIDANG ILMU</span>
                    <h3 class="fw-bold font-monospace text-success mb-0 mt-1">{{ $persenSesuai }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">SERAPAN LULUSAN AKTIF</span>
                    <h3 class="fw-bold font-monospace text-dark mb-0 mt-1">{{ $persenBekerja }}%</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.tracer.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold uppercase text-dark mb-1">Filter Program Studi</label>
                        <select name="program_studi_id" class="form-select rounded-0 uppercase small fw-bold">
                            <option value="">-- SEMUA PROGRAM STUDI --</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark mb-1">Tahun Lulus</label>
                        <select name="tahun_lulus" class="form-select rounded-0 font-monospace fw-bold">
                            <option value="">-- SEMUA TAHUN LULUS --</option>
                            @foreach($tahunLulusList as $thn)
                                <option value="{{ $thn }}" {{ request('tahun_lulus') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2 shadow-sm">Filter</button>
                        <a href="{{ route('admin.tracer.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA TRACER STUDY --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Responden Tracer Study Lulusan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th class="text-start" style="width: 22%;">NAMA ALUMNI & PRODI</th>
                            <th style="width: 10%;">THN LULUS</th>
                            <th style="width: 16%;">STATUS KARIR</th>
                            <th style="width: 22%;">TEMPAT PELAYANAN / INSTANSI</th>
                            <th style="width: 12%;">WAKTU TUNGGU</th>
                            <th style="width: 13%;">KESESUAIAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tracers as $index => $item)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $tracers->firstItem() + $index }}</td>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark uppercase">{{ $item->mahasiswa->nama_lengkap ?? '-' }}</div>
                                    <span class="text-muted font-monospace" style="font-size: 10px;">NIM: {{ $item->mahasiswa->nim ?? '-' }} | {{ optional(optional($item->mahasiswa)->programStudi)->nama_prodi ?? '-' }}</span>
                                </td>
                                <td class="text-center font-monospace fw-bold">{{ $item->tahun_lulus }}</td>
                                <td class="text-center uppercase fw-bold">{{ $item->status_pekerjaan }}</td>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark uppercase">{{ $item->nama_instansi_gereja ?? '-' }}</div>
                                    <span class="text-muted font-monospace" style="font-size: 10px;">JABATAN: {{ $item->jabatan_pekerjaan ?? '-' }}</span>
                                </td>
                                <td class="text-center font-monospace fw-bold text-primary">{{ $item->waktu_tunggu_bulan }} BULAN</td>
                                <td class="text-center">
                                    <span class="badge {{ in_array($item->kesesuaian_bidang, ['Sangat Sesuai', 'Sesuai']) ? 'bg-success' : 'bg-warning text-dark' }} rounded-0 font-monospace uppercase">
                                        {{ strtoupper($item->kesesuaian_bidang) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data responden Tracer Study yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tracers->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $tracers->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection