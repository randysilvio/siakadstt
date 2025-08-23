@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Dasbor Eksekutif Rektorat</h1>

            <div class="alert alert-success" role="alert">
                <h4 class="alert-heading">Selamat Datang, Pimpinan!</h4>
                <p>Halaman ini dirancang untuk memberikan Anda gambaran strategis (helicopter view) mengenai Indikator Kinerja Utama (KPI) institusi secara ringkas dan real-time.</p>
            </div>

            <!-- 1. KARTU INDIKATOR KINERJA UTAMA (KPI) -->
            <div class="row mt-4">
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="card-title">Total Mahasiswa Aktif</h5>
                            <p class="card-text fs-1 fw-bold text-primary">{{ $totalMahasiswaAktif }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="card-title">Pendaftar Tahun Ini</h5>
                            <p class="card-text fs-1 fw-bold text-info">{{ $pendaftarTahunIni }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="card-title">Pendapatan Semester Ini</h5>
                            <p class="card-text fs-1 fw-bold text-success">Rp{{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="card-title">Mahasiswa Lulus Tahun Ini</h5>
                            <p class="card-text fs-1 fw-bold text-warning">{{ $mahasiswaLulusTahunIni }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. VISUALISASI TREN JANGKA PANJANG -->
            <div class="row mt-4">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header"><h5>Tren Keuangan (5 Tahun Terakhir)</h5></div>
                        <div class="card-body"><canvas id="grafikKeuangan"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header"><h5>Pertumbuhan Mahasiswa (5 Tahun Terakhir)</h5></div>
                        <div class="card-body"><canvas id="grafikPertumbuhan"></canvas></div>
                    </div>
                </div>
            </div>

            <!-- 3. TABEL RINGKASAN KINERJA PROGRAM STUDI -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h5>Ringkasan Kinerja Program Studi</h5></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Program Studi</th>
                                            <th>Kaprodi</th>
                                            <th>Jumlah Mahasiswa Aktif</th>
                                            <th>Jumlah Dosen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($kinerjaProdi as $prodi)
                                            <tr>
                                                <td><strong>{{ $prodi->nama_prodi }}</strong></td>
                                                <td>{{ $prodi->kaprodi->nama_lengkap ?? 'N/A' }}</td>
                                                <td>{{ $prodi->jumlah_mahasiswa_aktif }}</td>
                                                <td>{{ $prodi->jumlah_dosen }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">Tidak ada data program studi.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- CDN untuk Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data dari Controller
    const labels = @json($grafikLabels);
    const keuanganData = @json($grafikKeuanganData);
    const mahasiswaBaruData = @json($grafikMahasiswaBaruData);
    const lulusanData = @json($grafikLulusanData);

    // Grafik Keuangan
    new Chart(document.getElementById('grafikKeuangan').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Pendapatan (Rp)',
                data: keuanganData,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { callback: value => 'Rp' + new Intl.NumberFormat('id-ID').format(value) } } }
        }
    });

    // Grafik Pertumbuhan Mahasiswa
    new Chart(document.getElementById('grafikPertumbuhan').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Mahasiswa Baru',
                    data: mahasiswaBaruData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Mahasiswa Lulus',
                    data: lulusanData,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    fill: true,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endpush
