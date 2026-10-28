@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    
    {{-- 1. HEADER UTAMA --}}
    <div class="card border-0 bg-dark text-white p-3 p-md-4 mb-4 mt-2 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="small font-monospace text-white-50 uppercase">Dashboard Pimpinan Institusi</span>
                <h3 class="fw-bold uppercase mb-1 mt-1 text-white">Ringkasan Eksekutif Akademik & Keuangan</h3>
                <p class="small mb-0 text-white-50">
                    Pemantauan performa program studi, pertumbuhan mahasiswa, dan realisasi penerimaan keuangan.
                </p>
            </div>
            <div>
                <a href="{{ route('rektorat.cetak') }}" target="_blank" class="btn btn-sm btn-light text-dark px-4 py-2 uppercase fw-bold small shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Laporan Eksekutif
                </a>
            </div>
        </div>
    </div>

    {{-- 2. KOTAK KPI METRIK UTAMA (LENGKAP DENGAN SUB-INFORMASI) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card wm-card-exclusive hover-lift border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-white-50 mb-2" style="font-size: 11px;">Mahasiswa Aktif</h6>
                    <h2 class="fw-bold text-white mb-1 font-monospace">{{ number_format($totalMahasiswaAktif) }}</h2>
                    <span class="small text-white-50 uppercase fw-bold" style="font-size: 10px;">Populasi Kampus</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2" style="font-size: 11px;">Lulusan Tahun Ini</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($mahasiswaLulusTahunIni) }}</h2>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 10px;">Alumni Baru</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2" style="font-size: 11px;">Penerimaan Dana (Lunas)</h6>
                    <h4 class="fw-bold text-dark mb-1 font-monospace">Rp {{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</h4>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 10px;">Semester Aktif</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2" style="font-size: 11px;">Piutang / Tunggakan <x-info-aturan kunci="keu_piutang" /></h6>
                    <h4 class="fw-bold text-danger mb-1 font-monospace">Rp {{ number_format($piutangSemesterIni, 0, ',', '.') }}</h4>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 10px;">Belum Terserap</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI DATA (3 TAB GRAFIK LENGKAP) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-top border-dark border-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
                    Analisis Tren Akademik & Finansial
                </div>
                <div class="card-body p-3 p-md-4">
                    <ul class="nav nav-tabs mb-4" id="grafikTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-3 px-md-4 small uppercase text-dark" id="sumber-tab" data-bs-toggle="tab" data-bs-target="#sumber" type="button" role="tab">
                                Sumber Pendapatan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 px-md-4 small uppercase text-dark" id="keuangan-tab" data-bs-toggle="tab" data-bs-target="#keuangan" type="button" role="tab">
                                Tren 5 Tahun Keuangan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 px-md-4 small uppercase text-dark" id="pertumbuhan-tab" data-bs-toggle="tab" data-bs-target="#pertumbuhan" type="button" role="tab">
                                Demografi Mahasiswa
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="grafikTabContent">
                        <div class="tab-pane fade show active" id="sumber" role="tabpanel">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div style="height: 320px; display: flex; justify-content: center;">
                                        <canvas id="grafikSumberPendapatan"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-5 mt-4 mt-md-0">
                                    <h6 class="fw-bold uppercase small border-bottom pb-2">Rincian Dana Masuk</h6>
                                    <ul class="list-group list-group-flush mt-2 font-monospace small">
                                        @forelse($rincianPendapatan as $rincian)
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                {{ strtoupper(str_replace('_', ' ', $rincian->jenis_pembayaran)) }}
                                                <span class="fw-bold">Rp {{ number_format($rincian->total_masuk, 0, ',', '.') }}</span>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted uppercase">Belum ada transaksi lunas.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="keuangan" role="tabpanel">
                            <div style="height: 320px;">
                                <canvas id="grafikKeuangan"></canvas>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pertumbuhan" role="tabpanel">
                             <div style="height: 320px;">
                                <canvas id="grafikPertumbuhan"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. TABEL KINERJA PRODI & VISUAL PROGRESS BAR RASIO DOSEN : MAHASISWA --}}
    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-5 bg-white">
        <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
            Performa Program Studi & Pemantauan Rasio Dosen : Mahasiswa
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th class="text-start" style="width: 30%;">PROGRAM STUDI</th>
                            <th class="text-start" style="width: 25%;">KETUA PRODI</th>
                            <th style="width: 10%;">MAHASISWA</th>
                            <th style="width: 10%;">DOSEN TETAP</th>
                            <th style="width: 25%;">RASIO DOSEN : MAHASISWA <x-info-aturan kunci="mutu_rasio" /></th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($kinerjaProdi as $prodi)
                            @php
                                if ($prodi->jumlah_dosen > 0) {
                                    $rasioSatuBanding = round($prodi->jumlah_mahasiswa_aktif / $prodi->jumlah_dosen, 1);
                                    $teksRasio = "1 : " . rtrim(rtrim(number_format($rasioSatuBanding, 1, ',', ''), '0'), ',');
                                    $persenVisual = min(($rasioSatuBanding / 50) * 100, 100);
                                    // Akreditasi rumpun Sosial Humaniora: ideal 1 : 25–35, di atas 1 : 50 skor 0
                                    $warnaBar = $rasioSatuBanding > 50 ? 'bg-danger' : (($rasioSatuBanding >= 25 && $rasioSatuBanding <= 35) ? 'bg-success' : 'bg-warning');
                                } else {
                                    $teksRasio = "N/A (0 DOSEN)";
                                    $persenVisual = 0;
                                    $warnaBar = 'bg-secondary';
                                }
                            @endphp
                            <tr>
                                <td class="text-start uppercase fw-bold text-dark ps-3">
                                    {{ $prodi->nama_prodi }}
                                </td>
                                <td class="text-start uppercase text-muted ps-3">
                                    {{ $prodi->kaprodi->nama_lengkap ?? 'BELUM DITENTUKAN' }}
                                </td>
                                <td class="text-center font-monospace fw-bold text-dark">
                                    {{ $prodi->jumlah_mahasiswa_aktif }}
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    {{ $prodi->jumlah_dosen }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center px-2">
                                        <div class="progress flex-grow-1 me-3" style="height: 8px; background-color: #e9ecef;">
                                            <div class="progress-bar {{ $warnaBar }}" role="progressbar" style="width: {{ $persenVisual }}%"></div>
                                        </div>
                                        <span class="small font-monospace text-dark fw-bold" style="white-space: nowrap;">{{ $teksRasio }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center uppercase fw-bold py-4">Tidak ada data program studi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($grafikLabels);
    const keuanganData = @json($grafikKeuanganData);
    const mahasiswaBaruData = @json($grafikMahasiswaBaruData);
    const lulusanData = @json($grafikLulusanData);
    const rincianLabels = @json($rincianPendapatan->pluck('jenis_pembayaran')->map(function($item) { return strtoupper(str_replace('_', ' ', $item)); }));
    const rincianData = @json($rincianPendapatan->pluck('total_masuk'));

    Chart.defaults.font.family = "'Inter', sans-serif";
    
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: 'bold' } } }
        }
    };

    if (document.getElementById('grafikSumberPendapatan') && rincianData.length > 0) {
        new Chart(document.getElementById('grafikSumberPendapatan').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: rincianLabels,
                datasets: [{
                    data: rincianData,
                    backgroundColor: ['#1e293b', '#475569', '#64748b', '#94a3b8', '#cbd5e1'],
                    borderWidth: 1,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                cutout: '60%'
            }
        });
    }

    new Chart(document.getElementById('grafikKeuangan').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'TOTAL PENDAPATAN (RP)',
                data: keuanganData,
                backgroundColor: '#1e293b',
                borderColor: '#0f172a',
                borderWidth: 1
            }]
        },
        options: {
            ...commonOptions,
            scales: { 
                y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + (value/1000000).toFixed(0) + 'Jt' } } 
            }
        }
    });

    new Chart(document.getElementById('grafikPertumbuhan').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'MAHASISWA BARU',
                    data: mahasiswaBaruData,
                    borderColor: '#1e293b',
                    backgroundColor: 'rgba(30, 41, 59, 0.06)',
                    fill: true,
                    tension: 0.2
                },
                {
                    label: 'LULUSAN',
                    data: lulusanData,
                    borderColor: '#64748b',
                    backgroundColor: 'rgba(100, 116, 139, 0.06)',
                    fill: true,
                    tension: 0.2
                }
            ]
        },
        options: commonOptions
    });
});
</script>
@endpush