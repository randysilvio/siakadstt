@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Hasil Evaluasi Dosen oleh Mahasiswa (EDOM)</h3>
            <span class="text-muted small uppercase">Rekapitulasi Penilaian Kinerja Staf Pengajar Berbasis Indeks Kinerja Dosen</span>
        </div>
    </div>

    {{-- Filter Sesi Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.evaluasi-hasil.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-10">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Periode Evaluasi Akademik</label>
                        <select name="sesi_id" id="sesi_id" class="form-select rounded-0 uppercase small fw-bold">
                            <option value="">-- PILIH SESI EVALUASI --</option>
                            @foreach ($sesiList as $s)
                                <option value="{{ $s->id }}" {{ $sesiTerpilih && $sesiTerpilih->id == $s->id ? 'selected' : '' }}>
                                    {{ strtoupper($s->nama_sesi) }} (TA: {{ $s->tahunAkademik->tahun ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary rounded-0 uppercase fw-bold small py-2 shadow-sm">
                            Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($sesiTerpilih)
        <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
            <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                <span class="mb-0 fw-bold uppercase small">Data Evaluasi: {{ $sesiTerpilih->nama_sesi }}</span>
                <span class="badge bg-light text-dark rounded-0 font-monospace uppercase">SKALA: 1.00 - 4.00</span> <x-info-aturan kunci="edom" />
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 6%;">NO</th>
                                <th class="text-start" style="width: 30%;">NAMA DOSEN PENGAMPU</th>
                                <th style="width: 14%;">NIDN</th>
                                <th style="width: 14%;">PARTISIPASI</th>
                                <th style="width: 12%;">SKOR RATA-RATA</th>
                                <th style="width: 12%;">PREDIKAT</th>
                                <th style="width: 12%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @forelse ($hasilEvaluasi as $index => $h)
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-muted">{{ $hasilEvaluasi->firstItem() + $index }}</td>
                                    <td class="text-start ps-3 fw-bold text-dark uppercase">{{ $h->nama_dosen }}</td>
                                    <td class="text-center font-monospace text-muted">{{ $h->nidn ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border border-dark border-opacity-25 rounded-0 font-monospace">{{ $h->jumlah_responden }} RESPONDEN</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold fs-6 text-primary">
                                        {{ number_format($h->nilai_rata_rata, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $nilai = $h->nilai_rata_rata;
                                            if ($nilai >= 3.5) { $badgeClass = 'bg-success text-white'; $kategori = 'SANGAT BAIK'; }
                                            elseif ($nilai >= 2.5) { $badgeClass = 'bg-primary text-white'; $kategori = 'BAIK'; }
                                            elseif ($nilai >= 1.5) { $badgeClass = 'bg-warning text-dark'; $kategori = 'CUKUP'; }
                                            else { $badgeClass = 'bg-danger text-white'; $kategori = 'KURANG'; }
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-0 font-monospace px-2 py-1" style="font-size: 10px;">{{ $kategori }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.evaluasi-hasil.show', ['sesi' => $sesiTerpilih->id, 'dosen' => $h->dosen_id]) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-3 uppercase fw-bold small">
                                            Rincian
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 uppercase fw-bold text-muted">
                                        <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum terdapat data evaluasi (kuesioner) yang diselesaikan pada sesi ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($hasilEvaluasi->hasPages())
                <div class="card-footer bg-white border-top py-3 rounded-0">
                    <div class="d-flex justify-content-center">
                        {{ $hasilEvaluasi->links() }}
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="alert bg-light border border-dark border-opacity-25 text-dark rounded-0 d-flex align-items-center p-4">
            <i class="bi bi-info-circle-fill text-primary me-3 fs-4"></i>
            <div class="small uppercase fw-bold">
                Silakan pilih <strong>Periode Evaluasi Akademik</strong> melalui menu pilihan di atas untuk memuat laporan penilaian.
            </div>
        </div>
    @endif
</div>
@endsection