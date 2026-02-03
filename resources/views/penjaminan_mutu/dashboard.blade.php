@extends('layouts.app')

@section('content')
<div class="container">
    {{-- 1. HEADER & STATUS UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1 text-dark">Dashboard Penjaminan Mutu</h2>
            <p class="text-muted mb-0">Monitoring indikator kinerja utama & kesiapan akreditasi.</p>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 border border-success me-2">
                <i class="bi bi-check-circle-fill me-1"></i> Sistem Aktif
            </span>
            
            {{-- TOMBOL KE PUSAT LAPORAN --}}
            <a href="{{ route('mutu.laporan.index') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i>Pusat Laporan Akreditasi
            </a>
        </div>
    </div>

    {{-- 2. KARTU INDIKATOR KINERJA UTAMA (KPI) --}}
    <div class="row g-4 mb-5">
        {{-- Total Mahasiswa --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden bg-primary text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75 fw-bold ls-1 mb-2">Total Mahasiswa</h6>
                    <h2 class="display-5 fw-bold mb-0">{{ number_format($jumlahMahasiswaAktif) }}</h2>
                    <div class="mt-3 small opacity-75">
                        <i class="bi bi-person-check-fill me-1"></i> Status Aktif
                    </div>
                </div>
                <i class="bi bi-mortarboard-fill position-absolute top-0 end-0 p-3 opacity-25" style="font-size: 5rem;"></i>
            </div>
        </div>

        {{-- Total Dosen --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden bg-success text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75 fw-bold ls-1 mb-2">Total Dosen Tetap</h6>
                    <h2 class="display-5 fw-bold mb-0">{{ number_format($jumlahDosen) }}</h2>
                    <div class="mt-3 small opacity-75">
                        <i class="bi bi-person-workspace me-1"></i> NIDN Terdaftar
                    </div>
                </div>
                <i class="bi bi-person-video3 position-absolute top-0 end-0 p-3 opacity-25" style="font-size: 5rem;"></i>
            </div>
        </div>

        {{-- Rasio Dosen --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden bg-info text-white" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75 fw-bold ls-1 mb-2">Rasio Dosen : Mhs</h6>
                    <h2 class="display-5 fw-bold mb-0">{{ $rasioDosenMahasiswa }}</h2>
                    <div class="mt-3 small opacity-75">
                        <i class="bi bi-graph-up-arrow me-1"></i> Indikator Akreditasi
                    </div>
                </div>
                <i class="bi bi-pie-chart-fill position-absolute top-0 end-0 p-3 opacity-25" style="font-size: 5rem;"></i>
            </div>
        </div>

        {{-- Rata-rata IPK (DINAMIS) --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden bg-warning text-dark" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75 fw-bold ls-1 mb-2">Rata-rata IPK</h6>
                    {{-- UPDATE DISINI: Menampilkan variabel $rataIPK --}}
                    <h2 class="display-5 fw-bold mb-0">{{ number_format($rataIPK, 2) }}</h2>
                    <div class="mt-3 small opacity-75">
                        <i class="bi bi-award-fill me-1"></i> Prestasi Akademik
                    </div>
                </div>
                <i class="bi bi-star-fill position-absolute top-0 end-0 p-3 opacity-25" style="font-size: 5rem;"></i>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI DATA & ANALISIS --}}
    <div class="row g-4 mb-5">
        {{-- Grafik Tren Mahasiswa --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-bar-chart-line-fill me-2"></i>Tren Pertumbuhan Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <canvas id="trenMahasiswaChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Grafik Status Mahasiswa --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-pie-chart-fill me-2"></i>Distribusi Status</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 300px;">
                        <canvas id="distribusiStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. LAPORAN EVALUASI DOSEN (EDOM) --}}
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-secondary">
                <i class="bi bi-trophy-fill me-2 text-warning"></i>Kinerja Dosen Terbaik (EDOM)
            </h5>
            @if($sesiEdomAktif)
                <span class="badge bg-light text-dark border"><i class="bi bi-calendar-event me-1"></i> Periode: {{ $sesiEdomAktif->nama_sesi }}</span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($sesiEdomAktif && $hasilEdom->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 text-center" style="width: 80px;">Rank</th>
                                <th>Nama Dosen</th>
                                <th class="text-center">Skor Rata-rata</th>
                                <th class="text-center">Kategori</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hasilEdom as $index => $hasil)
                                @php
                                    $skor = $hasil->rata_rata_skor;
                                    $badgeColor = $skor >= 3.5 ? 'success' : ($skor >= 2.5 ? 'warning' : 'danger');
                                    $kategori = $skor >= 3.5 ? 'Sangat Baik' : ($skor >= 2.5 ? 'Baik' : 'Perlu Evaluasi');
                                @endphp
                                <tr>
                                    <td class="ps-4 text-center">
                                        @if($index == 0)
                                            <i class="bi bi-trophy-fill text-warning fs-5"></i>
                                        @else
                                            <span class="fw-bold text-secondary">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                <span class="fw-bold small">{{ substr($hasil->nama_lengkap, 0, 1) }}</span>
                                            </div>
                                            <span class="fw-bold text-dark">{{ $hasil->nama_lengkap }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <h5 class="mb-0 fw-bold text-dark">{{ number_format($skor, 2) }} <small class="text-muted fs-6">/ 4.00</small></h5>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} border border-{{ $badgeColor }} rounded-pill px-3">
                                            {{ $kategori }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.evaluasi-hasil.show', ['sesi' => $sesiEdomAktif->id, 'dosen' => $hasil->dosen_id]) }}" class="btn btn-sm btn-outline-primary shadow-sm">
                                            Detail Evaluasi
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="opacity-50 mb-3">
                    <p class="text-muted fw-bold">Data evaluasi belum tersedia untuk periode ini.</p>
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

    // Grafik Tren Mahasiswa
    new Chart(document.getElementById('trenMahasiswaChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: trenMahasiswaLabels,
            datasets: [{
                label: 'Jumlah Mahasiswa Aktif',
                data: trenMahasiswaTotals,
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: '#4e73df',
                borderWidth: 1,
                borderRadius: 5,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, grid: { color: '#f3f3f3' } }, x: { grid: { display: false } } },
            plugins: { legend: { display: false } }
        }
    });

    // Grafik Distribusi Status
    new Chart(document.getElementById('distribusiStatusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: distribusiStatusLabels,
            datasets: [{
                data: distribusiStatusTotals,
                backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc', '#e74a3b', '#858796'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            },
            cutout: '70%'
        }
    });
});
</script>
<style>
    .ls-1 { letter-spacing: 1px; }
</style>
@endpush