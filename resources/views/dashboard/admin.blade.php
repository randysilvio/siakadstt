@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- WELCOME BANNER --}}
    <div class="card bg-primary text-white shadow-lg mb-4 border-0 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <div class="card-body p-4 p-md-5 position-relative">
            <div class="row align-items-center position-relative z-1">
                <div class="col-md-8">
                    <h1 class="fw-bold mb-2">Dashboard Administrator</h1>
                    <p class="lead mb-0 opacity-75">Selamat datang kembali! Berikut adalah ringkasan aktivitas sistem akademik hari ini, {{ now()->isoFormat('dddd, D MMMM Y') }}.</p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="bi bi-speedometer2" style="font-size: 5rem; opacity: 0.3;"></i>
                </div>
            </div>
            {{-- Dekorasi Latar --}}
            <div class="position-absolute top-0 end-0 p-5">
                <div class="bg-white opacity-10 rounded-circle" style="width: 200px; height: 200px; filter: blur(50px);"></div>
            </div>
        </div>
    </div>

    {{-- KARTU STATISTIK --}}
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold ls-1">Total Mahasiswa</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ number_format($totalMahasiswa) }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 70%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold ls-1">Total Dosen</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ number_format($totalDosen) }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-2">
                            <i class="bi bi-person-video3 fs-4"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold ls-1">Program Studi</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ $totalProdi }}</h2>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-2">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 85%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold ls-1">Mata Kuliah</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ number_format($totalMatkul) }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2">
                            <i class="bi bi-book-half fs-4"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 50%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KONTEN UTAMA: GRAFIK & MENU CEPAT --}}
    <div class="row g-4 mb-5">
        {{-- Kolom Kiri: Grafik --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-pie-chart-fill me-2"></i>Sebaran Mahasiswa per Prodi</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="max-height: 350px; width: 100%;">
                        <canvas id="grafikMahasiswaProdi"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Akses Cepat (Menu Grid) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-grid-fill me-2"></i>Akses Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Menu Item 1 --}}
                        <div class="col-6">
                            <a href="{{ route('admin.mahasiswa.index') }}" class="text-decoration-none">
                                <div class="card h-100 border bg-light hover-shadow transition-all text-center p-3">
                                    <i class="bi bi-mortarboard fs-2 text-primary mb-2"></i>
                                    <div class="small fw-bold text-dark">Data Mhs</div>
                                </div>
                            </a>
                        </div>
                        {{-- Menu Item 2 --}}
                        <div class="col-6">
                            <a href="{{ route('admin.dosen.index') }}" class="text-decoration-none">
                                <div class="card h-100 border bg-light hover-shadow transition-all text-center p-3">
                                    <i class="bi bi-person-video3 fs-2 text-success mb-2"></i>
                                    <div class="small fw-bold text-dark">Data Dosen</div>
                                </div>
                            </a>
                        </div>
                        {{-- Menu Item 3 --}}
                        <div class="col-6">
                            <a href="{{ route('admin.tahun-akademik.index') }}" class="text-decoration-none">
                                <div class="card h-100 border bg-light hover-shadow transition-all text-center p-3">
                                    <i class="bi bi-calendar-event fs-2 text-danger mb-2"></i>
                                    <div class="small fw-bold text-dark">Thn Akademik</div>
                                </div>
                            </a>
                        </div>
                        {{-- Menu Item 4 --}}
                        <div class="col-6">
                            <a href="{{ route('admin.pengaturan.index') }}" class="text-decoration-none">
                                <div class="card h-100 border bg-light hover-shadow transition-all text-center p-3">
                                    <i class="bi bi-gear fs-2 text-secondary mb-2"></i>
                                    <div class="small fw-bold text-dark">Pengaturan</div>
                                </div>
                            </a>
                        </div>
                        {{-- Menu Item 5 --}}
                        <div class="col-6">
                            <a href="{{ route('admin.absensi.laporan.index') }}" class="text-decoration-none">
                                <div class="card h-100 border bg-light hover-shadow transition-all text-center p-3">
                                    <i class="bi bi-clock-history fs-2 text-warning mb-2"></i>
                                    <div class="small fw-bold text-dark">Laporan Absen</div>
                                </div>
                            </a>
                        </div>
                        {{-- Menu Item 6 --}}
                        <div class="col-6">
                            <a href="{{ route('admin.evaluasi-hasil.index') }}" class="text-decoration-none">
                                <div class="card h-100 border bg-light hover-shadow transition-all text-center p-3">
                                    <i class="bi bi-clipboard-data fs-2 text-info mb-2"></i>
                                    <div class="small fw-bold text-dark">Hasil Evaluasi</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN BAWAH: MANAJEMEN KONTEN PUBLIK --}}
    <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-globe me-2"></i>Manajemen Konten Publik</h5>
    <div class="row g-4">
        {{-- Card Slideshow --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                        <i class="bi bi-images fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Slideshow</h6>
                        <p class="small text-muted mb-2">Gambar banner depan.</p>
                        <a href="{{ route('admin.slideshows.index') }}" class="text-decoration-none small fw-bold">Kelola <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Card Dokumen --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 me-3">
                        <i class="bi bi-file-earmark-arrow-down fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Dokumen Publik</h6>
                        <p class="small text-muted mb-2">File unduhan untuk umum.</p>
                        <a href="{{ route('admin.dokumen-publik.index') }}" class="text-decoration-none small fw-bold text-danger">Kelola <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Pengumuman --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                        <i class="bi bi-megaphone fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Pengumuman</h6>
                        <p class="small text-muted mb-2">Info untuk civitas.</p>
                        <a href="{{ route('admin.pengumuman.index') }}" class="text-decoration-none small fw-bold text-success">Kelola <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LIST PENGUMUMAN TERBARU (DI BAWAH) --}}
    <div class="card border-0 shadow-sm mt-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-bell me-2"></i>Pengumuman Terbaru</h5>
            <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Buat Baru</a>
        </div>
        <div class="list-group list-group-flush">
            @forelse($pengumumans as $p)
                <a href="{{ route('admin.pengumuman.show', $p->id) }}" class="list-group-item list-group-item-action py-3">
                    <div class="d-flex w-100 justify-content-between mb-1">
                        <h6 class="mb-1 fw-bold text-dark">{{ $p->judul }}</h6>
                        <small class="text-muted">{{ $p->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-1 small text-muted text-truncate" style="max-width: 80%;">{{ strip_tags($p->konten) }}</p>
                </a>
            @empty
                <div class="list-group-item text-center py-4 text-muted">
                    Belum ada pengumuman yang dibuat.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dataGrafik = @json($dataGrafikProdi);

    const ctx = document.getElementById('grafikMahasiswaProdi').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: dataGrafik.labels,
            datasets: [{
                data: dataGrafik.data,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        usePointStyle: true,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#666',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) { label += ': '; }
                            label += context.raw + ' Mahasiswa';
                            return label;
                        }
                    }
                }
            },
            cutout: '70%',
        }
    });
</script>
<style>
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        cursor: pointer;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>
@endpush