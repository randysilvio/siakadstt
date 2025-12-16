@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Hasil Evaluasi Dosen</h2>
            <p class="text-muted mb-0">Rekapitulasi penilaian kinerja dosen oleh mahasiswa.</p>
        </div>
    </div>

    {{-- Filter Sesi --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded">
            <form action="{{ route('admin.evaluasi-hasil.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="sesi_id" class="form-label fw-bold text-secondary small text-uppercase">Pilih Periode Evaluasi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-calendar-range"></i></span>
                        <select name="sesi_id" id="sesi_id" class="form-select border-start-0 ps-0">
                            @foreach ($sesiList as $s)
                                <option value="{{ $s->id }}" {{ $sesiTerpilih && $sesiTerpilih->id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama_sesi }} ({{ $s->tahunAkademik->tahun ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($sesiTerpilih)
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-bar-chart-line-fill me-2"></i>Rekapitulasi: {{ $sesiTerpilih->nama_sesi }}
                </h6>
                <span class="badge bg-light text-dark border">Skala 1 - 4</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 text-center" style="width: 50px;">No</th>
                                <th>Nama Dosen</th>
                                <th>NIDN</th>
                                <th class="text-center">Responden</th>
                                <th class="text-center">Rata-rata</th>
                                <th class="text-center">Kategori</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasilEvaluasi as $index => $h)
                                <tr>
                                    <td class="ps-4 text-center fw-bold text-secondary">{{ $hasilEvaluasi->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <span class="fw-bold text-dark">{{ $h->nama_dosen }}</span>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $h->nidn }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border rounded-pill px-3">
                                            <i class="bi bi-people me-1"></i> {{ $h->jumlah_responden }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{-- LOGIKA BARU: SKALA 1-4 --}}
                                        @php
                                            $nilai = $h->nilai_rata_rata;
                                            $badgeClass = '';
                                            $kategori = '';

                                            if ($nilai >= 3.5) {
                                                $badgeClass = 'bg-success';
                                                $kategori = 'Sangat Baik';
                                            } elseif ($nilai >= 2.5) {
                                                $badgeClass = 'bg-warning text-dark';
                                                $kategori = 'Baik';
                                            } elseif ($nilai >= 1.5) {
                                                $badgeClass = 'bg-warning bg-opacity-75 text-dark';
                                                $kategori = 'Cukup';
                                            } else {
                                                $badgeClass = 'bg-danger';
                                                $kategori = 'Kurang';
                                            }
                                        @endphp
                                        <h5 class="mb-0 fw-bold">{{ number_format($nilai, 2) }}</h5>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeClass }} bg-opacity-10 border {{ str_replace('bg-', 'border-', $badgeClass) }} {{ strpos($badgeClass, 'text-dark') !== false ? 'text-dark' : 'text-'.str_replace('bg-', '', $badgeClass) }}">
                                            {{ $kategori }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.evaluasi-hasil.show', ['sesi' => $sesiTerpilih->id, 'dosen' => $h->dosen_id]) }}" class="btn btn-sm btn-outline-primary shadow-sm">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="mb-2"><i class="bi bi-clipboard-data fs-1 opacity-50"></i></div>
                                        Belum ada data evaluasi yang masuk untuk sesi ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($hasilEvaluasi->count() > 0)
                <div class="card-footer bg-white border-top">
                    {{ $hasilEvaluasi->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
            <div>
                <strong>Info:</strong> Silakan pilih sesi evaluasi pada filter di atas untuk melihat data.
            </div>
        </div>
    @endif
</div>
@endsection