@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Dashboard Admin</h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Mahasiswa</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalMahasiswa }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Program Studi</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalProdi }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Total Mata Kuliah</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalMataKuliah }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            Grafik Jumlah Mahasiswa per Program Studi
        </div>
        <div class="card-body">
            <canvas id="mahasiswaChart"></canvas>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">Pengumuman Terbaru</div>
        <div class="card-body">
            @forelse($pengumumans as $pengumuman)
                <h5>{{ $pengumuman->judul }}</h5>
                <p>{!! nl2br(e($pengumuman->konten)) !!}</p>
                <small class="text-muted">Diposting pada {{ $pengumuman->created_at->format('d M Y') }} untuk {{ ucfirst($pengumuman->target_role) }}</small>
                @if(!$loop->last) <hr> @endif
            @empty
                <p>Tidak ada pengumuman saat ini.</p>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('mahasiswaChart');
        fetch('/api/stats/mahasiswa-per-prodi', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'), // Jika menggunakan token
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: '# Jumlah Mahasiswa',
                        data: data.values,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    });
</script>
@endpush