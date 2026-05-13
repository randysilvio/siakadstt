@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER PORTAL INTERNAL --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3 border-bottom pb-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dasbor Utama Portal</h3>
            <span class="text-muted small uppercase">Sistem Informasi Akademik & Kemahasiswaan Terintegrasi</span>
        </div>
        <div>
            <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold px-3 py-2 shadow-none" style="font-size: 11px;">
                <i class="bi bi-person-fill me-1"></i> {{ strtoupper(Auth::user()->roles->first()->display_name ?? Auth::user()->roles->first()->name ?? 'PENGGUNA') }}
            </span>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- TAMPILAN DASHBOARD MAHASISWA --}}
    {{-- ============================================================== --}}
    @if(Auth::user()->hasRole('mahasiswa'))
        @if(isset($mahasiswa))
            
            {{-- ===== UPDATE BARU: Alert Notifikasi Evaluasi Dosen ===== --}}
            @php
                $cekSesiAktif = \App\Models\EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->exists();
                $tampilkanAlert = (isset($periodeEvaluasiAktif) && $periodeEvaluasiAktif) || $cekSesiAktif;
            @endphp

            @if($tampilkanAlert)
                <div class="alert alert-primary border border-primary border-2 rounded-0 d-flex align-items-center mb-4 shadow-sm p-4" role="alert">
                    <i class="bi bi-megaphone-fill fs-2 me-4 text-primary"></i>
                    <div>
                        <h6 class="alert-heading fw-bold uppercase text-dark mb-1">Periode Evaluasi Dosen Sedang Berlangsung</h6>
                        <p class="small text-muted uppercase mb-2">
                            Silakan isi kuesioner evaluasi untuk mata kuliah yang Anda ambil semester ini guna meningkatkan mutu pengajaran.
                        </p>
                        <a href="{{ route('evaluasi.index') }}" class="btn btn-sm btn-primary rounded-0 uppercase fw-bold small px-4 py-2">
                            <i class="bi bi-pencil-square me-1"></i> Mulai Evaluasi Sekarang
                        </a>
                    </div>
                </div>
            @endif
            {{-- ========================================================== --}}
            
            {{-- Notifikasi Tagihan --}}
            @if(isset($memiliki_tagihan) && $memiliki_tagihan)
                <div class="alert alert-danger border border-danger border-2 rounded-0 p-3 mb-4 shadow-sm small uppercase" role="alert">
                    <strong class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>PERHATIAN:</strong> Anda memiliki tagihan kewajiban keuangan yang belum diselesaikan. 
                    <a href="{{ route('pembayaran.riwayat') }}" class="alert-link text-underline fw-bold">Lihat Riwayat Pembayaran</a> untuk mengonfirmasi status.
                </div>
            @endif

            <div class="row g-4 mb-5">
                {{-- KANVAS KIRI: Identitas & Jadwal --}}
                <div class="col-lg-8">
                    {{-- Kartu Profil Enterprise --}}
                    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
                        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                            Portofolio Akademik Mahasiswa
                        </div>
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark uppercase mb-3">{{ $mahasiswa->nama_lengkap }}</h5>
                            <div class="row g-3 border-top pt-3">
                                <div class="col-md-6">
                                    <span class="small text-muted font-monospace uppercase d-block" style="font-size: 10px;">NOMOR INDUK MAHASISWA</span>
                                    <strong class="text-primary font-monospace fs-6">{{ $mahasiswa->nim }}</strong>
                                    
                                    <span class="small text-muted font-monospace uppercase d-block mt-2" style="font-size: 10px;">PROGRAM STUDI</span>
                                    <strong class="text-dark uppercase small">{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</strong>
                                </div>
                                <div class="col-md-6 border-start ps-md-4">
                                    <span class="small text-muted font-monospace uppercase d-block" style="font-size: 10px;">DOSEN PEMBIMBING WALI</span>
                                    <strong class="text-dark uppercase small">{{ optional($mahasiswa->dosenWali)->nama_lengkap ?? 'BELUM DITENTUKAN' }}</strong>
                                    
                                    <span class="small text-muted font-monospace uppercase d-block mt-2" style="font-size: 10px;">STATUS PERSETUJUAN KRS</span>
                                    <div class="mt-1">
                                        @php
                                            $krsClass = match($mahasiswa->status_krs) {
                                                'Disetujui' => 'bg-success text-white',
                                                'Ditolak' => 'bg-danger text-white',
                                                'Menunggu Persetujuan' => 'bg-warning text-dark',
                                                default => 'bg-secondary text-white'
                                            };
                                        @endphp
                                        <span class="badge {{ $krsClass }} rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">
                                            {{ $mahasiswa->status_krs ?? 'BELUM MENGISI' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Rincian Jadwal Kuliah --}}
                    <div class="card border-0 shadow-sm rounded-0">
                        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
                            Jadwal Perkuliahan Terdaftar Semester Ini
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                                        <tr>
                                            <th style="width: 15%;">HARI</th>
                                            <th style="width: 20%;">JAM (WIT)</th>
                                            <th class="text-start">MATA KULIAH</th>
                                            <th style="width: 8%;">SKS</th>
                                            <th class="text-start" style="width: 25%;">PENGAMPU</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small text-dark">
                                        @forelse ($jadwalKuliah as $jadwal)
                                            <tr>
                                                <td class="text-center fw-bold uppercase">{{ $jadwal->hari }}</td>
                                                <td class="text-center font-monospace text-muted">
                                                    {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                                </td>
                                                <td class="text-start uppercase fw-bold text-dark">
                                                    {{ optional($jadwal->mataKuliah)->nama_mk ?? '-' }}
                                                </td>
                                                <td class="text-center font-monospace fw-bold text-primary">
                                                    {{ optional($jadwal->mataKuliah)->sks ?? 0 }}
                                                </td>
                                                <td class="text-start uppercase text-muted" style="font-size: 11px;">
                                                    {{ optional(optional($jadwal->mataKuliah)->dosen)->nama_lengkap ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 uppercase fw-bold text-muted small">
                                                    Anda belum mengambil KRS pada semester aktif atau KRS belum disahkan wali.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KANVAS KANAN: Pintasan, Metrik, & Warta --}}
                <div class="col-lg-4">
                    {{-- Metrik Indeks Kumulatif (Monospace & Siku) --}}
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="bg-light border border-dark border-opacity-25 rounded-0 p-3 text-center">
                                <span class="small text-muted font-monospace uppercase d-block" style="font-size: 10px;">IPK KUMULATIF</span>
                                <span class="fs-3 fw-bold font-monospace text-dark">{{ number_format($ipk, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light border border-dark border-opacity-25 rounded-0 p-3 text-center">
                                <span class="small text-muted font-monospace uppercase d-block" style="font-size: 10px;">SKS DISAHKAN</span>
                                <span class="fs-3 fw-bold font-monospace text-primary">{{ $total_sks }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Bilah Aksi Cepat --}}
                    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                        <div class="card-header bg-light text-dark rounded-0 py-3 uppercase fw-bold small border-bottom">
                            Pintasan Operasional
                        </div>
                        <div class="list-group list-group-flush rounded-0">
                            @if(isset($periodeKrsAktif) && $periodeKrsAktif && $mahasiswa->status_krs !== 'Disetujui')
                                <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action rounded-0 p-3 border-start border-primary border-4 bg-light">
                                    <strong class="uppercase small fw-bold text-primary d-block">Pengisian / Revisi KRS</strong>
                                    <span class="font-monospace text-muted" style="font-size: 10px;">JALUR PERWALIAN TERBUKA</span>
                                </a>
                            @endif

                            @if($tampilkanAlert)
                                <a href="{{ route('evaluasi.index') }}" class="list-group-item list-group-item-action rounded-0 p-3 border-start border-success border-4 bg-light">
                                    <strong class="uppercase small fw-bold text-success d-block">Kuesioner Mutu (EDOM)</strong>
                                    <span class="font-monospace text-muted" style="font-size: 10px;">SURVEI WAJIB SEMESTER</span>
                                </a>
                            @endif
                            
                            <a href="{{ route('khs.index') }}" class="list-group-item list-group-item-action rounded-0 px-3 py-2 small uppercase fw-bold text-dark">
                                <i class="bi bi-chevron-right me-1 text-muted"></i> Kartu Hasil Studi (KHS)
                            </a>
                            <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action rounded-0 px-3 py-2 small uppercase fw-bold text-dark">
                                <i class="bi bi-chevron-right me-1 text-muted"></i> Transkrip Akademik Sementara
                            </a>
                        </div>
                    </div>

                    {{-- Pengumuman Sistem --}}
                     <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
                            Warta Pengumuman Internal
                        </div>
                        <div class="list-group list-group-flush rounded-0">
                            @forelse($pengumuman as $p)
                                <div class="list-group-item rounded-0 p-3">
                                    <h6 class="uppercase fw-bold text-dark small mb-1">{{ $p->judul }}</h6>
                                    <span class="font-monospace text-muted" style="font-size: 10px;">
                                        DIPUBLIKASIKAN: {{ $p->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            @empty
                                <div class="list-group-item rounded-0 p-4 text-center text-muted small uppercase font-monospace">
                                    TIDAK ADA PENGUMUMAN BARU.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Grafik Analisis Tren Kinerja --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Grafik Tren Indeks Prestasi Semester (IPS)
                </div>
                <div class="card-body p-4">
                    <canvas id="grafikIPS" height="250"></canvas>
                </div>
            </div>
        @else
             <div class="alert alert-warning border rounded-0 font-monospace small uppercase p-3">
                 Data master portofolio mahasiswa Anda tidak ditemukan. Silakan hubungi bagian BAAK.
             </div>
        @endif
    @endif

    {{-- ============================================================== --}}
    {{-- TAMPILAN DASHBOARD DOSEN --}}
    {{-- ============================================================== --}}
    @if(Auth::user()->hasRole('dosen'))
        <div class="alert alert-light border border-dark border-opacity-25 rounded-0 p-4 mb-4 shadow-sm">
            <h6 class="uppercase fw-bold small text-dark mb-2">Dasbor Tenaga Pendidik</h6>
            <p class="small mb-0 text-muted uppercase font-monospace">
                Selamat datang. Silakan gunakan menu navigasi utama untuk mengelola perwalian, ruang virtual (Verum), serta entri penilaian akademik.
            </p>
        </div>
    @endif
    
    {{-- ============================================================== --}}
    {{-- TAMPILAN DASHBOARD ADMIN --}}
    {{-- ============================================================== --}}
    @if(Auth::user()->hasRole('admin'))
        <div class="alert alert-light border border-dark border-opacity-25 rounded-0 p-4 mb-4 shadow-sm">
            <h6 class="uppercase fw-bold small text-dark mb-2">Dasbor Administrator Sistem</h6>
            <p class="small mb-0 text-muted uppercase font-monospace">
                Selamat bekerja. Ruang kendali master data, penjaminan mutu, konfigurasi institusi, dan sirkulasi keuangan terpusat aktif beroperasi.
            </p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    @if(Auth::user()->hasRole('mahasiswa') && isset($dataGrafik))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dataGrafik = @json($dataGrafik);
                const ctx = document.getElementById('grafikIPS').getContext('2d');
                
                // Set tipografi global grafik menggunakan monospace
                Chart.defaults.font.family = "'font-monospace', monospace";
                
                if (dataGrafik.labels && dataGrafik.labels.length > 0) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dataGrafik.labels,
                            datasets: [{
                                label: 'CAPAIAN IPS',
                                data: dataGrafik.data,
                                borderColor: '#212529',
                                backgroundColor: 'rgba(33, 37, 41, 0.05)',
                                fill: true,
                                borderWidth: 2,
                                pointBackgroundColor: '#0d6efd',
                                pointRadius: 4,
                                tension: 0 // Garis patah presisi tanpa lengkungan
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 4.0,
                                    ticks: { font: { weight: 'bold' } },
                                    grid: { color: '#dee2e6' }
                                },
                                x: {
                                    ticks: { font: { weight: 'bold' } },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                } else {
                    ctx.font = "bold 12px monospace";
                    ctx.fillStyle = "#6c757d";
                    ctx.textAlign = "center";
                    ctx.fillText("DATA HISTORIS IPS BELUM TERSEDIA.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                }
            });
        </script>
    @endif
@endpush