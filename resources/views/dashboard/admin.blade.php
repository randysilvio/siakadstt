@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard Admin</h1>

    {{-- Kartu Statistik Utama --}}
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
                    <h5 class="card-title">Total Prodi</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalProdi }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Total Mata Kuliah</h5>
                    <p class="card-text fs-4 fw-bold">{{ $totalMatkul }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Manajemen Konten Publik --}}
    <h2 class="h4 mb-3">Manajemen Konten Publik</h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Slideshow</h5>
                    <p class="card-text">Atur gambar yang tampil di halaman depan.</p>
                    {{-- PERBAIKAN: Menggunakan nama rute admin --}}
                    <a href="{{ route('admin.slideshows.index') }}" class="btn btn-outline-primary">Kelola Slideshow</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Dokumen Publik</h5>
                    <p class="card-text">Unggah dan kelola dokumen unduhan.</p>
                    {{-- PERBAIKAN: Menggunakan nama rute admin --}}
                    <a href="{{ route('admin.dokumen-publik.index') }}" class="btn btn-outline-primary">Kelola Dokumen</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Pengumuman</h5>
                    <p class="card-text">Buat dan kelola pengumuman untuk user.</p>
                    {{-- PERBAIKAN: Menggunakan nama rute admin --}}
                    <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-primary">Kelola Pengumuman</a>
                </div>
            </div>
        </div>
    </div>
    <hr class="my-4">

    {{-- Grafik dan Pengumuman Terbaru --}}
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    Grafik Mahasiswa per Program Studi
                </div>
                <div class="card-body">
                    <canvas id="grafikMahasiswaProdi"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Pengumuman Terbaru
                    {{-- PERBAIKAN: Menggunakan nama rute admin --}}
                    <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-sm btn-outline-success">+ Buat Baru</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($pengumumans as $pengumuman)
                        {{-- PERBAIKAN: Menggunakan nama rute admin --}}
                        <a href="{{ route('admin.pengumuman.show', $pengumuman->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $pengumuman->judul }}</h6>
                                <small>{{ $pengumuman->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small text-muted">{{ Str::limit(strip_tags($pengumuman->konten ?? 'Konten tidak tersedia.'), 80) }}</p>
                        </a>
                    @empty
                        <div class="list-group-item">Tidak ada pengumuman.</div>
                    @endforelse
                </div>
                <div class="card-footer text-center">
                    {{-- PERBAIKAN: Menggunakan nama rute admin --}}
                    <a href="{{ route('admin.pengumuman.index') }}">Kelola semua pengumuman</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // PERBAIKAN: Kode JavaScript untuk grafik diaktifkan kembali
        const dataGrafik = @json($dataGrafikProdi);

        const ctx = document.getElementById('grafikMahasiswaProdi').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut', // Tipe grafik bisa 'pie', 'bar', 'line', dll.
            data: {
                labels: dataGrafik.labels,
                datasets: [{
                    label: 'Jumlah Mahasiswa',
                    data: dataGrafik.data,
                    backgroundColor: [ // Anda bisa menambahkan lebih banyak warna
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Distribusi Mahasiswa per Program Studi'
                    }
                }
            }
        });
    </script>
@endpush