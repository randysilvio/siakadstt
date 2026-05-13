@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER FORMAL --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0">PUSAT KONTROL ADMINISTRATOR</h3>
            <span class="text-muted small uppercase">Sistem Informasi Akademik | {{ now()->translatedFormat('d F Y') }}</span>
        </div>
        <div class="text-end">
            <span class="badge bg-dark rounded-1 px-3 py-2">STATUS: OTORITAS PENUH</span>
        </div>
    </div>

    {{-- STATISTIK UTAMA --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 rounded-0">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold mb-1">DATA MAHASISWA</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($totalMahasiswa) }}</h3>
                    <small class="text-primary">Entitas Terdaftar</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 rounded-0">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold mb-1">DATA DOSEN</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($totalDosen) }}</h3>
                    <small class="text-success">Tenaga Pendidik</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 rounded-0">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold mb-1">PROGRAM STUDI</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ $totalProdi }}</h3>
                    <small class="text-info">Unit Akademik</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4 rounded-0">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold mb-1">MATA KULIAH</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($totalMatkul) }}</h3>
                    <small class="text-warning">Kurikulum Aktif</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- GRAFIK ANALITIK --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 rounded-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark uppercase">Distribusi Mahasiswa per Program Studi</h6>
                </div>
                <div class="card-body">
                    <canvas id="grafikMahasiswaProdi" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>

        {{-- AKSES CEPAT (TANPA IKON NON-FORMAL) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark uppercase">Menu Administrasi Utama</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.mahasiswa.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">MANAJEMEN MAHASISWA</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('admin.dosen.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">MANAJEMEN DOSEN</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('admin.absensi.laporan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">REKAPITULASI PRESENSI</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('admin.user.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">OTORITAS PENGGUNA</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('admin.pengaturan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">KONFIGURASI SISTEM</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('admin.evaluasi-hasil.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">LAPORAN EVALUASI (EDOM)</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MANAJEMEN KONTEN PUBLIK --}}
    <h6 class="fw-bold mb-3 text-dark uppercase">Publikasi & Informasi Luar</h6>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-body p-4 border-top border-primary border-4">
                    <h6 class="fw-bold text-dark mb-1">Materi Slideshow</h6>
                    <p class="small text-muted mb-3">Banner visual halaman depan.</p>
                    <a href="{{ route('admin.slideshows.index') }}" class="btn btn-sm btn-dark w-100 rounded-0">KELOLA MEDIA</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-body p-4 border-top border-danger border-4">
                    <h6 class="fw-bold text-dark mb-1">Dokumen Publik</h6>
                    <p class="small text-muted mb-3">Arsip file unduhan umum.</p>
                    <a href="{{ route('admin.dokumen-publik.index') }}" class="btn btn-sm btn-dark w-100 rounded-0">KELOLA FILE</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-body p-4 border-top border-success border-4">
                    <h6 class="fw-bold text-dark mb-1">Rilis Pengumuman</h6>
                    <p class="small text-muted mb-3">Warta resmi civitas akademika.</p>
                    <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-sm btn-dark w-100 rounded-0">KELOLA WARTA</a>
                </div>
            </div>
        </div>
    </div>

    {{-- LOG PENGUMUMAN TERAKHIR --}}
    <div class="card border-0 shadow-sm rounded-0">
        <div class="card-header bg-dark text-white py-3">
            <h6 class="fw-bold mb-0 uppercase">Log Pengumuman Terbaru</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <tbody>
                        @forelse($pengumumans as $p)
                            <tr>
                                <td class="ps-4" style="width: 150px;"><span class="text-muted small font-monospace">{{ $p->created_at->format('d/m/Y') }}</span></td>
                                <td class="fw-bold text-dark">{{ $p->judul }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.pengumuman.show', $p->id) }}" class="btn btn-sm btn-light border text-dark">Buka</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted small">Belum ada pengumuman yang diterbitkan.</td></tr>
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
    const dataGrafik = @json($dataGrafikProdi);
    const ctx = document.getElementById('grafikMahasiswaProdi').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: dataGrafik.labels,
            datasets: [{
                data: dataGrafik.data,
                backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d', '#212529'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11, weight: 'bold' } } }
            },
            cutout: '65%',
        }
    });
</script>
@endpush