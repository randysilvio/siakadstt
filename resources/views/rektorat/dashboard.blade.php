@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- 1. WELCOME BANNER (Gaya Premium) --}}
    <div class="card border-0 shadow-lg mb-5 overflow-hidden" style="background: linear-gradient(120deg, #1e3c72 0%, #2a5298 100%);">
        <div class="card-body p-5 position-relative text-white">
            <div class="row align-items-center position-relative z-1">
                <div class="col-lg-8">
                    <h5 class="text-uppercase letter-spacing-2 mb-2 opacity-75">Executive Information System</h5>
                    <h1 class="display-5 fw-bold mb-3">Selamat Datang, Pimpinan</h1>
                    <p class="lead mb-4 opacity-90" style="max-width: 600px;">
                        Berikut adalah laporan strategis (helicopter view) mengenai performa akademik dan keuangan institusi secara <em>real-time</em>.
                    </p>
                    <button onclick="window.print()" class="btn btn-light text-primary fw-bold shadow-sm">
                        <i class="bi bi-printer-fill me-2"></i> Cetak Laporan Eksekutif
                    </button>
                </div>
                <div class="col-lg-4 text-end d-none d-lg-block">
                    <i class="bi bi-buildings-fill" style="font-size: 8rem; opacity: 0.2;"></i>
                </div>
            </div>
            {{-- Dekorasi Background --}}
            <div class="position-absolute bottom-0 end-0 bg-white opacity-10 rounded-circle" style="width: 300px; height: 300px; transform: translate(30%, 30%);"></div>
        </div>
    </div>

    {{-- 2. KEY PERFORMANCE INDICATORS (KPI) --}}
    <div class="row g-4 mb-5">
        {{-- Total Mahasiswa --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-top transition-all">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-0">Total Mahasiswa Aktif</h6>
                    </div>
                    <h2 class="fw-bold text-dark mb-1">{{ $totalMahasiswaAktif }}</h2>
                    <small class="text-success"><i class="bi bi-graph-up-arrow me-1"></i> Data Real-time</small>
                </div>
                <div class="card-footer bg-white border-0 py-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pendaftar Baru --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-top transition-all">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                            <i class="bi bi-person-plus-fill fs-4"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-0">Pendaftar Tahun Ini</h6>
                    </div>
                    <h2 class="fw-bold text-dark mb-1">{{ $pendaftarTahunIni }}</h2>
                    <small class="text-muted">Calon Mahasiswa</small>
                </div>
                <div class="card-footer bg-white border-0 py-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pendapatan --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-top transition-all">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-0">Pendapatan Semester</h6>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Rp{{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</h3>
                    <small class="text-muted">Total Pembayaran Masuk</small>
                </div>
                <div class="card-footer bg-white border-0 py-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lulusan --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-top transition-all">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                            <i class="bi bi-mortarboard-fill fs-4"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-0">Lulusan Tahun Ini</h6>
                    </div>
                    <h2 class="fw-bold text-dark mb-1">{{ $mahasiswaLulusTahunIni }}</h2>
                    <small class="text-muted">Alumni Baru</small>
                </div>
                <div class="card-footer bg-white border-0 py-2">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 40%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI DATA (Full Width) --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-graph-up me-2"></i>Analisis Tren Strategis (5 Tahun Terakhir)</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" id="grafikTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-4" id="keuangan-tab" data-bs-toggle="tab" data-bs-target="#keuangan" type="button" role="tab">
                                <i class="bi bi-cash-stack me-2"></i>Tren Keuangan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-4" id="pertumbuhan-tab" data-bs-toggle="tab" data-bs-target="#pertumbuhan" type="button" role="tab">
                                <i class="bi bi-people-fill me-2"></i>Pertumbuhan Mahasiswa
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content p-2" id="grafikTabContent">
                        {{-- Tab Keuangan --}}
                        <div class="tab-pane fade show active" id="keuangan" role="tabpanel">
                            <div style="height: 350px;">
                                <canvas id="grafikKeuangan"></canvas>
                            </div>
                        </div>
                        {{-- Tab Pertumbuhan --}}
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

    {{-- 4. TABEL KINERJA PRODI (Ditingkatkan) --}}
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-trophy-fill me-2"></i>Performa Program Studi</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4">Program Studi</th>
                            <th>Kepala Prodi</th>
                            <th>Rasio Mahasiswa</th>
                            <th class="text-center">Jml Mahasiswa</th>
                            <th class="text-center">Jml Dosen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kinerjaProdi as $prodi)
                            @php
                                // Menghitung persentase visual sederhana
                                $percent = ($totalMahasiswaAktif > 0) ? ($prodi->jumlah_mahasiswa_aktif / $totalMahasiswaAktif) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                            <i class="bi bi-mortarboard"></i>
                                        </div>
                                        <strong>{{ $prodi->nama_prodi }}</strong>
                                    </div>
                                </td>
                                <td>{{ $prodi->kaprodi->nama_lengkap ?? 'Belum Ditentukan' }}</td>
                                <td style="width: 30%;">
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="small text-muted">{{ round($percent, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="text-center fw-bold">{{ $prodi->jumlah_mahasiswa_aktif }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $prodi->jumlah_dosen }} Dosen</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4">Tidak ada data program studi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
    .hover-top:hover { transform: translateY(-5px); transition: all 0.3s ease; }
    .transition-all { transition: all 0.3s ease; }
    .letter-spacing-2 { letter-spacing: 2px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($grafikLabels);
    const keuanganData = @json($grafikKeuanganData);
    const mahasiswaBaruData = @json($grafikMahasiswaBaruData);
    const lulusanData = @json($grafikLulusanData);

    // Config Umum Chart
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    };

    // Grafik Keuangan (Bar)
    new Chart(document.getElementById('grafikKeuangan').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: keuanganData,
                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            ...commonOptions,
            scales: { 
                y: { 
                    beginAtZero: true, 
                    ticks: { callback: value => 'Rp ' + (value/1000000).toFixed(0) + 'jt' } 
                } 
            }
        }
    });

    // Grafik Pertumbuhan (Line)
    new Chart(document.getElementById('grafikPertumbuhan').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Mahasiswa Baru',
                    data: mahasiswaBaruData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Lulusan',
                    data: lulusanData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: commonOptions
    });
});
</script>
@endpush