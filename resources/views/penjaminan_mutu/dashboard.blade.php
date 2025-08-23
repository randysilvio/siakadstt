@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Dasbor Penjaminan Mutu & Akreditasi</h1>
            
            <!-- UPDATE 1: KARTU INDIKATOR KINERJA UTAMA (KPI) -->
            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="card-title">Mahasiswa Aktif</h5>
                            <p class="card-text fs-1 fw-bold text-primary">{{ $jumlahMahasiswaAktif }}</p>
                        </div>
                        <div class="card-footer text-muted">Total mahasiswa terdaftar</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="card-title">Jumlah Dosen</h5>
                            <p class="card-text fs-1 fw-bold text-success">{{ $jumlahDosen }}</p>
                        </div>
                         <div class="card-footer text-muted">Total dosen pengajar</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="card-title">Rasio Dosen:Mahasiswa</h5>
                            <p class="card-text fs-1 fw-bold text-info">{{ $rasioDosenMahasiswa }}</p>
                        </div>
                         <div class="card-footer text-muted">Metrik penting akreditasi</div>
                    </div>
                </div>
            </div>

            <!-- UPDATE 2: VISUALISASI DATA DENGAN GRAFIK -->
            <div class="row mt-4">
                <div class="col-lg-7 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Tren Mahasiswa Aktif per Angkatan</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="trenMahasiswaChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 mb-4">
                     <div class="card">
                        <div class="card-header">
                            <h5>Distribusi Status Mahasiswa</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="distribusiStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UPDATE 3: LAPORAN RINGKAS HASIL EVALUASI DOSEN (EDOM) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Ringkasan Kinerja Dosen Berdasarkan Evaluasi Mahasiswa (EDOM)</h5>
                            @if($sesiEdomAktif)
                                <small class="text-muted">Menampilkan data untuk sesi: {{ $sesiEdomAktif->nama_sesi }}</small>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($sesiEdomAktif && $hasilEdom->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Peringkat</th>
                                                <th>Nama Dosen</th>
                                                <th>Skor Rata-rata (dari 5)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hasilEdom as $index => $hasil)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $hasil->nama_lengkap }}</td>
                                                    <td>
                                                        <strong class="text-warning">{{ number_format($hasil->rata_rata_skor, 2) }}</strong>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning text-center">
                                    Tidak ada data evaluasi yang aktif atau belum ada mahasiswa yang mengisi kuesioner.
                                </div>
                            @endif
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
    const trenMahasiswaLabels = @json($trenMahasiswaLabels);
    const trenMahasiswaTotals = @json($trenMahasiswaTotals);
    const distribusiStatusLabels = @json($distribusiStatusLabels);
    const distribusiStatusTotals = @json($distribusiStatusTotals);

    // Grafik 1: Tren Mahasiswa
    const ctxTren = document.getElementById('trenMahasiswaChart').getContext('2d');
    new Chart(ctxTren, {
        type: 'bar',
        data: {
            labels: trenMahasiswaLabels,
            datasets: [{
                label: 'Jumlah Mahasiswa Aktif',
                data: trenMahasiswaTotals,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Jumlah: ${context.raw} mahasiswa`;
                        }
                    }
                }
            }
        }
    });

    // Grafik 2: Distribusi Status Mahasiswa
    const ctxDistribusi = document.getElementById('distribusiStatusChart').getContext('2d');
    new Chart(ctxDistribusi, {
        type: 'pie',
        data: {
            labels: distribusiStatusLabels,
            datasets: [{
                label: 'Distribusi Status',
                data: distribusiStatusTotals,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.6)', // Aktif
                    'rgba(255, 206, 86, 0.6)', // Cuti
                    'rgba(153, 102, 255, 0.6)', // Lulus
                    'rgba(255, 99, 132, 0.6)', // Drop Out
                    'rgba(201, 203, 207, 0.6)'  // Non-Aktif
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endpush
