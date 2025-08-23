@extends('layouts.app')

@section('content')
<div class="container">
    {{-- ============================================================== --}}
    {{-- TAMPILAN DASHBOARD MAHASISWA --}}
    {{-- ============================================================== --}}
    @if(Auth::user()->hasRole('mahasiswa'))
        @if(isset($mahasiswa))
            <h2 class="mb-4">Dasbor Mahasiswa</h2>
            
            {{-- Notifikasi Tagihan --}}
            @if($memiliki_tagihan)
                <div class="alert alert-danger" role="alert">
                    <strong>Perhatian!</strong> Anda memiliki tagihan pembayaran yang belum lunas.
                    <a href="{{ route('pembayaran.riwayat') }}" class="alert-link">Lihat Riwayat Pembayaran</a> untuk melanjutkan proses akademik.
                </div>
            @endif

            <div class="row">
                <!-- Kolom Kiri: Profil & Jadwal -->
                <div class="col-lg-8">
                    {{-- Kartu Profil --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Selamat Datang, {{ $mahasiswa->nama_lengkap }}!</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
                                    <p class="mb-1"><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Dosen Wali:</strong> {{ $mahasiswa->dosenWali->nama_lengkap ?? 'Belum ditentukan' }}</p>
                                    <p class="mb-1"><strong>Status KRS:</strong> 
                                        <span class="badge 
                                            @if($mahasiswa->status_krs == 'Disetujui') bg-success
                                            @elseif($mahasiswa->status_krs == 'Ditolak') bg-danger
                                            @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan') bg-warning text-dark
                                            @else bg-secondary @endif">
                                            {{ $mahasiswa->status_krs ?? 'Belum Mengisi' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Jadwal Kuliah --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Jadwal Kuliah Semester Ini</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Hari</th>
                                            <th>Jam</th>
                                            <th>Mata Kuliah</th>
                                            <th>SKS</th>
                                            <th>Dosen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($jadwalKuliah as $jadwal)
                                            <tr>
                                                <td>{{ $jadwal->hari }}</td>
                                                <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                                <td>{{ $jadwal->mataKuliah->nama_mk }}</td>
                                                <td>{{ $jadwal->mataKuliah->sks }}</td>
                                                <td>{{ $jadwal->mataKuliah->dosen->nama_lengkap ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-3">Anda belum mengambil KRS untuk semester ini atau KRS belum disetujui.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Aksi Cepat, IPK, SKS & Pengumuman -->
                <div class="col-lg-4">
                    {{-- [KODE BARU DITAMBAHKAN] Kartu Aksi Cepat --}}
                    <div class="card mb-4">
                        <div class="card-header fw-bold">Aksi Cepat</div>
                        <div class="list-group list-group-flush">
                            {{-- Tombol ini hanya muncul jika periode KRS aktif dan KRS belum final --}}
                            @if(isset($periodeKrsAktif) && $periodeKrsAktif && $mahasiswa->status_krs !== 'Disetujui')
                                <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action list-group-item-primary">
                                    <strong>Isi / Ubah Kartu Rencana Studi (KRS)</strong>
                                    <small class="d-block text-muted">Periode KRS sedang berlangsung.</small>
                                </a>
                            @endif

                             {{-- Tombol ini hanya muncul jika periode evaluasi aktif --}}
                            @if(isset($periodeEvaluasiAktif) && $periodeEvaluasiAktif)
                                <a href="{{ route('evaluasi.index') }}" class="list-group-item list-group-item-action list-group-item-success">
                                    <strong>Isi Kuesioner Evaluasi Dosen</strong>
                                    <small class="d-block text-muted">Bantu tingkatkan kualitas pengajaran.</small>
                                </a>
                            @endif
                            
                            <a href="{{ route('khs.index') }}" class="list-group-item list-group-item-action">Lihat Kartu Hasil Studi (KHS)</a>
                            <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action">Lihat Transkrip Nilai</a>
                        </div>
                    </div>

                    {{-- Kartu IPK --}}
                    <div class="card text-center bg-primary text-white mb-4">
                        <div class="card-body">
                            <h5 class="card-title">IPK Kumulatif</h5>
                            <p class="card-text display-4 fw-bold">{{ number_format($ipk, 2) }}</p>
                        </div>
                    </div>
                    {{-- Kartu SKS --}}
                    <div class="card text-center bg-info text-white mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Total SKS Lulus</h5>
                            <p class="card-text display-4 fw-bold">{{ $total_sks }}</p>
                        </div>
                    </div>
                    {{-- Kartu Pengumuman --}}
                     <div class="card">
                        <div class="card-header">Pengumuman Terbaru</div>
                        <div class="list-group list-group-flush">
                            @forelse($pengumuman as $p)
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $p->judul }}</h6>
                                    </div>
                                    <small class="text-muted">{{ $p->created_at->isoFormat('D MMMM YYYY') }}</small>
                                </a>
                            @empty
                                <div class="list-group-item text-muted">Belum ada pengumuman.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Grafik Tren IP --}}
            <div class="row mt-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Grafik Tren Indeks Prestasi (IP) per Semester</h5>
                            <canvas id="grafikIPS"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @else
             <div class="alert alert-warning">Data mahasiswa Anda tidak ditemukan. Silakan hubungi administrator.</div>
        @endif
        <hr class="my-4">
    @endif

    {{-- ============================================================== --}}
    {{-- TAMPILAN DASHBOARD DOSEN (dan peran lainnya) --}}
    {{-- ============================================================== --}}
    @if(Auth::user()->hasRole('dosen'))
        <h2 class="mb-4">Dasbor Dosen</h2>
        {{-- ... (Konten dasbor dosen dapat ditambahkan di sini) ... --}}
    @endif
    
    @if(Auth::user()->hasRole('admin'))
        <h2 class="mb-4">Dasbor Administrator</h2>
        <p>Selamat datang, Administrator. Gunakan menu navigasi di atas untuk mengelola sistem.</p>
    @endif

    {{-- ... (Tambahkan blok untuk peran lain seperti pustakawan, keuangan, dll. jika perlu) ... --}}

</div>
@endsection

@push('scripts')
    {{-- Script ini hanya akan di-load jika user yang login adalah mahasiswa --}}
    @if(Auth::user()->hasRole('mahasiswa') && isset($dataGrafik))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const dataGrafik = @json($dataGrafik);
            const ctx = document.getElementById('grafikIPS').getContext('2d');
            
            if (dataGrafik.labels.length > 0) {
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
            } else {
                // Tampilkan pesan jika tidak ada data untuk grafik
                ctx.font = "16px Arial";
                ctx.fillStyle = "#888";
                ctx.textAlign = "center";
                ctx.fillText("Data IP Semester belum tersedia untuk ditampilkan.", ctx.canvas.width / 2, ctx.canvas.height / 2);
            }
        </script>
    @endif
@endpush
