@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Tampilan Dashboard Mahasiswa --}}
    @if(Auth::user()->role == 'mahasiswa')
        <h1 class="mb-4">Dashboard Mahasiswa</h1>
        @if($memiliki_tagihan)
            <div class="alert alert-danger" role="alert">
                <strong>Perhatian!</strong> Anda memiliki tagihan pembayaran yang belum lunas.
                <a href="{{ route('pembayaran.riwayat') }}" class="alert-link">Lihat Riwayat Pembayaran</a>.
            </div>
        @endif
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Profil Ringkas</h5>
                        <p><strong>Nama:</strong> {{ $mahasiswa->nama_lengkap }}</p>
                        <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
                        <p><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-primary text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">IPK Kumulatif</h5>
                        <p class="card-text fs-2">{{ $ipk }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-info text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total SKS</h5>
                        <p class="card-text fs-2">{{ $total_sks }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Grafik Tren Indeks Prestasi (IP) per Semester</h5>
                        <canvas id="grafikIPS"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <hr>
    @endif

    {{-- Tampilan Dashboard Dosen --}}
    @if(Auth::user()->role == 'dosen')
        <h1 class="mb-4">Dashboard Dosen</h1>
        <div class="row">
            <div class="col-md-8">
                {{-- Portal Kaprodi --}}
                @if($prodiYangDikepalai)
                    <div class="card bg-primary text-white mb-3">
                        <div class="card-body">
                            <h5>Portal Kepala Program Studi: {{ $prodiYangDikepalai->nama_prodi }}</h5>
                            <p>Anda memiliki akses sebagai Kaprodi.</p>
                            <a href="{{ route('kaprodi.dashboard') }}" class="btn btn-light">Masuk ke Portal Kaprodi</a>
                        </div>
                    </div>
                @endif

                <div class="card mb-3">
                    <div class="card-header">Mata Kuliah yang Anda Ajar</div>
                    <ul class="list-group list-group-flush">
                        @forelse($mata_kuliahs as $mk)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $mk->kode_mk }}</strong> - {{ $mk->nama_mk }} <br>
                                    <small>{{ $mk->sks }} SKS - {{ $mk->semester }}</small>
                                </div>
                                <a href="{{ route('nilai.show', $mk) }}" class="badge bg-primary rounded-pill">Input Nilai</a>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">Anda tidak mengajar mata kuliah apapun.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Perwalian</h5>
                        <p class="card-text fs-2">{{ $jumlahMahasiswaWali }}</p>
                        <a href="{{ route('perwalian.index') }}" class="btn btn-primary">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>
        <hr>
    @endif

    {{-- Pengumuman Terbaru --}}
    {{-- Saya memperbaiki variabel $pengumuman->count() menjadi $pengumuman->isNotEmpty() atau count($pengumuman) > 0 --}}
    @if(isset($pengumuman) && $pengumuman->isNotEmpty())
        <div class="card mt-4">
            <div class="card-header">
                <h3>Pengumuman Terbaru</h3>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($pengumuman as $p)
                    <li class="list-group-item">
                        <h5 class="mb-1">{{ $p->judul }}</h5>
                        <p class="mb-1">{{ $p->isi }}</p>
                        <small class="text-muted">Diterbitkan: {{ $p->created_at->isoFormat('D MMMM YYYY') }}</small>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="mt-4 alert alert-info">
            Belum ada pengumuman untuk Anda saat ini.
        </div>
    @endif
</div>
@endsection

@push('scripts')
    {{-- Script ini hanya akan di-load jika user yang login adalah mahasiswa --}}
    @if(Auth::user()->role == 'mahasiswa' && isset($dataGrafik))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const dataGrafik = @json($dataGrafik);
            const ctx = document.getElementById('grafikIPS').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dataGrafik.labels,
                    datasets: [{
                        label: 'IP Semester',
                        data: dataGrafik.data,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4.0,
                            title: {
                                display: true,
                                text: 'Nilai IP'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Semester'
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endpush