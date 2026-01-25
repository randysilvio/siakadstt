@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- === HEADER PROFILE === --}}
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-body p-4 bg-white d-flex align-items-center">
            {{-- Foto Profil --}}
            <div class="me-4 position-relative">
                @if($mahasiswa->foto_profil)
                    <img src="{{ asset('storage/' . $mahasiswa->foto_profil) }}" alt="Foto Profil" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; font-size: 2.5rem; font-weight: bold;">
                        {{ substr($mahasiswa->nama_lengkap, 0, 1) }}
                    </div>
                @endif
                <span class="position-absolute bottom-0 start-100 translate-middle p-2 bg-success border border-white rounded-circle" title="Status Aktif"></span>
            </div>

            {{-- Info Mahasiswa --}}
            <div class="flex-grow-1">
                <h2 class="mb-1 fw-bold text-dark">Selamat Datang, {{ explode(' ', $mahasiswa->nama_lengkap)[0] }}!</h2>
                <p class="text-muted mb-0">{{ $mahasiswa->nama_lengkap }}</p>
                <div class="d-flex align-items-center mt-2">
                    <span class="badge bg-primary me-2"><i class="bi bi-person-badge me-1"></i> {{ $mahasiswa->nim }}</span>
                    <span class="badge bg-light text-dark border"><i class="bi bi-mortarboard-fill me-1 text-secondary"></i> {{ $mahasiswa->programStudi->nama_prodi ?? 'Prodi Belum Diatur' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- KOLOM KIRI: STATISTIK & JADWAL --}}
        <div class="col-lg-8">
            
            {{-- Statistik Akademik --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="me-3 bg-white bg-opacity-25 rounded-3 p-3">
                                <i class="bi bi-award-fill fs-2"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-uppercase small opacity-75">Indeks Prestasi Kumulatif</h6>
                                <span class="fs-2 fw-bold">{{ number_format($ipk, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #198754, #157347);">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="me-3 bg-white bg-opacity-25 rounded-3 p-3">
                                <i class="bi bi-journal-bookmark-fill fs-2"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-uppercase small opacity-75">Total SKS Lulus</h6>
                                <span class="fs-2 fw-bold">{{ $total_sks }} SKS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Peringatan Keuangan --}}
            @if($memiliki_tagihan)
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                    <div class="bg-danger text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading fw-bold mb-0">Tagihan Belum Lunas</h6>
                        <small>Anda memiliki tagihan pembayaran yang perlu diselesaikan. <a href="{{ route('pembayaran.riwayat') }}" class="fw-bold text-danger text-decoration-underline">Cek Tagihan Sekarang</a></small>
                    </div>
                </div>
            @endif

            {{-- Jadwal Kuliah --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-calendar3-week me-2"></i>Jadwal Kuliah Semester Ini</h5>
                    
                    {{-- TOMBOL BARU: CETAK JADWAL KULIAH --}}
                    <a href="{{ route('jadwal.cetak') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-printer me-1"></i> Cetak Jadwal
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-4 py-3">Hari</th>
                                    <th>Waktu</th>
                                    <th>Mata Kuliah</th>
                                    <th class="text-center">SKS</th>
                                    <th>Dosen Pengampu</th>
                                    <th class="text-center pe-4">RPS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwalKuliah as $jadwal)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $jadwal->hari }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border fw-normal">
                                                <i class="bi bi-clock me-1 text-muted"></i>
                                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $jadwal->mataKuliah->nama_mk }}</div>
                                            <small class="text-muted d-block" style="font-size: 0.8rem;">
                                                <i class="bi bi-upc-scan me-1"></i> {{ $jadwal->mataKuliah->kode_mk }}
                                            </small>
                                        </td>
                                        <td class="text-center fw-bold">{{ $jadwal->mataKuliah->sks }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle text-secondary d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <span class="small">{{ $jadwal->mataKuliah->dosen->nama_lengkap ?? '-' }}</span>
                                            </div>
                                        </td>
                                        
                                        {{-- TOMBOL DOWNLOAD RPS --}}
                                        <td class="text-center pe-4">
                                            @if($jadwal->mataKuliah->file_rps)
                                                <a href="{{ asset('storage/' . $jadwal->mataKuliah->file_rps) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm" title="Download RPS (PDF)">
                                                    <i class="bi bi-file-earmark-pdf-fill"></i> <span class="d-none d-md-inline ms-1">RPS</span>
                                                </a>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal px-2 py-1" title="RPS belum diunggah dosen">
                                                    <i class="bi bi-dash-circle me-1"></i> N/A
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted opacity-50 mb-2">
                                                <i class="bi bi-calendar-x fs-1"></i>
                                            </div>
                                            <h6 class="fw-bold text-secondary">Tidak ada jadwal kuliah.</h6>
                                            <p class="small text-muted mb-0">Pastikan KRS Anda sudah disetujui dan periode akademik aktif.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: MENU & INFO --}}
        <div class="col-lg-4">
            
            {{-- Menu Cepat --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-grid-fill me-2 text-primary"></i>Akses Cepat</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3"><i class="bi bi-card-checklist fs-5"></i></div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark">Kartu Rencana Studi</h6>
                            <small class="text-muted">Isi atau revisi KRS semester ini</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    
                    <a href="{{ route('khs.index') }}" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3"><i class="bi bi-file-earmark-bar-graph fs-5"></i></div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark">Kartu Hasil Studi</h6>
                            <small class="text-muted">Lihat nilai per semester</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>

                    <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning rounded p-2 me-3"><i class="bi bi-trophy fs-5"></i></div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark">Transkrip Nilai</h6>
                            <small class="text-muted">Rekap nilai keseluruhan</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>

                    <a href="{{ route('evaluasi.index') }}" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 text-info rounded p-2 me-3"><i class="bi bi-chat-heart fs-5"></i></div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark">Evaluasi Dosen</h6>
                            <small class="text-muted">Berikan penilaian kinerja dosen</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                </div>
            </div>

            {{-- Pengumuman Terbaru --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-megaphone-fill me-2 text-danger"></i>Pengumuman</h6>
                    <a href="{{ route('berita.index') }}" class="text-decoration-none small fw-bold">Lihat Semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($pengumumans as $p)
                        <a href="{{ route('pengumuman.public.show', $p) }}" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex w-100 justify-content-between mb-1">
                                <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;">
                                    {{ $p->created_at->isoFormat('D MMM Y') }}
                                </span>
                                @if($p->created_at->diffInDays(now()) < 3)
                                    <span class="badge bg-danger rounded-pill" style="font-size: 0.6rem;">BARU</span>
                                @endif
                            </div>
                            <h6 class="mb-1 fw-bold text-dark text-truncate">{{ $p->judul }}</h6>
                            <p class="mb-0 small text-muted text-truncate">{{ strip_tags($p->konten) }}</p>
                        </a>
                    @empty
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" alt="Empty" width="48" class="opacity-25 mb-2">
                            <p class="text-muted small mb-0">Belum ada pengumuman terbaru.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection