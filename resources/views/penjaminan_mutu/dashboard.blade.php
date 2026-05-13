@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- 1. HEADER & STATUS UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dashboard Penjaminan Mutu</h3>
            <span class="text-muted small uppercase">Monitoring Indikator Kinerja Utama & Kesiapan Akreditasi</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-3 py-2 me-2" style="font-size: 11px;">
                <i class="bi bi-check-circle-fill me-1"></i> Sistem Aktif
            </span>
            
            {{-- TOMBOL KE PUSAT LAPORAN --}}
            <a href="{{ route('mutu.laporan.index') }}" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Pusat Laporan Akreditasi
            </a>
        </div>
    </div>

    {{-- 2. KARTU INDIKATOR KINERJA UTAMA (KPI) --}}
    <div class="row g-4 mb-5">
        {{-- Total Mahasiswa --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Total Mahasiswa</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($jumlahMahasiswaAktif) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-person-check-fill me-1"></i> Status Aktif
                    </span>
                </div>
            </div>
        </div>

        {{-- Total Dosen --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Total Dosen Tetap</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($jumlahDosen) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-person-workspace me-1"></i> NIDN Terdaftar
                    </span>
                </div>
            </div>
        </div>

        {{-- Rasio Dosen --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Rasio Dosen : Mhs</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ $rasioDosenMahasiswa }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-graph-up-arrow me-1"></i> Indikator Borang
                    </span>
                </div>
            </div>
        </div>

        {{-- Rata-rata IPK --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Rata-rata IPK</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($rataIPK, 2) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-award-fill me-1"></i> Prestasi Institusi
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI DATA & ANALISIS --}}
    <div class="row g-4 mb-5">
        {{-- Grafik Tren Mahasiswa --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Tren Pertumbuhan Mahasiswa
                </div>
                <div class="card-body p-4">
                    <canvas id="trenMahasiswaChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Grafik Status Mahasiswa --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Distribusi Status
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 300px;">
                        <canvas id="distribusiStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. LAPORAN EVALUASI DOSEN (EDOM) STANDAR ENTERPRISE --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">
                Kinerja Dosen Terbaik (EDOM)
            </span>
            @if($sesiEdomAktif)
                <span class="badge bg-light text-dark rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">
                    Periode: {{ $sesiEdomAktif->nama_sesi }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($sesiEdomAktif && $hasilEdom->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 8%;">RANK</th>
                                <th class="text-start" style="width: 37%;">NAMA DOSEN</th>
                                <th style="width: 20%;">SKOR RATA-RATA</th>
                                <th style="width: 20%;">KATEGORI</th>
                                <th style="width: 15%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @foreach($hasilEdom as $index => $hasil)
                                @php
                                    $skor = $hasil->rata_rata_skor;
                                    $kategori = $skor >= 3.5 ? 'SANGAT BAIK' : ($skor >= 2.5 ? 'BAIK' : 'PERLU EVALUASI');
                                @endphp
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-dark">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="text-start uppercase fw-bold text-dark ps-3">
                                        {{ $hasil->nama_lengkap }}
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-dark">
                                        {{ number_format($skor, 2) }} <span class="text-muted small font-monospace">/ 4.00</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-dark text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            {{ $kategori }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.evaluasi-hasil.show', ['sesi' => $sesiEdomAktif->id, 'dosen' => $hasil->dosen_id]) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-3 uppercase fw-bold small">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 uppercase fw-bold text-muted">
                    <i class="bi bi-trophy fs-2 d-block mb-2 text-secondary opacity-50"></i>
                    Data evaluasi belum tersedia untuk periode aktif ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trenMahasiswaLabels = @json($trenMahasiswaLabels);
    const trenMahasiswaTotals = @json($trenMahasiswaTotals);
    const distribusiStatusLabels = @json($distribusiStatusLabels);
    const distribusiStatusTotals = @json($distribusiStatusTotals);

    // Tetapkan Font Monospace Formal secara global untuk Chart
    Chart.defaults.font.family = "'font-monospace', monospace";

    // Grafik Tren Mahasiswa (Baris Sudut Tajam 0px, Latar Gelap Tegas)
    new Chart(document.getElementById('trenMahasiswaChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: trenMahasiswaLabels,
            datasets: [{
                label: 'JUMLAH MAHASISWA AKTIF',
                data: trenMahasiswaTotals,
                backgroundColor: '#212529', // bg-dark
                borderColor: '#000000',
                borderWidth: 1,
                borderRadius: 0, // Sudut presisi tajam 0px
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { 
                y: { beginAtZero: true, grid: { color: '#e9ecef' } }, 
                x: { grid: { display: false } } 
            },
            plugins: { legend: { display: false } }
        }
    });

    // Grafik Distribusi Status (Kombinasi Warna Solid/Kaku)
    new Chart(document.getElementById('distribusiStatusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: distribusiStatusLabels,
            datasets: [{
                data: distribusiStatusTotals,
                backgroundColor: ['#212529', '#0d6efd', '#dc3545', '#ffc107', '#6c757d'],
                borderWidth: 1,
                borderColor: '#ffffff',
                borderRadius: 0,
                hoverOffset: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom', 
                    labels: { boxWidth: 12, font: { weight: 'bold' } } 
                }
            },
            cutout: '70%'
        }
    });
});
</script>
@endpush