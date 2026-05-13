@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Hasil Evaluasi Dosen oleh Mahasiswa (EDOM)</h3>
            <span class="text-muted small">Rekapitulasi penilaian kinerja staf pengajar berbasis Indeks Kinerja Dosen</span>
        </div>
    </div>

    {{-- Filter Sesi Formal --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.evaluasi-hasil.index') }}" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-10">
                        <label class="form-label text-muted small fw-bold mb-1">PERIODE EVALUASI AKADEMIK</label>
                        <select name="sesi_id" id="sesi_id" class="form-select rounded-1">
                            <option value="">-- Pilih Sesi Evaluasi --</option>
                            @foreach ($sesiList as $s)
                                <option value="{{ $s->id }}" {{ $sesiTerpilih && $sesiTerpilih->id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama_sesi }} (Tahun Akademik: {{ $s->tahunAkademik->tahun ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mt-4 text-end d-grid">
                        <button type="submit" class="btn btn-dark rounded-1">Tampilkan Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($sesiTerpilih)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">Data Evaluasi: {{ $sesiTerpilih->nama_sesi }}</h6>
                <span class="badge bg-light text-secondary border rounded-1">Standar Skala: 1.00 - 4.00</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="text-center text-muted small" style="width: 60px;">NO</th>
                                <th class="text-muted small">NAMA DOSEN PENGAMPU</th>
                                <th class="text-muted small">NIDN</th>
                                <th class="text-center text-muted small">PARTISIPASI (MHS)</th>
                                <th class="text-center text-muted small">NILAI RATA-RATA</th>
                                <th class="text-center text-muted small">PREDIKAT KINERJA</th>
                                <th class="text-end text-muted small pe-4">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasilEvaluasi as $index => $h)
                                <tr>
                                    <td class="text-center text-muted">{{ $hasilEvaluasi->firstItem() + $index }}</td>
                                    <td class="fw-bold text-dark">
                                        {{ $h->nama_dosen }}
                                    </td>
                                    <td class="text-muted font-monospace small">{{ $h->nidn }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $h->jumlah_responden }} Responden</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $nilai = $h->nilai_rata_rata;
                                            $badgeClass = '';
                                            $kategori = '';

                                            // Logika Predikat (Standard)
                                            if ($nilai >= 3.5) {
                                                $badgeClass = 'bg-success';
                                                $kategori = 'SANGAT BAIK';
                                            } elseif ($nilai >= 2.5) {
                                                $badgeClass = 'bg-primary';
                                                $kategori = 'BAIK';
                                            } elseif ($nilai >= 1.5) {
                                                $badgeClass = 'bg-warning text-dark';
                                                $kategori = 'CUKUP';
                                            } else {
                                                $badgeClass = 'bg-danger';
                                                $kategori = 'KURANG';
                                            }
                                        @endphp
                                        <span class="fw-bold fs-6">{{ number_format($nilai, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeClass }} rounded-1 px-2 py-1">{{ $kategori }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.evaluasi-hasil.show', ['sesi' => $sesiTerpilih->id, 'dosen' => $h->dosen_id]) }}" class="btn btn-sm btn-light border text-dark">
                                            Buka Rincian
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum terdapat data evaluasi (kuesioner) yang diselesaikan pada sesi ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($hasilEvaluasi->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $hasilEvaluasi->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="alert bg-light border text-dark rounded-1 d-flex align-items-center">
            <i class="bi bi-info-circle text-primary me-3 fs-4"></i>
            <div>
                Silakan pilih <strong>Periode Evaluasi Akademik</strong> melalui menu *dropdown* di atas untuk memuat laporan penilaian.
            </div>
        </div>
    @endif
</div>
@endsection