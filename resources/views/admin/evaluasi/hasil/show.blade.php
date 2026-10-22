@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Rincian Evaluasi Kinerja Dosen</h3>
            <span class="text-muted small uppercase">Analisis Skor per Indikator & Umpan Balik Mahasiswa</span>
        </div>
        <div>
            <a href="{{ route('admin.evaluasi-hasil.index', ['sesi_id' => $sesi->id]) }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekapitulasi
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-dark text-white border-top border-secondary border-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-light text-dark mb-2 rounded-0 font-monospace uppercase">PROFIL SUBJEK EVALUASI</span>
                    <h3 class="fw-bold mb-1 uppercase">{{ $dosen->nama_lengkap }}</h3>
                    <p class="mb-0 text-white-50 font-monospace small uppercase">NIDN: {{ $dosen->nidn ?? '-' }} | PERIODE: {{ $sesi->nama_sesi }}</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block bg-white text-dark p-3 rounded-0 text-center border border-dark" style="min-width: 160px;">
                        <span class="d-block text-muted small font-monospace fw-bold mb-1">INDEKS KINERJA</span>
                        <span class="fs-2 fw-bold font-monospace {{ $totalRataRata >= 3.5 ? 'text-success' : ($totalRataRata >= 2.5 ? 'text-primary' : 'text-danger') }}">
                            {{ number_format($totalRataRata, 2) }}
                        </span>
                        <span class="text-muted font-monospace">/ 4.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        {{-- Kolom Indikator Penilaian --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark uppercase small mb-0">Rincian Indikator Penilaian</h6>
                    <a href="{{ route('admin.evaluasi-hasil.cetak', ['sesi' => $sesi->id, 'dosen' => $dosen->id]) }}" target="_blank" class="btn btn-dark btn-sm rounded-0 uppercase fw-bold small px-3">
                        <i class="bi bi-printer-fill me-1"></i> Cetak PDF
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-light text-dark uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 10%;">NO</th>
                                <th class="text-start">DESKRIPSI INDIKATOR</th>
                                <th style="width: 24%;">SKOR (1-4)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detailPerPertanyaan as $item)
                                <tr>
                                    <td class="text-center text-muted font-monospace fw-bold">{{ $item->urutan }}</td>
                                    <td class="text-dark ps-3">{{ $item->pertanyaan }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center px-2">
                                            <span class="fw-bold font-monospace me-2">{{ number_format($item->skor_rata_rata, 2) }}</span>
                                            @php 
                                                $persen = ($item->skor_rata_rata / 4) * 100; 
                                                $color = $item->skor_rata_rata >= 3 ? 'bg-success' : ($item->skor_rata_rata >= 2 ? 'bg-primary' : 'bg-danger');
                                            @endphp
                                            <div class="progress rounded-0 flex-grow-1" style="height: 6px; max-width: 60px;">
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
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold uppercase small mb-0">Umpan Balik Kualitatif Mahasiswa</h6>
                </div>
                <div class="card-body bg-light overflow-auto p-3" style="max-height: 600px;">
                    @forelse ($masukanTeks as $masukan)
                        <div class="card border border-dark border-opacity-25 shadow-none mb-2 rounded-0 bg-white">
                            <div class="card-body p-3">
                                <p class="mb-2 text-dark small">"{{ $masukan->jawaban_teks }}"</p>
                                <div class="text-end text-muted font-monospace" style="font-size: 0.7rem;">
                                    TEREKAM: {{ $masukan->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted small uppercase fw-bold">
                            Tidak terdapat catatan umpan balik kualitatif untuk dosen ini pada sesi berjalan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection