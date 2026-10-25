@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    
    {{-- HEADER FORMAL --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 mt-2 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pusat Kontrol Administrator</h3>
            <span class="text-muted small uppercase">Sistem Informasi Akademik | {{ now()->translatedFormat('d F Y') }}</span>
        </div>
        <div class="text-sm-end">
            <span class="badge bg-dark px-3 py-2 font-monospace uppercase shadow-2xs">Otoritas Penuh</span>
        </div>
    </div>

    {{-- 1. PANEL PROGRES SEMESTER (SMART CHECKLIST) --}}
    @if($periodeAktif)
    <div class="card border-0 shadow-sm mb-4 border-top border-dark border-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <div>
                    <h6 class="fw-bold text-dark mb-1 uppercase">Progres Kelengkapan Data: {{ $periodeAktif->nama_semester }}</h6>
                    <span class="text-muted small">Pemantauan otomatis validasi operasional semester berjalan.</span>
                </div>
                <h4 class="fw-bold font-monospace text-dark mb-0">{{ $persenKrs }}%</h4>
            </div>
            
            <div class="progress mb-4" style="height: 8px;">
                <div class="progress-bar bg-dark" role="progressbar" style="width: {{ $persenKrs }}%;"></div>
            </div>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-dark me-2"></i>
                        <span class="small fw-bold text-dark">Semester Aktif Ditetapkan</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        @if($matkulBelumDiplot == 0)
                            <i class="bi bi-check-circle-fill text-dark me-2"></i>
                            <span class="small fw-bold text-dark">Seluruh Matkul Memiliki Dosen</span>
                        @else
                            <i class="bi bi-exclamation-circle-fill text-danger me-2"></i>
                            <a href="{{ route('admin.mata-kuliah.index') }}" class="small fw-bold text-danger text-decoration-none border-bottom border-danger">{{ $matkulBelumDiplot }} Mata Kuliah Belum Diplot Dosen</a>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <i class="bi {{ $persenKrs >= 90 ? 'bi-check-circle-fill text-dark' : 'bi-clock-history text-secondary' }} me-2"></i>
                        <span class="small fw-bold text-dark">Validasi KRS Mahasiswa ({{ $persenKrs }}%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-danger border-0 shadow-sm mb-4 border-start border-danger border-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <h6 class="fw-bold mb-0 uppercase small">Perhatian: Tahun Akademik Belum Aktif</h6>
                <small>Sistem mendeteksi belum ada Tahun Akademik / Semester yang ditetapkan aktif saat ini.</small>
            </div>
        </div>
    </div>
    @endif

    {{-- 2. STATISTIK UTAMA (1 HIGHLIGHT BER-WATERMARK + 3 POLOS SERAGAM) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card wm-card-exclusive hover-lift border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-white-50 small font-monospace fw-bold mb-1 uppercase" style="font-size: 11px;">DATA MAHASISWA</h6>
                    <h3 class="fw-bold font-monospace text-white mb-1">{{ number_format($totalMahasiswa) }}</h3>
                    <small class="text-white-50 font-monospace" style="font-size: 11px;">Entitas Terdaftar</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted small font-monospace fw-bold mb-1 uppercase" style="font-size: 11px;">DATA DOSEN</h6>
                    <h3 class="fw-bold font-monospace text-dark mb-1">{{ number_format($totalDosen) }}</h3>
                    <small class="text-muted font-monospace" style="font-size: 11px;">Tenaga Pendidik</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted small font-monospace fw-bold mb-1 uppercase" style="font-size: 11px;">PROGRAM STUDI</h6>
                    <h3 class="fw-bold font-monospace text-dark mb-1">{{ $totalProdi }}</h3>
                    <small class="text-muted font-monospace" style="font-size: 11px;">Unit Akademik</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted small font-monospace fw-bold mb-1 uppercase" style="font-size: 11px;">MATA KULIAH</h6>
                    <h3 class="fw-bold font-monospace text-dark mb-1">{{ number_format($totalMatkul) }}</h3>
                    <small class="text-muted font-monospace" style="font-size: 11px;">Kurikulum Aktif</small>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. GRAFIK DEMOGRAFI & AKSES CEPAT ADMINISTRASI --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Analitik Demografi & Infrastruktur Sistem</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center h-100">
                        <div class="col-md-5 border-end pe-md-4">
                            <canvas id="grafikMahasiswaProdi" style="max-height: 220px;"></canvas>
                        </div>
                        <div class="col-md-7 ps-md-4 mt-4 mt-md-0">
                            <h6 class="fw-bold text-dark mb-3 uppercase small">Pantauan Infrastruktur & Operasional</h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <span class="text-muted small">Status Server Aplikasi</span>
                                <span class="badge bg-dark text-white font-monospace">OPTIMAL</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <span class="text-muted small">Basis Data / SIAKAD</span>
                                <span class="text-dark small fw-bold font-monospace">TERKONEKSI</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <span class="text-muted small">Total Entitas Sivitas</span>
                                <span class="text-dark small fw-bold font-monospace">{{ number_format($totalMahasiswa + $totalDosen) }} AKUN</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Sinkronisasi Waktu Lokal</span>
                                <span class="text-dark small font-monospace fw-bold">{{ now()->format('H:i') }} WIT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MENU ADMINISTRASI UTAMA LENGKAP 6 ITEM --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="fw-bold mb-0 uppercase small">Menu Administrasi Utama</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.mahasiswa.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark uppercase">Manajemen Mahasiswa</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('admin.dosen.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark uppercase">Manajemen Dosen</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('admin.absensi.laporan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark uppercase">Rekapitulasi Presensi Pegawai</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('admin.user.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark uppercase">Otoritas Akun Pengguna</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('admin.pengaturan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark uppercase">Pengaturan Sistem & Tema</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('admin.evaluasi-hasil.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center border-bottom-0">
                        <span class="fw-bold small text-dark uppercase">Laporan Evaluasi Dosen (EDOM)</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. PANTAUAN KELAS VIRTUAL (VERUM) LIVE HARI INI --}}
    <div class="card border-0 shadow-sm mb-4 border-top border-dark border-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark uppercase small">
                Jadwal & Monitor Perkuliahan Hari Ini ({{ now()->translatedFormat('l, d F Y') }})
            </h6>
            <span class="badge bg-light text-dark border font-monospace">{{ $jadwalHariIni->count() }} KELAS</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 16%;">WAKTU (WIT)</th>
                            <th class="text-start" style="width: 42%;">MATA KULIAH & DOSEN PENGAMPU</th>
                            <th style="width: 26%;">RUANG / PLATFORM</th>
                            <th style="width: 16%;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalHariIni as $j)
                            <tr>
                                <td class="text-center font-monospace">
                                    <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}</span>
                                    <span class="text-muted">- {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}</span>
                                </td>
                                <td class="ps-3">
                                    <span class="fw-bold text-dark uppercase d-block">{{ $j->mataKuliah->nama_mk ?? ($j->mataKuliah->nama_mata_kuliah ?? '-') }}</span>
                                    <span class="text-muted small">{{ $j->mataKuliah->dosen->nama_lengkap ?? 'Belum Diplot' }}</span>
                                </td>
                                <td class="text-center font-monospace">
                                    @if(filter_var($j->ruang, FILTER_VALIDATE_URL))
                                        <a href="{{ $j->ruang }}" target="_blank" class="text-dark fw-bold text-decoration-underline">Buka Kelas Daring</a>
                                    @else
                                        <span>{{ $j->ruang ?? 'Verum LMS' }} ({{ $j->nama_kelas ?? 'A' }})</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <i class="bi {{ $j->ikon_status }} fs-5" title="{{ $j->tooltip_status }}" data-bs-toggle="tooltip"></i>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small uppercase fw-bold">Tidak ada jadwal perkuliahan yang berlangsung hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 5. LOG PENGUMUMAN & AKTIVITAS PENGGUNA --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 uppercase small">Log Pengumuman Terbaru</h6>
                    <a href="{{ route('admin.pengumuman.index') }}" class="text-white small text-decoration-none">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle small">
                            <thead class="bg-light border-bottom text-dark uppercase fw-bold">
                                <tr>
                                    <th class="ps-3" style="width: 130px;">TANGGAL</th>
                                    <th>JUDUL PENGUMUMAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengumumans as $p)
                                    <tr style="cursor: pointer;" onclick="window.location='{{ route('admin.pengumuman.show', $p->id) }}'">
                                        <td class="ps-3"><span class="badge bg-light text-dark border font-monospace">{{ $p->created_at->format('d/m/Y') }}</span></td>
                                        <td class="fw-bold text-dark pe-3">{{ $p->judul }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted small uppercase fw-bold">Belum ada pengumuman yang diterbitkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Pemantauan Akses Terakhir</h6>
                    <span class="badge bg-dark px-2 font-monospace">LOG</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0 align-middle small">
                            <tbody>
                                @forelse($logPengguna as $log)
                                    <tr class="border-bottom">
                                        <td class="ps-3 py-2.5">
                                            <h6 class="fw-bold text-dark mb-0 small uppercase">{{ $log->name }}</h6>
                                            <span class="text-muted font-monospace" style="font-size: 0.7rem; text-transform: uppercase;">
                                                {{ $log->roles->count() > 0 ? str_replace('_', ' ', $log->roles->first()->name) : 'PENGGUNA' }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3 py-2.5">
                                            <span class="badge bg-light text-dark border font-monospace">{{ $log->updated_at->diffForHumans() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted small uppercase fw-bold">Belum ada data riwayat akses.</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dataGrafik = @json($dataGrafikProdi ?? ['labels' => [], 'data' => []]);
        const ctx = document.getElementById('grafikMahasiswaProdi');
        
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dataGrafik.labels,
                    datasets: [{
                        data: dataGrafik.data,
                        backgroundColor: ['#1e293b', '#475569', '#64748b', '#94a3b8', '#cbd5e1'],
                        borderWidth: 1,
                        borderColor: '#ffffff',
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