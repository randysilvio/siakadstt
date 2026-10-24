<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Informasi Akademik - STT GPI Papua</title>
    
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Figtree', sans-serif; background-color: #f8fafc; color: #212529; }
        .navbar-custom { background-color: #ffffff; border-bottom: 2px solid #212529; padding-top: 0.8rem; padding-bottom: 0.8rem; }
        .brand-text { font-family: 'Raleway', sans-serif; font-weight: 800; color: #212529; letter-spacing: -0.01em; line-height: 1; }
        .brand-sub { font-size: 0.7rem; font-weight: 700; color: #495057; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 2px; display: block; }
        .navbar-nav .nav-link { color: #495057; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; padding: 0.5rem 0.65rem !important; transition: all 0.2s; border-radius: 0 !important; }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active, .navbar-nav .show > .nav-link { color: #ffffff; background-color: #212529; }
        .navbar-nav .nav-link i { margin-right: 5px; color: inherit; }
        .dropdown-toggle::after { display: none !important; }
        .dropdown-menu { border: 1px solid #212529; box-shadow: none; border-radius: 0; padding: 0; margin-top: 0; }
        .dropdown-item { font-size: 0.85rem; text-transform: uppercase; color: #212529; padding: 0.6rem 1rem; border-radius: 0; font-weight: 600; border-bottom: 1px solid #e9ecef; }
        .dropdown-item:last-child { border-bottom: none; }
        .dropdown-item:hover { background-color: #212529; color: #ffffff; }
        .dropdown-header { color: #6c757d; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; padding: 0.6rem 1rem; background-color: #f8f9fa; border-bottom: 1px solid #e9ecef; margin: 0; }
        .dropdown-divider { border-color: #212529; margin: 0; }
        .user-avatar { width: 32px; height: 32px; background-color: #212529; color: #ffffff; border-radius: 0; display: flex; align-items: center; justify-content: center; font-weight: bold; font-family: monospace; font-size: 1rem; }
        .user-name-truncate { max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block; vertical-align: middle; }
        .footer { background-color: #ffffff; border-top: 2px solid #212529; color: #212529; }
        @keyframes pulse-dot { 0% { transform: scale(0.95); opacity: 0.8; } 50% { transform: scale(1.2); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.8; } }
        .online-dot { animation: pulse-dot 2s infinite ease-in-out; }
        .bg-light-new { background-color: #e9ecef !important; }
        .cmd-item:hover { background-color: #212529 !important; color: #ffffff !important; }
        .cmd-item:hover .text-muted, .cmd-item:hover .text-dark { color: #e9ecef !important; }
    </style>

    @if(request()->routeIs('berita.index') || request()->routeIs('dosen.public.*') || request()->routeIs('pengumuman.public.*'))
        @vite('resources/css/app.css')
    @endif
    
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    @if(session()->has('impersonate_by'))
        <div class="bg-danger text-white text-center py-2 fw-bold uppercase small shadow-sm" style="position: sticky; top: 0; z-index: 1050;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Mode Penyamaran: Anda sedang mengendalikan akun <u class="font-monospace">{{ Auth::user()->name }}</u>.
            <a href="{{ route('impersonate.stop') }}" class="btn btn-sm btn-dark text-white fw-bold ms-3 rounded-0 px-3 shadow-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Kembali ke Admin
            </a>
        </div>
    @endif

    <nav class="navbar navbar-expand-xl navbar-light navbar-custom sticky-top shadow-sm">
        <div class="container-fluid px-3 px-xl-5">
            <a class="navbar-brand d-flex align-items-center gap-3" href="{{ auth()->check() ? route('dashboard') : '/' }}">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" width="42" height="42" class="d-inline-block">
                <div class="d-flex flex-column justify-content-center">
                    <span class="brand-text fs-5">STT GPI PAPUA</span>
                    <span class="brand-sub">Sistem Informasi Akademik</span>
                </div>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-0">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kalender.*') ? 'active' : '' }}" href="{{ route('kalender.halaman') }}">
                                <i class="bi bi-calendar-event"></i>Kalender
                            </a>
                        </li>

                        @if(Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/pmb*', 'admin/mahasiswa*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-people-fill"></i>Mahasiswa & PMB
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Penerimaan Baru (PMB)</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pmb.index') }}">Data Pendaftar</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pmb-periods.index') }}">Setting Gelombang</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Data Mahasiswa</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.mahasiswa.index') }}">Manajemen Mahasiswa</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/tahun-akademik*', 'admin/program-studi*', 'admin/mata-kuliah*', 'admin/kurikulum*', 'admin/nilai*', 'admin/kalender*', 'bimbingan-akademik*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-mortarboard-fill"></i>Akademik
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Master Data</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tahun-akademik.index') }}">Tahun Akademik</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.program-studi.index') }}">Program Studi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.kurikulum.index') }}">Kurikulum</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.mata-kuliah.index') }}">Mata Kuliah</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Operasional</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.nilai.index') }}">Input Nilai Manual</a></li>
                                    <li><a class="dropdown-item" href="{{ route('bimbingan.index') }}">Bimbingan Skripsi & PPL</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.kalender.index') }}">Event Kalender</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/dosen*', 'admin/tendik*', 'admin/user*', 'admin/absensi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-badge-fill"></i>SDM & User
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Personalia</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dosen.index') }}">Data Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tendik.index') }}">Data Pegawai (Tendik)</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.user.index') }}">Akun User System</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Absensi Pegawai</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.laporan.index') }}">Laporan Kehadiran</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.lokasi.index') }}">Lokasi Kantor</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.pengaturan.index') }}">Setting Jam Kerja</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('administrasi_umum'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/pengumuman*', 'admin/slideshows*', 'admin/dokumen-publik*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-globe"></i>Website
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Manajemen Konten</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pengumuman.index') }}">Pengumuman</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.slideshows.index') }}">Slideshow Depan</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dokumen-publik.index') }}">Dokumen Publik</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('penjaminan_mutu'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/evaluasi*', 'admin/tracer-study*', 'admin/pengaturan*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear-wide-connected"></i>Pengaturan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Penjaminan Mutu</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-sesi.index') }}">Sesi Evaluasi Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-pertanyaan.index') }}">Bank Pertanyaan</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-hasil.index') }}">Laporan Hasil Evaluasi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracer.index') }}">Rekap Tracer Study Alumni</a></li>
                                    @if(Auth::user()->hasRole('admin'))
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Konfigurasi Global</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.pengaturan.index') }}">Pengaturan Sistem</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('penjaminan_mutu'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('penjaminan-mutu/dashboard') ? 'active' : '' }}" href="{{ route('mutu.dashboard') }}">
                                    <i class="bi bi-shield-check"></i>Dashboard Mutu
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('penjaminan-mutu/laporan*') ? 'active' : '' }}" href="{{ route('mutu.laporan.index') }}">
                                    <i class="bi bi-file-earmark-bar-graph"></i>Laporan
                                </a>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('administrasi_umum'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('administrasi*', 'admin/layanan-surat-mahasiswa*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-envelope-paper"></i>Administrasi
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">E-Office & Persuratan</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('administrasi.surat-keputusan.create') }}">Buat Surat / SK Baru</a></li>
                                    <li><a class="dropdown-item" href="{{ route('administrasi.surat-keputusan.index') }}">Arsip Dokumen SK</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Layanan Akademik</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.surat-mandiri.index') }}">Layanan Surat Mahasiswa</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('dosen') || (Auth::user()->hasRole('mahasiswa') && optional(Auth::user()->mahasiswa)->status_mahasiswa !== 'Lulus'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('verum.*') ? 'active' : '' }}" href="{{ route('verum.index') }}">
                                    <i class="bi bi-laptop"></i>E-Learning
                                </a>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('dosen') && !Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perwalian*', 'kaprodi*', 'dosen*', 'bimbingan-akademik*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-video3"></i>Dosen
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('dosen.dashboard') }}">Dashboard Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('dosen.tridharma.index') }}">Portofolio Tridharma (BKD)</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perwalian.index') }}">Tutorship Mahasiswa</a></li>
                                    <li><a class="dropdown-item" href="{{ route('bimbingan.index') }}">Bimbingan Skripsi & PPL</a></li>
                                    @if(Auth::user()->isKaprodi() || Auth::user()->hasRole('kaprodi'))
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Kaprodi Area</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('kaprodi.dashboard') }}">Dashboard Kaprodi</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('mahasiswa'))
                            @if(optional(Auth::user()->mahasiswa)->status_mahasiswa === 'Lulus')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('transkrip*', 'pembayaran*', 'tracer-study*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                        <i class="bi bi-award-fill"></i>Alumni
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><h6 class="dropdown-header">Arsip Kelulusan</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('transkrip.index') }}">Transkrip Nilai Akhir</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pembayaran.riwayat') }}">Riwayat Pembayaran</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Pusat Karir</h6></li>
                                        <li><a class="dropdown-item text-primary fw-bold" href="{{ route('tracer.index') }}">Tracer Study Alumni</a></li>
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('krs*', 'khs*', 'transkrip*', 'pembayaran*', 'evaluasi*', 'bimbingan-akademik*', 'layanan-surat*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                        <i class="bi bi-mortarboard"></i>Akademik
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><h6 class="dropdown-header">Studi & Tugas Akhir</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('krs.index') }}">KRS Studi</a></li>
                                        <li><a class="dropdown-item" href="{{ route('khs.index') }}">KHS Hasil</a></li>
                                        <li><a class="dropdown-item" href="{{ route('transkrip.index') }}">Transkrip Nilai</a></li>
                                        <li><a class="dropdown-item" href="{{ route('bimbingan.index') }}">Bimbingan Skripsi & PPL</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Administrasi</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('pembayaran.riwayat') }}">Riwayat Pembayaran</a></li>
                                        <li><a class="dropdown-item" href="{{ route('surat-mandiri.index') }}">Pengajuan Surat Mandiri</a></li>
                                        <li><a class="dropdown-item" href="{{ route('evaluasi.index') }}">Evaluasi Dosen</a></li>
                                    </ul>
                                </li>
                            @endif
                        @endif

                        @if (Auth::user()->hasRole('pustakawan'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perpustakaan*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-book"></i>Perpus
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.index') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.koleksi.index') }}">Koleksi Buku</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.peminjaman.index') }}">Sirkulasi</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('keuangan'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('pembayaran*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                                    <i class="bi bi-wallet2"></i>Keuangan
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-center gap-1 ps-lg-3">
                    @auth
                        @if(!Auth::user()->hasRole('mahasiswa') && !Auth::user()->hasRole('camaba'))
                            <li class="nav-item me-1">
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-2 py-1 d-flex align-items-center gap-2 font-monospace fw-bold" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#globalSearchModal" title="Pencarian Kilat Lintas Modul (Ctrl + K)">
                                    <i class="bi bi-search"></i>
                                    <span class="d-none d-xxl-inline">CARI</span>
                                    <span class="badge bg-dark text-white rounded-0" style="font-size: 0.6rem;">CTRL+K</span>
                                </button>
                            </li>
                        @endif

                        <li class="nav-item me-1 d-none d-lg-block">
                            <div class="bg-light border border-dark rounded-0 px-2 py-1 d-flex align-items-center shadow-none" title="Jumlah Pengguna Daring Saat Ini">
                                <i class="bi bi-circle-fill text-success online-dot me-2" style="font-size: 0.55rem;"></i>
                                <span class="small font-monospace uppercase fw-bold text-dark" style="font-size: 0.75rem;">
                                    <strong class="text-success font-monospace">{{ $onlineUsersCount ?? 1 }}</strong> ON
                                </span>
                            </div>
                        </li>

                        {{-- Dropdown dan Badge Notifikasi --}}
                        <li class="nav-item dropdown mx-1">
                            <a class="nav-link position-relative px-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5 text-secondary"></i>
                                <span id="notifBadge" class="position-absolute top-25 start-75 translate-middle badge rounded-0 bg-danger {{ Auth::user()->unreadNotifications->count() > 0 ? '' : 'd-none' }}" style="font-size: 0.65rem;">
                                    {{ Auth::user()->unreadNotifications->count() > 0 ? Auth::user()->unreadNotifications->count() : '0' }}
                                </span>
                            </a>
                            
                            <ul id="notifList" class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="width: 320px; max-height: 400px; overflow-y: auto;">
                                <li id="notifHeader">
                                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                                        <span class="fw-bold uppercase small">Notifikasi</span>
                                        @if(Auth::user()->unreadNotifications->count() > 0)
                                            <form action="{{ route('notifikasi.baca-semua') }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-dark rounded-0 py-0 px-2 uppercase fw-bold" style="font-size: 0.7rem;">Tandai Dibaca</button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                                
                                @forelse(Auth::user()->unreadNotifications->take(5) as $notification)
                                    <li>
                                        <a class="dropdown-item py-3 border-bottom text-wrap" href="{{ route('notifikasi.baca', $notification->id) }}" style="white-space: normal;">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="{{ $notification->data['icon'] ?? 'bi-info-circle-fill text-dark' }} fs-4"></i>
                                                <div>
                                                    <div class="fw-bold uppercase" style="font-size: 0.85rem; color: #212529;">{{ $notification->data['title'] ?? 'Notifikasi' }}</div>
                                                    <div class="text-muted" style="font-size: 0.8rem;">{{ $notification->data['message'] ?? '' }}</div>
                                                    <div class="text-muted font-monospace mt-1" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li id="emptyNotifMsg">
                                        <div class="px-3 py-4 text-center text-muted">
                                            <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                            <span class="small uppercase fw-bold">Belum ada notifikasi baru.</span>
                                        </div>
                                    </li>
                                @endforelse

                                <li><hr class="dropdown-divider m-0 border-secondary opacity-25"></li>
                                <li>
                                    <a href="{{ route('notifikasi.index') }}" class="dropdown-item text-center py-2 text-primary fw-bold uppercase bg-light" style="font-size: 0.75rem;">
                                        Lihat Semua Riwayat <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown ms-lg-1">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="user-avatar">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="d-none d-lg-inline fw-bold uppercase user-name-truncate" style="font-size: 0.85rem;">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <div class="px-3 py-2 text-center border-bottom mb-2 bg-light">
                                        <div class="small text-muted uppercase">Login Sebagai</div>
                                        <div class="fw-bold uppercase text-dark" style="font-size: 0.85rem;">{{ Auth::user()->roles->first()->name ?? 'USER' }}</div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>Edit Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger fw-bold"><i class="bi bi-box-arrow-right me-2"></i>LOGOUT</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn btn-sm btn-dark fw-bold px-4 py-2 rounded-0 uppercase shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login Area
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4 mb-5">
        @yield('content')
    </main>

    <footer class="footer mt-auto py-4">
        <div class="container text-center">
            <p class="mb-0 small uppercase">&copy; {{ date('Y') }} <strong>STT GPI Papua</strong>. Sistem Informasi Akademik.</p>
        </div>
    </footer>

    @include('partials.chatbot')

    {{-- MODAL GLOBAL QUICK SEARCH (COMMAND PALETTE CTRL + K) --}}
    @auth
    <div class="modal fade" id="globalSearchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-0 border-0 border-top border-dark border-4 shadow-lg">
                <div class="modal-header bg-dark text-white rounded-0 py-2 px-3">
                    <span class="fw-bold uppercase small font-monospace"><i class="bi bi-terminal-fill me-2"></i>Pencarian Kilat SIAKAD (Command Palette)</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 bg-light">
                    <div class="input-group input-group-lg mb-2">
                        <span class="input-group-text bg-white rounded-0 border-dark"><i class="bi bi-search text-dark"></i></span>
                        <input type="text" id="globalSearchInput" class="form-control rounded-0 border-dark font-monospace uppercase" placeholder="KETIK NIM, NAMA MAHASISWA, DOSEN, KODE MK, ATAU NOMOR SK..." autocomplete="off">
                    </div>
                    <div id="globalSearchResults" class="list-group rounded-0 border-0" style="max-height: 380px; overflow-y: auto;">
                        <div class="text-center py-4 text-muted small font-monospace uppercase">
                            Ketik minimal 2 karakter untuk mencari cepat lintas modul...
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2 px-3 border-top rounded-0 d-flex justify-content-between small font-monospace text-muted" style="font-size: 11px;">
                    <span>PINTASAN KEYBOARD: <strong>CTRL + K</strong></span>
                    <span>TEKAN <strong>ESC</strong> UNTUK MENUTUP</span>
                </div>
            </div>
        </div>
    </div>
    @endauth

    {{-- WADAH CUSTOM PUSH NOTIFICATION (BOOTSTRAP TOAST) --}}
    <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 1090; margin-top: 60px;">
        <div id="fcmToast" class="toast align-items-center text-bg-dark border-start border-success border-5 shadow-lg rounded-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-dark text-white border-bottom border-secondary rounded-0 p-3">
                <i class="bi bi-bell-fill text-success me-2 blink"></i>
                <strong class="me-auto uppercase fw-bold" id="fcmToastTitle" style="font-size: 0.8rem; letter-spacing: 1px;">Pesan Baru</strong>
                <small class="text-white-50 font-monospace" style="font-size: 0.65rem;">BARU SAJA</small>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body p-3 text-light uppercase small" id="fcmToastBody" style="line-height: 1.5;">
                Isi pesan notifikasi akan muncul di sini.
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>

    {{-- SCRIPT GLOBAL ANTI-SILENT ERROR PADA FORM MULTI-TAB & COMMAND PALETTE (CTRL+K) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const firstInvalid = document.querySelector('.tab-pane .is-invalid');
            if (firstInvalid) {
                const pane = firstInvalid.closest('.tab-pane');
                if (pane && pane.id) {
                    const tabBtn = document.querySelector('[data-bs-target="#' + pane.id + '"], [href="#' + pane.id + '"]');
                    if (tabBtn) {
                        new bootstrap.Tab(tabBtn).show();
                    }
                }
                firstInvalid.focus();
            }

            document.querySelectorAll('form').forEach(function (form) {
                form.querySelectorAll('input, select, textarea').forEach(function (input) {
                    input.addEventListener('invalid', function () {
                        const pane = input.closest('.tab-pane');
                        if (pane && !pane.classList.contains('active') && pane.id) {
                            const tabBtn = document.querySelector('[data-bs-target="#' + pane.id + '"], [href="#' + pane.id + '"]');
                            if (tabBtn) {
                                new bootstrap.Tab(tabBtn).show();
                            }
                        }
                    });
                });
            });

            const searchModalEl = document.getElementById('globalSearchModal');
            const searchInput = document.getElementById('globalSearchInput');
            const searchResults = document.getElementById('globalSearchResults');
            let searchTimeout = null;

            if (searchModalEl && searchInput) {
                const bsSearchModal = new bootstrap.Modal(searchModalEl);

                document.addEventListener('keydown', function (e) {
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                        e.preventDefault();
                        bsSearchModal.show();
                    }
                });

                searchModalEl.addEventListener('shown.bs.modal', function () {
                    searchInput.focus();
                    searchInput.select();
                });

                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimeout);
                    const q = this.value.trim();
                    if (q.length < 2) {
                        searchResults.innerHTML = '<div class="text-center py-4 text-muted small font-monospace uppercase">Ketik minimal 2 karakter untuk mencari cepat lintas modul...</div>';
                        return;
                    }

                    searchResults.innerHTML = '<div class="text-center py-4 text-muted small font-monospace uppercase">Mencari data...</div>';

                    searchTimeout = setTimeout(function () {
                        fetch('{{ route("global.search") }}?q=' + encodeURIComponent(q), {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            if (!data.results || data.results.length === 0) {
                                searchResults.innerHTML = '<div class="text-center py-4 text-muted small font-monospace uppercase">Tidak ditemukan hasil yang cocok.</div>';
                                return;
                            }

                            data.results.forEach(item => {
                                const a = document.createElement('a');
                                a.href = item.url;
                                a.className = 'list-group-item list-group-item-action rounded-0 border-bottom py-2 px-3 cmd-item d-flex align-items-center justify-content-between';

                                const left = document.createElement('div');
                                const badge = document.createElement('span');
                                badge.className = 'badge bg-dark text-white rounded-0 font-monospace me-2';
                                badge.style.fontSize = '9px';
                                badge.textContent = item.category;

                                const title = document.createElement('strong');
                                title.className = 'text-dark uppercase small';
                                title.textContent = item.title;

                                const sub = document.createElement('div');
                                sub.className = 'text-muted font-monospace mt-1';
                                sub.style.fontSize = '10px';
                                sub.textContent = item.subtitle;

                                left.appendChild(badge);
                                left.appendChild(title);
                                left.appendChild(sub);

                                const right = document.createElement('i');
                                right.className = 'bi bi-arrow-return-left text-muted';

                                a.appendChild(left);
                                a.appendChild(right);
                                searchResults.appendChild(a);
                            });
                        })
                        .catch(() => {
                            searchResults.innerHTML = '<div class="text-center py-4 text-danger small font-monospace uppercase">Gagal memuat hasil pencarian.</div>';
                        });
                    }, 220);
                });
            }
        });
    </script>
    
    @stack('scripts')

    <script>
        (function() {
            const idleDuration = 3 * 24 * 60 * 60 * 1000; 
            let idleTimer;

            function resetTimer() {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(logoutUser, idleDuration);
            }

            function logoutUser() {
                alert("Sesi Anda telah berakhir karena tidak ada aktivitas selama 3 hari.");
                const form = document.getElementById('logout-form');
                if (form) {
                    form.submit();
                } else {
                    window.location.href = '/'; 
                }
            }

            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onmousedown = resetTimer; 
            document.ontouchstart = resetTimer; 
            document.onclick = resetTimer;     
            document.onkeydown = resetTimer;   
            document.addEventListener('scroll', resetTimer, true); 
        })();
    </script>

    @auth
    <!-- FIREBASE PUSH NOTIFICATION SDK -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";
        import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging.js";

        const firebaseConfig = {
            apiKey: "AIzaSyCric2X1hFP4TJ4bghlSnavjQZwmnszehU",
            authDomain: "stt-gpi-papua.firebaseapp.com",
            projectId: "stt-gpi-papua",
            storageBucket: "stt-gpi-papua.firebasestorage.app",
            messagingSenderId: "633336694324",
            appId: "1:633336694324:web:a2cb604ff59a4299b4ec61"
        };

        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        function requestPermission() {
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    const vapidKey = "BL2185YiCpj00XSIKLknQcbdc2dNk8wBCR1A96lKfGI38y6cgZI0XMw77JBE_AgbEn7STVZQLWkmK5pOzg_DQII";
                    getToken(messaging, { vapidKey: vapidKey })
                    .then((currentToken) => {
                        if (currentToken) {
                            kirimTokenKeLaravel(currentToken);
                        }
                    }).catch((err) => {
                        console.log('Error saat mengambil token: ', err);
                    });
                }
            });
        }

        function kirimTokenKeLaravel(token) {
            fetch('{{ route("fcmToken.simpan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ token: token })
            })
            .then(response => response.json())
            .then(data => console.log('Response Simpan Token:', data))
            .catch(error => console.error('Error simpan token:', error));
        }

        requestPermission();

        const notifSound = new Audio('{{ asset("sounds/notification.mp3") }}');
        notifSound.preload = 'auto';

        onMessage(messaging, (payload) => {
            try {
                notifSound.currentTime = 0; 
                notifSound.play().catch(function() {});
            } catch (err) {}

            const toastEl = document.getElementById('fcmToast');
            if (toastEl && payload.notification) {
                document.getElementById('fcmToastTitle').textContent = payload.notification.title || 'Pesan Baru';
                document.getElementById('fcmToastBody').textContent = payload.notification.body || '';
                new bootstrap.Toast(toastEl, { delay: 6000 }).show();
            }

            const badge = document.getElementById('notifBadge');
            if (badge) {
                let currentCount = parseInt(badge.innerText) || 0;
                badge.innerText = currentCount + 1;
                badge.classList.remove('d-none'); 
            }

            const notifHeader = document.getElementById('notifHeader');
            if (notifHeader && payload.notification) {
                const rawUrl = payload.data?.url || '#';
                const safeUrl = (rawUrl.startsWith('/') || rawUrl.startsWith(window.location.origin)) ? rawUrl : '#';
                const title = payload.notification.title || 'Notifikasi';
                const body = payload.notification.body || '';
                const iconClass = (payload.data?.icon || 'bi-info-circle-fill text-primary').replace(/[^a-zA-Z0-9\-\s_]/g, '');
                
                const newLi = document.createElement('li');
                const linkEl = document.createElement('a');
                linkEl.className = 'dropdown-item py-3 border-bottom text-wrap bg-light-new';
                linkEl.href = safeUrl;
                linkEl.style.whiteSpace = 'normal';

                const wrapperDiv = document.createElement('div');
                wrapperDiv.className = 'd-flex align-items-start gap-2';

                const iconEl = document.createElement('i');
                iconEl.className = `${iconClass} fs-4`;

                const contentDiv = document.createElement('div');

                const titleDiv = document.createElement('div');
                titleDiv.className = 'fw-bold uppercase';
                titleDiv.style.fontSize = '0.85rem';
                titleDiv.style.color = '#212529';
                titleDiv.textContent = title;

                const bodyDiv = document.createElement('div');
                bodyDiv.className = 'text-muted';
                bodyDiv.style.fontSize = '0.8rem';
                bodyDiv.style.color = '#6c757d';
                bodyDiv.textContent = body;

                const timeDiv = document.createElement('div');
                timeDiv.className = 'text-primary font-monospace mt-1 fw-bold';
                timeDiv.style.fontSize = '0.7rem';
                timeDiv.textContent = 'BARU SAJA';

                contentDiv.appendChild(titleDiv);
                contentDiv.appendChild(bodyDiv);
                contentDiv.appendChild(timeDiv);

                wrapperDiv.appendChild(iconEl);
                wrapperDiv.appendChild(contentDiv);
                linkEl.appendChild(wrapperDiv);
                newLi.appendChild(linkEl);
                
                notifHeader.insertAdjacentElement('afterend', newLi);
                
                const emptyMsg = document.getElementById('emptyNotifMsg');
                if (emptyMsg) emptyMsg.remove();
            }
        });
    </script>
    @endauth

</body>
</html>