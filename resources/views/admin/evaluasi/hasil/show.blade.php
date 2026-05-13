@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-3">
        <a href="{{ route('admin.evaluasi-hasil.index', ['sesi_id' => $sesi->id]) }}" class="btn btn-outline-dark btn-sm rounded-1">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekapitulasi
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4 bg-dark text-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-light text-dark mb-2 rounded-1">Profil Subjek Evaluasi</span>
                    <h3 class="fw-bold mb-1">{{ $dosen->nama_lengkap }}</h3>
                    <p class="mb-0 text-white-50">NIDN: {{ $dosen->nidn }} | Periode: {{ $sesi->nama_sesi }}</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block bg-white text-dark p-3 rounded-2 text-center" style="min-width: 150px;">
                        <span class="d-block text-muted small fw-bold mb-1">INDEKS KINERJA</span>
                        <span class="fs-2 fw-bold {{ $totalRataRata >= 3.5 ? 'text-success' : ($totalRataRata >= 2.5 ? 'text-primary' : 'text-danger') }}">
                            {{ number_format($totalRataRata, 2) }}
                        </span>
                        <span class="text-muted">/ 4.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Kolom Indikator Penilaian --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Rincian Indikator Penilaian</h6>
                    <a href="{{ route('admin.evaluasi-hasil.cetak', ['sesi' => $sesi->id, 'dosen' => $dosen->id]) }}" target="_blank" class="btn btn-primary btn-sm rounded-1">
                        <i class="bi bi-printer me-1"></i> Cetak Laporan Fisik
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center text-muted small" style="width: 50px;">NO</th>
                                <th class="text-muted small">DESKRIPSI INDIKATOR</th>
                                <th class="text-center text-muted small" style="width: 150px;">NILAI (1-4)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detailPerPertanyaan as $item)
                                <tr>
                                    <td class="text-center text-muted small font-monospace">{{ $item->urutan }}</td>
                                    <td class="text-dark">{{ $item->pertanyaan }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="fw-bold me-2">{{ number_format($item->skor_rata_rata, 2) }}</span>
                                            @php 
                                                $persen = ($item->skor_rata_rata / 4) * 100; 
                                                $color = $item->skor_rata_rata >= 3 ? 'bg-success' : ($item->skor_rata_rata >= 2 ? 'bg-primary' : 'bg-danger');
                                            @endphp
                                            <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                                <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ $persen }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kolom Umpan Balik --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Umpan Balik Kualitatif (Mahasiswa)</h6>
                </div>
                <div class="card-body bg-light overflow-auto p-3" style="max-height: 600px;">
                    @forelse ($masukanTeks as $masukan)
                        <div class="card border border-light shadow-sm mb-2 rounded-1">
                            <div class="card-body p-3">
                                <p class="mb-2 text-dark" style="font-size: 0.95rem;">"{{ $masukan->jawaban_teks }}"</p>
                                <div class="text-end text-muted font-monospace" style="font-size: 0.75rem;">
                                    Terekam: {{ $masukan->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            Tidak terdapat catatan umpan balik kualitatif untuk dosen ini pada sesi berjalan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection