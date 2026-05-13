@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- 1. BAGIAN HEADER UTAMA (Flat & Enterprise) --}}
    <div class="card border-0 rounded-0 bg-dark text-white p-4 mb-4 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="small font-monospace text-muted uppercase">Executive Information System</span>
                <h3 class="fw-bold uppercase mb-1 mt-1 text-white">Selamat Datang, Pimpinan</h3>
                <p class="small mb-0 text-light opacity-75">
                    Laporan strategis performa akademik dan keuangan institusi secara real-time.
                </p>
            </div>
            <div>
                <a href="{{ route('rektorat.cetak') }}" target="_blank" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
                </a>
            </div>
        </div>
    </div>

    {{-- 2. KOTAK KPI (Sudut Siku, Monospace, Flat Border) --}}
    <div class="row g-4 mb-4">
        {{-- Total Mahasiswa --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Mahasiswa Aktif</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ $totalMahasiswaAktif }}</h2>
                    <span class="badge bg-success rounded-0 uppercase fw-bold font-monospace" style="font-size: 10px;">Real-Time</span>
                </div>
            </div>
        </div>

        {{-- Pendaftar Baru --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Pendaftar Tahun Ini</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ $pendaftarTahunIni }}</h2>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 11px;">Calon Mahasiswa</span>
                </div>
            </div>
        </div>

        {{-- Pendapatan --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Pendapatan Semester</h6>
                    <h3 class="fw-bold text-dark mb-1 font-monospace">Rp{{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</h3>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 11px;">Total Masuk</span>
                </div>
            </div>
        </div>

        {{-- Lulusan --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Lulusan Tahun Ini</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ $mahasiswaLulusTahunIni }}</h2>
                    <span class="small text-muted uppercase fw-bold" style="font-size: 11px;">Alumni Baru</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI DATA (Flat Tabs) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Analisis Tren Strategis (5 Tahun Terakhir)
                </div>
                <div class="card-body p-4">
                    <ul class="nav nav-tabs mb-4 rounded-0" id="grafikTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-0 fw-bold px-4 small uppercase text-dark" id="keuangan-tab" data-bs-toggle="tab" data-bs-target="#keuangan" type="button" role="tab">
                                Tren Keuangan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" id="pertumbuhan-tab" data-bs-toggle="tab" data-bs-target="#pertumbuhan" type="button" role="tab">
                                Pertumbuhan Mahasiswa
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="grafikTabContent">
                        <div class="tab-pane fade show active" id="keuangan" role="tabpanel">
                            <div style="height: 350px;">
                                <canvas id="grafikKeuangan"></canvas>
                            </div>
                        </div>
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

    {{-- 4. TABEL KINERJA PRODI (Monospace & Dark Header Standar) --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Performa Program Studi
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th class="text-start" style="width: 30%;">PROGRAM STUDI</th>
                            <th class="text-start" style="width: 25%;">KEPALA PRODI</th>
                            <th style="width: 20%;">RASIO AKTIF</th>
                            <th style="width: 12%;">JML MAHASISWA</th>
                            <th style="width: 13%;">JML DOSEN</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($kinerjaProdi as $prodi)
                            @php
                                $percent = ($totalMahasiswaAktif > 0) ? ($prodi->jumlah_mahasiswa_aktif / $totalMahasiswaAktif) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="text-start uppercase fw-bold text-dark ps-3">
                                    {{ $prodi->nama_prodi }}
                                </td>
                                <td class="text-start uppercase text-muted">
                                    {{ $prodi->kaprodi->nama_lengkap ?? 'BELUM DITENTUKAN' }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center px-2">
                                        <div class="progress flex-grow-1 me-2 rounded-0" style="height: 8px;">
                                            <div class="progress-bar bg-dark" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="small font-monospace text-dark fw-bold">{{ round($percent, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="text-center font-monospace fw-bold text-dark">
                                    {{ $prodi->jumlah_mahasiswa_aktif }}
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    {{ $prodi->jumlah_dosen }}
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

    // Config Umum Chart dg font standar formal
    Chart.defaults.font.family = "'font-monospace', monospace";
    
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: { boxWidth: 12, font: { weight: 'bold' } }
            }
        }
    };

    // Grafik Keuangan (Bar - Flat Dark/Primary)
    new Chart(document.getElementById('grafikKeuangan').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'PENDAPATAN (RP)',
                data: keuanganData,
                backgroundColor: '#212529', // bg-dark solid
                borderColor: '#000000',
                borderWidth: 1,
                borderRadius: 0 // Siku tajam 0px
            }]
        },
        options: {
            ...commonOptions,
            scales: { 
                y: { 
                    beginAtZero: true, 
                    ticks: { callback: value => 'Rp ' + (value/1000000).toFixed(0) + 'Jt' } 
                } 
            }
        }
    });

    // Grafik Pertumbuhan (Line - Flat UI)
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
                    tension: 0 // Garis lurus patah/kaku (bukan kurva melengkung)
                },
                {
                    label: 'LULUSAN',
                    data: lulusanData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.05)',
                    fill: true,
                    tension: 0 // Garis lurus patah/kaku
                }
            ]
        },
        options: commonOptions
    });
});
</script>
@endpush