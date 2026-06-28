@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- 1. BAGIAN HEADER UTAMA --}}
    <div class="card border-0 rounded-0 bg-dark text-white p-4 mb-4 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="small font-monospace text-muted uppercase">Executive Information System</span>
                <h3 class="fw-bold uppercase mb-1 mt-1 text-white">Selamat Datang, Pimpinan</h3>
                <p class="small mb-0 text-light opacity-75">
                    Laporan strategis performa akademik dan status keuangan institusi (Real-Time).
                </p>
            </div>
            <div>
                <a href="{{ route('rektorat.cetak') }}" target="_blank" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Laporan Eksekutif
                </a>
            </div>
        </div>
    </div>

    {{-- 2. KOTAK KPI METRIK UTAMA --}}
    <div class="row g-4 mb-4">
        {{-- Total Mahasiswa Aktif --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Mahasiswa Aktif</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($totalMahasiswaAktif) }}</h2>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 11px;">Populasi Kampus</span>
                </div>
            </div>
        </div>

        {{-- Lulusan --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Lulusan Tahun Ini</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($mahasiswaLulusTahunIni) }}</h2>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 11px;">Alumni Baru</span>
                </div>
            </div>
        </div>

        {{-- Uang Masuk (Lunas) --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-success border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Penerimaan Dana (Lunas)</h6>
                    <h3 class="fw-bold text-success mb-1 font-monospace">Rp {{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</h3>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 11px;">Semester Aktif</span>
                </div>
            </div>
        </div>

        {{-- Piutang (Belum Lunas) --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-danger border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Piutang / Tunggakan</h6>
                    <h3 class="fw-bold text-danger mb-1 font-monospace">Rp {{ number_format($piutangSemesterIni, 0, ',', '.') }}</h3>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 11px;">Belum Terserap</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI DATA (GRAFIK) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Analisis Tren Akademik & Finansial
                </div>
                <div class="card-body p-4">
                    <ul class="nav nav-tabs mb-4 rounded-0" id="grafikTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-0 fw-bold px-4 small uppercase text-dark" id="sumber-tab" data-bs-toggle="tab" data-bs-target="#sumber" type="button" role="tab">
                                Sumber Pendapatan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" id="keuangan-tab" data-bs-toggle="tab" data-bs-target="#keuangan" type="button" role="tab">
                                Tren 5 Tahun Keuangan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" id="pertumbuhan-tab" data-bs-toggle="tab" data-bs-target="#pertumbuhan" type="button" role="tab">
                                Demografi Mahasiswa
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="grafikTabContent">
                        {{-- Tab Doughnut Rincian Finansial --}}
                        <div class="tab-pane fade show active" id="sumber" role="tabpanel">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div style="height: 350px; display: flex; justify-content: center;">
                                        <canvas id="grafikSumberPendapatan"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <h6 class="fw-bold uppercase small border-bottom pb-2">Rincian Dana Masuk</h6>
                                    <ul class="list-group list-group-flush mt-3 font-monospace small">
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

                        {{-- Tab Bar Finansial --}}
                        <div class="tab-pane fade" id="keuangan" role="tabpanel">
                            <div style="height: 350px;">
                                <canvas id="grafikKeuangan"></canvas>
                            </div>
                        </div>

                        {{-- Tab Line Demografi --}}
                        <div class="tab-pane fade" id="pertumbuhan" role="tabpanel">
                             <div style="height: 350px;">
                                <canvas id="grafikPertumbuhan"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. TABEL KINERJA PRODI & RASIO DOSEN MAHASISWA --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Performa Program Studi & Pemantauan Rasio Ideal
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th class="text-start" style="width: 30%;">PROGRAM STUDI</th>
                            <th class="text-start" style="width: 25%;">KEPALA PRODI</th>
                            <th style="width: 10%;">MAHASISWA</th>
                            <th style="width: 10%;">DOSEN</th>
                            <th style="width: 25%;">RASIO DOSEN : MAHASISWA</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($kinerjaProdi as $prodi)
                            @php
                                // PERBAIKAN LOGIKA RASIO (Dosen berbanding Mahasiswa)
                                // Standar umum ideal adalah 1:30 atau 1:45 (Tergantung prodi Eksak/Sosial)
                                if ($prodi->jumlah_dosen > 0) {
                                    $rasioSatuBanding = round($prodi->jumlah_mahasiswa_aktif / $prodi->jumlah_dosen);
                                    $teksRasio = "1 : " . $rasioSatuBanding;
                                    
                                    // Hitung persen visual (Asumsi maksimal bar di rasio 1:50)
                                    $persenVisual = min(($rasioSatuBanding / 50) * 100, 100);
                                    
                                    // Pewarnaan Cerdas: Hijau (Ideal < 30), Kuning (Waspada 30-45), Merah (Overload > 45)
                                    $warnaBar = $rasioSatuBanding > 45 ? 'bg-danger' : ($rasioSatuBanding > 30 ? 'bg-warning' : 'bg-success');
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
                                <td class="text-start uppercase text-muted">
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
                                        <div class="progress flex-grow-1 me-3 rounded-0" style="height: 8px; background-color: #e9ecef;">
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
    
    // Data Doughnut Rincian Pendapatan
    const rincianLabels = @json($rincianPendapatan->pluck('jenis_pembayaran')->map(function($item) { return strtoupper(str_replace('_', ' ', $item)); }));
    const rincianData = @json($rincianPendapatan->pluck('total_masuk'));

    Chart.defaults.font.family = "'font-monospace', monospace";
    
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: 'bold' } } }
        }
    };

    // 1. Grafik Doughnut (Sumber Pendapatan)
    if(document.getElementById('grafikSumberPendapatan') && rincianData.length > 0) {
        new Chart(document.getElementById('grafikSumberPendapatan').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: rincianLabels,
                datasets: [{
                    data: rincianData,
                    backgroundColor: ['#212529', '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6c757d'],
                    borderWidth: 1,
                    borderColor: '#ffffff',
                    borderRadius: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'left' }
                },
                cutout: '60%'
            }
        });
    }

    // 2. Grafik Bar (Tren 5 Tahun Finansial)
    new Chart(document.getElementById('grafikKeuangan').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'TOTAL PENDAPATAN (RP)',
                data: keuanganData,
                backgroundColor: '#212529',
                borderColor: '#000000',
                borderWidth: 1,
                borderRadius: 0
            }]
        },
        options: {
            ...commonOptions,
            scales: { 
                y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + (value/1000000).toFixed(0) + 'Jt' } } 
            }
        }
    });

    // 3. Grafik Line (Pertumbuhan Mahasiswa)
    new Chart(document.getElementById('grafikPertumbuhan').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'MAHASISWA BARU',
                    data: mahasiswaBaruData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.05)',
                    fill: true,
                    tension: 0
                },
                {
                    label: 'LULUSAN',
                    data: lulusanData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.05)',
                    fill: true,
                    tension: 0
                }
            ]
        },
        options: commonOptions
    });
});
</script>
@endpush