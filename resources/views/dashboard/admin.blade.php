@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard Admin</h1>

    {{-- Kartu Statistik --}}
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Mahasiswa</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalMahasiswa }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Dosen</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalDosen }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Program Studi</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalProdi }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Total Mata Kuliah</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalMataKuliah }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Grafik Mahasiswa --}}
        <div class="col-md-7 mb-4">
            <div class="card">
                <div class="card-header">Distribusi Mahasiswa per Program Studi</div>
                <div class="card-body">
                    <canvas id="mahasiswaPerProdiChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Panel Pengumuman --}}
        <div class="col-md-5 mb-4">
            <div class="card">
                <div class="card-header">Pengumuman Terbaru</div>
                <div class="list-group list-group-flush">
                    @forelse($pengumumans as $pengumuman)
                        <a href="{{ route('pengumuman.show', $pengumuman) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $pengumuman->judul }}</h6>
                                <small>{{ $pengumuman->created_at->diffForHumans() }}</small>
                            </div>
                            <small>Target: {{ Str::ucfirst($pengumuman->target_role) }}</small>
                        </a>
                    @empty
                        <div class="list-group-item">Tidak ada pengumuman.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Library untuk Grafik --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetch('{{ route("dashboard.chart.mahasiswa-per-prodi") }}')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('mahasiswaPerProdiChart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie', // Tipe grafik: pie, bar, line, dll.
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Jumlah Mahasiswa',
                            data: data.values,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(255, 159, 64, 0.7)'
                            ],
                            borderColor: '#fff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: false,
                                text: 'Distribusi Mahasiswa'
                            }
                        }
                    }
                });
            });
    });
</script>
@endpush
