@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    {{-- 1. HEADER & TOMBOL PUSAT LAPORAN --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 mt-2 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dashboard Penjaminan Mutu</h3>
            <span class="text-muted small uppercase">Monitoring Indikator Kinerja Utama & Kesiapan Akreditasi</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border uppercase fw-bold font-monospace px-3 py-2" style="font-size: 11px;">
                Sistem Aktif
            </span>
            <a href="{{ route('mutu.laporan.index') }}" class="btn btn-sm btn-dark px-3 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Pusat Laporan Akreditasi
            </a>
        </div>
    </div>

    {{-- 2. KARTU INDIKATOR KINERJA UTAMA (KPI) LENGKAP --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card wm-card-exclusive hover-lift border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-white-50 mb-2" style="font-size: 11px;">Total Mahasiswa</h6>
                    <h2 class="fw-bold text-white mb-1 font-monospace">{{ number_format($jumlahMahasiswaAktif) }}</h2>
                    <span class="small text-white-50 uppercase font-monospace" style="font-size: 10px;">Status Aktif</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2" style="font-size: 11px;">Total Dosen Tetap</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($jumlahDosen) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 10px;">NIDN Terdaftar</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2" style="font-size: 11px;">Rasio Dosen : Mhs</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ $rasioDosenMahasiswa }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 10px;">Indikator Borang</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2" style="font-size: 11px;">Rata-rata IPK</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($rataIPK, 2) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 10px;">Capaian Akademik</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI DATA & ANALISIS (2 GRAFIK DIKEMBALIKAN PENUH) --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
                    Tren Pertumbuhan Mahasiswa per Angkatan
                </div>
                <div class="card-body p-4">
                    <canvas id="trenMahasiswaChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
                    Distribusi Status Mahasiswa
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 280px;">
                        <canvas id="distribusiStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. LAPORAN EVALUASI DOSEN & DATA LULUSAN --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Kinerja Dosen (Hasil EDOM)</span>
                    @if($sesiEdomAktif)
                        <span class="badge bg-light text-dark uppercase font-monospace fw-bold" style="font-size: 10px;">
                            Periode: {{ $sesiEdomAktif->nama_sesi }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($sesiEdomAktif && $hasilEdom->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 small">
                                <thead class="bg-light text-dark uppercase text-center fw-bold">
                                    <tr>
                                        <th style="width: 8%;">NO</th>
                                        <th class="text-start" style="width: 37%;">NAMA DOSEN</th>
                                        <th style="width: 20%;">SKOR RATA-RATA</th>
                                        <th style="width: 20%;">KATEGORI</th>
                                        <th style="width: 15%;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hasilEdom as $index => $hasil)
                                        @php
                                            $skor = $hasil->rata_rata_skor;
                                            $kategori = $skor >= 3.5 ? 'SANGAT BAIK' : ($skor >= 2.5 ? 'BAIK' : 'PERLU EVALUASI');
                                        @endphp
                                        <tr>
                                            <td class="text-center font-monospace fw-bold text-dark">{{ $index + 1 }}</td>
                                            <td class="text-start uppercase fw-bold text-dark ps-3">{{ $hasil->nama_lengkap }}</td>
                                            <td class="text-center font-monospace fw-bold text-dark">
                                                {{ number_format($skor, 2) }} <span class="text-muted small">/ 4.00</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-dark text-white uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                                    {{ $kategori }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.evaluasi-hasil.show', ['sesi' => $sesiEdomAktif->id, 'dosen' => $hasil->dosen_id]) }}" class="btn btn-sm btn-outline-dark py-1 px-3 uppercase fw-bold small">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 uppercase fw-bold text-muted small">
                            Data evaluasi belum tersedia untuk periode aktif ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Profil Kinerja Lulusan (Akreditasi)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light text-muted uppercase text-center fw-bold">
                                <tr>
                                    <th class="text-start ps-3">TAHUN</th>
                                    <th>LULUSAN</th>
                                    <th>IPK RATA2</th>
                                    <th>MASA STUDI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataLulusan as $lulusan)
                                    <tr>
                                        <td class="ps-3 fw-bold font-monospace">{{ $lulusan->tahun_lulus }}</td>
                                        <td class="text-center"><span class="badge bg-dark px-2 py-1 font-monospace">{{ $lulusan->jumlah }} ORANG</span></td>
                                        <td class="text-center font-monospace fw-bold text-dark">{{ number_format($lulusan->rata_ipk, 2) }}</td>
                                        <td class="text-center font-monospace text-muted">{{ $lulusan->rata_masa_studi }} TAHUN</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted small uppercase fw-bold">
                                            Belum Ada Data Lulusan Tercatat
                                        </td>
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
document.addEventListener('DOMContentLoaded', function () {
    const trenMahasiswaLabels = @json($trenMahasiswaLabels);
    const trenMahasiswaTotals = @json($trenMahasiswaTotals);
    const distribusiStatusLabels = @json($distribusiStatusLabels);
    const distribusiStatusTotals = @json($distribusiStatusTotals);

    Chart.defaults.font.family = "'Inter', sans-serif";

    new Chart(document.getElementById('trenMahasiswaChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: trenMahasiswaLabels,
            datasets: [{
                label: 'JUMLAH MAHASISWA AKTIF',
                data: trenMahasiswaTotals,
                backgroundColor: '#1e293b',
                borderColor: '#0f172a',
                borderWidth: 1,
                barThickness: 38
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

    new Chart(document.getElementById('distribusiStatusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: distribusiStatusLabels,
            datasets: [{
                data: distribusiStatusTotals,
                backgroundColor: ['#1e293b', '#475569', '#64748b', '#94a3b8', '#cbd5e1'],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: 'bold' } } }
            },
            cutout: '68%'
        }
    });
});
</script>
@endpush