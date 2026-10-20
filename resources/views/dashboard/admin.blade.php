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
            <span class="badge bg-dark rounded-1 px-3 py-2 shadow-sm">STATUS: OTORITAS PENUH</span>
        </div>
    </div>

    {{-- FASE 1: PANEL PROGRES SEMESTER (SMART CHECKLIST) --}}
    @if($periodeAktif)
    <div class="card border-0 shadow-sm rounded-0 mb-4 border-top border-dark border-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <div>
                    <h6 class="fw-bold text-dark mb-1 uppercase">Progres Kelengkapan Data: {{ $periodeAktif->nama_semester }}</h6>
                    <span class="text-muted small">Pemantauan otomatis validasi operasional semester berjalan.</span>
                </div>
                <h4 class="fw-bold text-dark mb-0">{{ $persenKrs }}%</h4>
            </div>
            
            <div class="progress rounded-0 mb-4" style="height: 8px;">
                <div class="progress-bar bg-dark" role="progressbar" style="width: {{ $persenKrs }}%;"></div>
            </div>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span class="small fw-bold text-dark">Semester Aktif Ditetapkan</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        @if($matkulBelumDiplot == 0)
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="small fw-bold text-dark">Seluruh Matkul Memiliki Dosen</span>
                        @else
                            <i class="bi bi-exclamation-circle-fill text-danger me-2"></i>
                            <a href="{{ route('admin.mahasiswa.index') }}" class="small fw-bold text-danger text-decoration-none border-bottom border-danger transition-btn">{{ $matkulBelumDiplot }} Mata Kuliah Belum Diplot Dosen</a>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        @if($persenKrs >= 90)
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="small fw-bold text-dark">KRS Disetujui ({{ $persenKrs }}%)</span>
                        @else
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            <span class="small fw-bold text-dark">Validasi KRS Berjalan ({{ $persenKrs }}%)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-danger rounded-0 border-0 shadow-sm mb-4 border-start border-danger border-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <h6 class="fw-bold mb-0">Perhatian Tindakan Diperlukan</h6>
                <small>Sistem mendeteksi belum ada Tahun Akademik / Semester yang di-set aktif untuk saat ini.</small>
            </div>
        </div>
    </div>
    @endif

    {{-- STATISTIK UTAMA --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-start border-primary border-4 rounded-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold mb-1">DATA MAHASISWA</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($totalMahasiswa) }}</h3>
                    <small class="text-primary">Entitas Terdaftar</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-start border-success border-4 rounded-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold mb-1">DATA DOSEN</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($totalDosen) }}</h3>
                    <small class="text-success">Tenaga Pendidik</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-start border-info border-4 rounded-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold mb-1">PROGRAM STUDI</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ $totalProdi }}</h3>
                    <small class="text-info">Unit Akademik</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-start border-warning border-4 rounded-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold mb-1">MATA KULIAH</h6>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($totalMatkul) }}</h3>
                    <small class="text-warning">Kurikulum Aktif</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- GRAFIK & PANTAUAN SISTEM (Split Layout) --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 rounded-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase">Analitik Demografi & Sistem</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center h-100">
                        <div class="col-md-5 border-end pe-md-4">
                            <canvas id="grafikMahasiswaProdi" style="max-height: 220px;"></canvas>
                        </div>
                        <div class="col-md-7 ps-md-4 mt-4 mt-md-0">
                            <h6 class="fw-bold text-dark mb-4 uppercase small">Pantauan Infrastruktur & Operasional</h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <span class="text-muted small">Status Server App</span>
                                <span class="badge bg-success rounded-0">OPTIMAL</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <span class="text-muted small">Basis Data / SIAKAD</span>
                                <span class="text-dark small fw-bold">Terkoneksi</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <span class="text-muted small">Total Entitas Terdaftar</span>
                                <span class="text-dark small fw-bold">{{ number_format($totalMahasiswa + $totalDosen) }} Akun</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Sinkronisasi Waktu Lokal</span>
                                <span class="text-dark small font-monospace">{{ now()->format('H:i') }} WIT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- AKSES CEPAT --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark uppercase">Menu Administrasi Utama</h6>
                </div>
                <div class="list-group list-group-flush menu-akses-cepat">
                    <a href="{{ route('admin.mahasiswa.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">MANAJEMEN MAHASISWA</span>
                        <i class="bi bi-chevron-right text-muted transition-icon"></i>
                    </a>
                    <a href="{{ route('admin.dosen.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">MANAJEMEN DOSEN</span>
                        <i class="bi bi-chevron-right text-muted transition-icon"></i>
                    </a>
                    <a href="{{ route('admin.absensi.laporan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">REKAPITULASI PRESENSI</span>
                        <i class="bi bi-chevron-right text-muted transition-icon"></i>
                    </a>
                    <a href="{{ route('admin.user.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">OTORITAS PENGGUNA</span>
                        <i class="bi bi-chevron-right text-muted transition-icon"></i>
                    </a>
                    <a href="{{ route('admin.pengaturan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">KONFIGURASI SISTEM</span>
                        <i class="bi bi-chevron-right text-muted transition-icon"></i>
                    </a>
                    <a href="{{ route('admin.evaluasi-hasil.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center border-bottom-0">
                        <span class="fw-bold small text-dark">LAPORAN EVALUASI (EDOM)</span>
                        <i class="bi bi-chevron-right text-muted transition-icon"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL COMPACT: PANTAUAN KELAS VIRTUAL (VERUM) LIVE HARI INI --}}
    <div class="card border-0 shadow-sm rounded-0 mb-4 border-top border-danger border-4">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark uppercase small">
                <i class="bi bi-headset text-danger me-2"></i>Monitor Kelas Virtual: {{ now()->translatedFormat('d F Y') }}
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-sm mb-0 align-middle">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="ps-4 text-muted fw-bold uppercase" style="width: 15%; font-size: 0.75rem;">Jam</th>
                            <th class="text-muted fw-bold uppercase" style="width: 40%; font-size: 0.75rem;">Mata Kuliah / Dosen</th>
                            <th class="text-muted fw-bold uppercase" style="width: 30%; font-size: 0.75rem;">Platform</th>
                            <th class="text-center pe-4 text-muted fw-bold uppercase" style="width: 15%; font-size: 0.75rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalHariIni as $j)
                            <tr class="border-bottom">
                                <td class="ps-4">
                                    <span class="font-monospace fw-bold text-dark" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}</span>
                                    <span class="text-muted d-block" style="font-size: 0.7rem;">s/d {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block" style="font-size: 0.85rem;">{{ $j->mataKuliah->nama_mata_kuliah ?? '-' }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $j->mataKuliah->dosen->nama_lengkap ?? 'Belum Diplot' }}</span>
                                </td>
                                <td>
                                    @if(filter_var($j->ruang, FILTER_VALIDATE_URL))
                                        <a href="{{ $j->ruang }}" target="_blank" class="text-primary text-decoration-none transition-btn" style="font-size: 0.75rem;"><i class="bi bi-link-45deg"></i> Buka Tautan</a>
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-laptop"></i> {{ $j->ruang ?? 'E-Learning Verum' }}</span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    {{-- Ikon status dinamis --}}
                                    <i class="bi {{ $j->ikon_status }} fs-5" title="{{ $j->tooltip_status }}" data-bs-toggle="tooltip"></i>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">Tidak ada kelas virtual yang dijadwalkan hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BAGIAN BAWAH: LOG PENGUMUMAN & AKTIVITAS PENGGUNA --}}
    <div class="row g-4 mb-5">
        
        {{-- TABEL PENGUMUMAN --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 h-100">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 uppercase">Log Pengumuman Terbaru</h6>
                    <a href="{{ route('admin.pengumuman.index') }}" class="text-white small text-decoration-none opacity-75 hover-opacity-100">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless mb-0 align-middle">
                            <thead class="bg-light border-bottom">
                                <tr>
                                    <th class="ps-4 text-muted small fw-bold uppercase" style="width: 140px;">Tanggal Rilis</th>
                                    <th class="text-muted small fw-bold uppercase">Judul Pengumuman</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengumumans as $p)
                                    <tr style="cursor: pointer;" onclick="window.location='{{ route('admin.pengumuman.show', $p->id) }}'">
                                        <td class="ps-4"><span class="badge bg-light text-dark border font-monospace">{{ $p->created_at->format('d/m/Y') }}</span></td>
                                        <td class="fw-bold text-dark pe-4">
                                            <div class="text-truncate" style="max-width: 300px;" title="{{ $p->judul }}">
                                                {{ $p->judul }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted small">Belum ada pengumuman yang diterbitkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL AKTIVITAS PENGGUNA --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase">Pemantauan Akses Terakhir</h6>
                    <span class="badge bg-dark rounded-0 px-3">LOG</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0 align-middle">
                            <tbody>
                                @forelse($logPengguna as $log)
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <h6 class="fw-bold text-dark mb-0 small">{{ $log->name }}</h6>
                                            @if($log->roles->count() > 0)
                                                <span class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">{{ str_replace('_', ' ', $log->roles->first()->name) }}</span>
                                            @else
                                                <span class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">PENGGUNA</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <span class="badge bg-light text-dark border font-monospace">{{ $log->updated_at->diffForHumans() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted small">Belum ada data riwayat akses pengguna.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    /* Efek Melayang Kartu */
    .hover-lift {
        transition: transform 0.25s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.1) !important;
    }
    
    /* Animasi Panah Menu */
    .transition-icon {
        transition: transform 0.2s ease-in-out, color 0.2s ease-in-out;
    }
    .menu-akses-cepat .list-group-item:hover .transition-icon {
        transform: translateX(4px);
        color: #212529 !important;
    }
    .menu-akses-cepat .list-group-item:hover {
        background-color: #f8f9fa;
    }

    /* Efek Transisi Text/Link */
    .transition-btn {
        transition: all 0.2s;
    }
    .transition-btn:hover {
        opacity: 0.7;
    }
    
    .hover-opacity-100:hover {
        opacity: 1 !important;
    }

    /* Tabel Hover */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Animasi Berkedip Halus (Pulse) untuk Icon Status Kelas Live */
    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.3; transform: scale(1.15); }
        100% { opacity: 1; transform: scale(1); }
    }
    .pulse-badge {
        animation: pulse 1.5s infinite;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inisialisasi Tooltip Bootstrap (Agar keterangan ikon muncul saat di-hover)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Inisialisasi Chart Donat
        const dataGrafik = @json($dataGrafikProdi ?? ['labels' => [], 'data' => []]);
        const ctx = document.getElementById('grafikMahasiswaProdi');
        
        if(ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dataGrafik.labels,
                    datasets: [{
                        data: dataGrafik.data,
                        backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d', '#212529'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endpush