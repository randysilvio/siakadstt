<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $globalCfg        = \App\Models\Pengaturan::allCached();
        $themeColors      = \App\Models\Pengaturan::themeColors();
        $logoUrl          = \App\Models\Pengaturan::logoUrl();
        $cardWmUrl        = \App\Models\Pengaturan::cardWatermarkUrl();
        $faviconUrl       = !empty($globalCfg['favicon_path']) ? asset('storage/' . $globalCfg['favicon_path']) : $logoUrl;
        $namaBrand        = $globalCfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
        $namaLengkap      = $globalCfg['nama_kampus'] ?? 'Sistem Informasi Akademik';
        $istilahPerwalian = $globalCfg['istilah_perwalian'] ?? 'Tutorship';
    @endphp
    <title>SIAKAD - {{ $namaBrand }}</title>
    
    <link rel="icon" href="{{ $faviconUrl }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|raleway:700,800&display=swap" rel="stylesheet" />

    <style>
        :root {
            --siakad-primary: {{ $themeColors['primary'] }};
            --siakad-accent: {{ $themeColors['accent'] }};
            --siakad-radius: {{ $themeColors['radius'] ?? '10px' }};
            --siakad-wm-opacity: {{ $themeColors['watermark_opacity'] ?? '0.06' }};
        }
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            -webkit-tap-highlight-color: transparent;
        }

        /* Compatibility Utility Classes untuk Seluruh Blade Internal */
        .uppercase { text-transform: uppercase !important; }
        .flex-grow { flex-grow: 1 !important; }
        .font-bold { font-weight: 700 !important; }
        .text-sm { font-size: 0.875rem !important; }
        .tracking-widest { letter-spacing: 0.08em !important; }
        .shadow-2xs { box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.05) !important; }
        .py-0\.5 { padding-top: 0.15rem !important; padding-bottom: 0.15rem !important; }
        .py-1\.5 { padding-top: 0.375rem !important; padding-bottom: 0.375rem !important; }
        .py-2\.5 { padding-top: 0.625rem !important; padding-bottom: 0.625rem !important; }
        .px-2\.5 { padding-left: 0.625rem !important; padding-right: 0.625rem !important; }
        .me-1\.5 { margin-right: 0.375rem !important; }
        .top-25 { top: 22% !important; }
        .start-75 { left: 75% !important; }
        @media (min-width: 768px) {
            .items-md-center { align-items: center !important; }
        }

        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 2px solid var(--siakad-primary);
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
        }
        .brand-logo-img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .brand-text {
            font-family: 'Raleway', sans-serif;
            font-weight: 800;
            color: var(--siakad-primary);
            letter-spacing: 0.01em;
            line-height: 1.15;
            white-space: nowrap;
        }
        .brand-sub {
            font-size: 0.66rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-top: 2px;
            display: block;
            white-space: nowrap;
        }
        .navbar-nav .nav-link {
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            padding: 0.45rem 0.65rem !important;
            transition: all 0.2s;
            border-radius: var(--siakad-radius) !important;
            white-space: nowrap;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active, .navbar-nav .show > .nav-link {
            color: #ffffff;
            background-color: var(--siakad-primary);
        }
        .dropdown-toggle::after { display: none !important; }
        .dropdown-menu {
            border: 1px solid var(--siakad-primary);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border-radius: var(--siakad-radius);
            padding: 0.25rem;
            margin-top: 0.25rem;
        }
        .dropdown-item {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #1e293b;
            padding: 0.55rem 0.85rem;
            border-radius: calc(var(--siakad-radius) * 0.6);
            font-weight: 600;
        }
        .dropdown-item:hover { background-color: var(--siakad-primary); color: #ffffff; }
        .dropdown-header { color: #64748b; font-size: 0.7rem; text-transform: uppercase; font-weight: 700; padding: 0.45rem 0.85rem; }
        .dropdown-divider { border-color: #e2e8f0; margin: 0.25rem 0; }
        .user-avatar {
            width: 32px;
            height: 32px;
            background-color: var(--siakad-primary);
            color: #ffffff;
            border-radius: var(--siakad-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-family: monospace;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .user-name-truncate { max-width: 130px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block; vertical-align: middle; }
        .footer { background-color: #ffffff; border-top: 2px solid var(--siakad-primary); color: #1e293b; }

        /* Override Warna Tema Dinamis */
        .bg-dark, .bg-primary { background-color: var(--siakad-primary) !important; }
        .pagination { --bs-pagination-color: var(--siakad-primary); --bs-pagination-hover-color: var(--siakad-primary); --bs-pagination-active-bg: var(--siakad-primary);
            --bs-pagination-active-border-color: var(--siakad-primary); --bs-pagination-focus-box-shadow: none; }
        .siakad-paginasi .page-link { min-width: 2.1rem; text-align: center; font-weight: 600; }
        .btn-dark, .btn-primary { background-color: var(--siakad-primary) !important; border-color: var(--siakad-primary) !important; color: #ffffff !important; }
        .btn-dark:hover, .btn-primary:hover { background-color: var(--siakad-accent) !important; border-color: var(--siakad-accent) !important; color: #ffffff !important; }
        .btn-outline-dark, .btn-outline-primary { color: var(--siakad-primary) !important; border-color: var(--siakad-primary) !important; }
        .btn-outline-dark:hover, .btn-outline-primary:hover { background-color: var(--siakad-primary) !important; color: #ffffff !important; }
        .text-primary { color: var(--siakad-primary) !important; }
        .border-dark, .border-primary { border-color: var(--siakad-primary) !important; }
        .nav-tabs .nav-link.active { background-color: var(--siakad-primary) !important; color: #ffffff !important; border-color: var(--siakad-primary) !important; }

        /* Proporsi Sudut Kartu & Komponen */
        .card, .btn, .form-control, .form-select, .alert, .modal-content {
            border-radius: var(--siakad-radius) !important;
        }
        .card-header:first-child {
            border-top-left-radius: calc(var(--siakad-radius) - 1px) !important;
            border-top-right-radius: calc(var(--siakad-radius) - 1px) !important;
        }

        /* Watermark Eksklusif Hanya pada Kartu Utama (.wm-card-exclusive) */
        .wm-card-exclusive {
            position: relative;
            overflow: hidden;
        }
        .wm-card-exclusive::after {
            content: "";
            position: absolute;
            right: -12px;
            bottom: -12px;
            width: 110px;
            height: 110px;
            background-image: url('{{ $cardWmUrl }}');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: bottom right;
            filter: grayscale(100%) contrast(120%);
            opacity: var(--siakad-wm-opacity);
            pointer-events: none;
            z-index: 0;
        }
        .wm-card-exclusive.bg-dark::after {
            filter: grayscale(100%) invert(100%);
            opacity: 0.08;
        }
        .wm-card-exclusive > * {
            position: relative;
            z-index: 1;
        }

        /* Efek Hover Lift Halus */
        .hover-lift {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08) !important;
        }

        @keyframes pulse-dot { 0% { transform: scale(0.95); opacity: 0.8; } 50% { transform: scale(1.2); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.8; } }
        .online-dot { animation: pulse-dot 2s infinite ease-in-out; }
        .bg-light-new { background-color: #e9ecef !important; }

        /* MOBILE BOTTOM NAVIGATION BAR */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 62px;
            background: #ffffff;
            border-top: 1px solid #cbd5e1;
            box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.06);
            z-index: 1040;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .mobile-bottom-nav a {
            flex: 1;
            text-align: center;
            text-decoration: none;
            color: #64748b;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 2px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            transition: color 0.2s ease;
        }
        .mobile-bottom-nav a i { font-size: 1.15rem; line-height: 1; }
        .mobile-bottom-nav a.active { color: var(--siakad-primary); }

        @media (max-width: 991.98px) {
            body { padding-bottom: 72px; }
            .container, .container-fluid { padding-left: 12px !important; padding-right: 12px !important; }
            h3 { font-size: 1.2rem !important; }
            h4 { font-size: 1.05rem !important; }
            .table th, .table td { font-size: 0.78rem; padding: 0.5rem 0.45rem; }
            .navbar-collapse { max-height: 75vh; overflow-y: auto; padding-top: 0.75rem; padding-bottom: 0.75rem; }
        }

        .cmd-item:hover { background-color: var(--siakad-primary) !important; color: #ffffff !important; }
        .cmd-item:hover .text-muted, .cmd-item:hover .text-dark { color: #e9ecef !important; }
    </style>

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    @if(session()->has('impersonate_by'))
        <div class="bg-danger text-white text-center py-2 px-2 fw-bold uppercase small shadow-sm" style="position: sticky; top: 0; z-index: 1055;">
            Mode Penyamaran: Anda sedang mengendalikan akun <u class="font-monospace">{{ Auth::user()->name }}</u>.
            <form action="{{ route('impersonate.stop') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-dark text-white fw-bold ms-2 px-3 py-0.5 shadow-sm">
                    Kembali ke Admin
                </button>
            </form>
        </div>
    @endif

    <nav class="navbar navbar-expand-xl navbar-light navbar-custom sticky-top shadow-2xs">
        <div class="container-fluid px-3 px-lg-4">
            {{-- KLIK LOGO & NAMA KAMPUS LANGSUNG KE DASHBOARD (TIDAK TERPOTONG) --}}
            <a class="navbar-brand d-flex align-items-center me-2 me-xl-3 flex-shrink-0" href="{{ auth()->check() ? route('dashboard') : '/' }}" title="Kembali ke Dashboard Utama">
                <img src="{{ $logoUrl }}" alt="Logo" class="brand-logo-img">
                <div class="d-flex flex-column justify-content-center">
                    <span class="brand-text fs-5">{{ $namaBrand }}</span>
                    <span class="brand-sub">Sistem Informasi Akademik</span>
                </div>
            </a>

            <div class="d-flex align-items-center gap-1 ms-auto d-xl-none">
                @auth
                    @if(!Auth::user()->hasRole('mahasiswa') && !Auth::user()->hasRole('camaba'))
                        <button type="button" class="btn btn-sm btn-outline-dark px-2 py-1" data-bs-toggle="modal" data-bs-target="#globalSearchModal" title="Cari Cepat">
                            <i class="bi bi-search"></i>
                        </button>
                    @endif
                    <a class="btn btn-sm position-relative px-2" href="{{ route('notifikasi.index') }}" title="Notifikasi">
                        <i class="bi bi-bell fs-5 text-dark"></i>
                        @if(Auth::user()->unreadNotifications->count() > 0)
                            <span class="position-absolute top-0 start-75 translate-middle badge bg-danger" style="font-size: 0.6rem;">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>
                @endauth
                <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-xl-auto align-items-xl-center gap-1">
                    @auth
                        @if(Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/pmb*', 'admin/mahasiswa*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    Mahasiswa & PMB
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Penerimaan Baru (PMB)</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pmb.index') }}">Data Pendaftar</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pmb-periods.index') }}">Gelombang PMB</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pmb-panitia.index') }}">Panitia PMB</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Data Mahasiswa</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.mahasiswa.index') }}">Data Mahasiswa</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/tahun-akademik*', 'admin/fakultas*', 'admin/program-studi*', 'admin/mata-kuliah*', 'admin/kurikulum*', 'admin/nilai*', 'admin/kalender*', 'kalender-akademik*', 'bimbingan-akademik*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    Akademik
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Master Akademik</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tahun-akademik.index') }}">Tahun Akademik</a></li>
                                    @if(($globalCfg['fitur_fakultas_aktif'] ?? '0') === '1')
                                        <li><a class="dropdown-item" href="{{ route('admin.fakultas.index') }}">Fakultas</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="{{ route('admin.program-studi.index') }}">Program Studi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.kurikulum.index') }}">Kurikulum</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.mata-kuliah.index') }}">Mata Kuliah</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Perkuliahan & Kalender</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.nilai.index') }}">Input Nilai</a></li>
                                    <li><a class="dropdown-item" href="{{ route('bimbingan.index') }}">Bimbingan Skripsi & PPL</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.kalender.index') }}">Kalender Akademik</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/dosen*', 'admin/tendik*', 'admin/user*', 'admin/absensi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    SDM & User
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Personalia</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dosen.index') }}">Data Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tendik.index') }}">Data Tendik</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.user.index') }}">Akun Pengguna</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Kehadiran Pegawai</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.laporan.index') }}">Laporan Absensi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.lokasi.index') }}">Lokasi Kerja</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.pengaturan.index') }}">Jam Kerja</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('administrasi_umum'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/pengumuman*', 'admin/slideshows*', 'admin/dokumen-publik*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    Website
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.pengumuman.index') }}">Berita & Pengumuman</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.slideshows.index') }}">Slideshow Beranda</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dokumen-publik.index') }}">Dokumen Publik</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('penjaminan_mutu'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/evaluasi*', 'admin/tracer-study*', 'admin/pengaturan*', 'admin/feeder*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    Pengaturan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Mutu & Evaluasi</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-sesi.index') }}">Sesi Evaluasi Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-pertanyaan.index') }}">Pertanyaan Evaluasi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-hasil.index') }}">Hasil Evaluasi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracer.index') }}">Data Tracer Study</a></li>
                                    @if(Auth::user()->hasRole('admin'))
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Sistem & PDDIKTI</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.pengaturan.index') }}">Pengaturan Sistem</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.aturan.index') }}">Aturan & Perhitungan</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.feeder.index') }}">Sinkronisasi Feeder & Backup</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('penjaminan_mutu'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('penjaminan-mutu/laporan*') ? 'active' : '' }}" href="{{ route('mutu.laporan.index') }}">
                                    Laporan Mutu
                                </a>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('administrasi_umum'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('administrasi*', 'admin/layanan-surat-mahasiswa*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    Administrasi
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('administrasi.surat-keputusan.create') }}">Buat SK Baru</a></li>
                                    <li><a class="dropdown-item" href="{{ route('administrasi.surat-keputusan.index') }}">Arsip SK</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.surat-mandiri.index') }}">Permohonan Surat</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('dosen') || (Auth::user()->hasRole('mahasiswa') && optional(Auth::user()->mahasiswa)->status_mahasiswa !== 'Lulus'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('verum.*') ? 'active' : '' }}" href="{{ route('verum.index') }}">
                                    Verum
                                </a>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('dosen') && !Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perwalian*', 'kaprodi*', 'dosen*', 'bimbingan-akademik*', 'kalender-akademik*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    Akademik & Dosen
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('dosen.dashboard') }}">Ruang Kerja Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('dosen.tridharma.index') }}">Data Tridharma</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perwalian.index') }}">{{ $istilahPerwalian }} Mahasiswa</a></li>
                                    <li><a class="dropdown-item" href="{{ route('bimbingan.index') }}">Bimbingan Skripsi & PPL</a></li>
                                    <li><a class="dropdown-item" href="{{ route('kalender.halaman') }}">Kalender Akademik</a></li>
                                    @if(Auth::user()->isKaprodi() || Auth::user()->hasRole('kaprodi'))
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('kaprodi.dashboard') }}">Panel Kaprodi</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('mahasiswa'))
                            @if(optional(Auth::user()->mahasiswa)->status_mahasiswa === 'Lulus')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('transkrip*', 'pembayaran*', 'tracer-study*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                        Alumni
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('transkrip.index') }}">Transkrip Nilai Akhir</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pembayaran.riwayat') }}">Riwayat Pembayaran</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('tracer.index') }}">Tracer Study Alumni</a></li>
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('krs*', 'khs*', 'transkrip*', 'pembayaran*', 'evaluasi*', 'bimbingan-akademik*', 'layanan-surat*', 'kalender-akademik*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                        Akademik
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('krs.index') }}">Kartu Rencana Studi (KRS)</a></li>
                                        <li><a class="dropdown-item" href="{{ route('khs.index') }}">Kartu Hasil Studi (KHS)</a></li>
                                        <li><a class="dropdown-item" href="{{ route('transkrip.index') }}">Transkrip Nilai</a></li>
                                        <li><a class="dropdown-item" href="{{ route('bimbingan.index') }}">Bimbingan Skripsi & PPL</a></li>
                                        <li><a class="dropdown-item" href="{{ route('kalender.halaman') }}">Kalender Akademik</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('pembayaran.riwayat') }}">Riwayat Pembayaran</a></li>
                                        <li><a class="dropdown-item" href="{{ route('surat-mandiri.index') }}">Permohonan Surat</a></li>
                                        <li><a class="dropdown-item" href="{{ route('evaluasi.index') }}">Evaluasi Dosen</a></li>
                                    </ul>
                                </li>
                            @endif
                        @endif

                        @if (Auth::user()->hasRole('pustakawan'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perpustakaan*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    Perpustakaan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.koleksi.index') }}">Koleksi Buku</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.peminjaman.index') }}">Sirkulasi Peminjaman</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(!Auth::user()->hasRole('admin') && Auth::user()->isPanitiaPmb())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/pmb*') ? 'active' : '' }}" href="{{ route('admin.pmb.index') }}">
                                    Panitia PMB
                                </a>
                            </li>
                        @endif

                        @if(Auth::user()->hasRole('keuangan'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('pembayaran*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                                    Tagihan & Pembayaran
                                </a>
                            </li>
                        @endif

                        @if(!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('dosen') && !Auth::user()->hasRole('mahasiswa') && !Auth::user()->hasRole('camaba'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('kalender.*') ? 'active' : '' }}" href="{{ route('kalender.halaman') }}">
                                    Kalender Akademik
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-xl-auto align-items-xl-center gap-1 mt-2 mt-xl-0 border-top border-xl-0 pt-2 pt-xl-0 flex-shrink-0">
                    @auth
                        @if(!Auth::user()->hasRole('mahasiswa') && !Auth::user()->hasRole('camaba'))
                            <li class="nav-item me-1 d-none d-xl-block">
                                <button type="button" class="btn btn-sm btn-outline-dark px-2.5 py-1 d-flex align-items-center gap-2 font-monospace fw-bold" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#globalSearchModal" title="Pencarian Cepat (Ctrl + K)">
                                    <i class="bi bi-search"></i>
                                    <span class="d-none d-xxl-inline">CARI</span>
                                </button>
                            </li>
                        @endif

                        <li class="nav-item me-1 d-none d-lg-block">
                            <div class="bg-light border border-dark px-2 py-1 d-flex align-items-center" style="border-radius: var(--siakad-radius);" title="Pengguna Aktif Saat Ini">
                                <i class="bi bi-circle-fill text-success online-dot me-1.5" style="font-size: 0.5rem;"></i>
                                <span class="small font-monospace uppercase fw-bold text-dark" style="font-size: 0.72rem;">
                                    <strong>{{ $onlineUsersCount ?? 1 }}</strong> ON
                                </span>
                            </div>
                        </li>

                        <li class="nav-item dropdown mx-1 d-none d-xl-block">
                            <a class="nav-link position-relative px-2" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5 text-secondary"></i>
                                <span id="notifBadge" class="position-absolute top-25 start-75 translate-middle badge bg-danger {{ Auth::user()->unreadNotifications->count() > 0 ? '' : 'd-none' }}" style="font-size: 0.65rem;">
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
                                                <button type="submit" class="btn btn-sm btn-dark py-0 px-2 uppercase fw-bold" style="font-size: 0.7rem;">Tandai Dibaca</button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                                
                                @forelse(Auth::user()->unreadNotifications->take(5) as $notification)
                                    @php
                                        \App\Http\Controllers\NotificationController::sanitizeNotificationRecord($notification);
                                    @endphp
                                    <li>
                                        <a class="dropdown-item py-2.5 border-bottom text-wrap" href="{{ route('notifikasi.baca', $notification->id) }}" style="white-space: normal; word-break: break-word;">
                                            <div class="fw-bold uppercase" style="font-size: 0.8rem;">{{ $notification->data['title'] ?? 'Notifikasi' }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $notification->data['message'] ?? '' }}</div>
                                            <div class="text-muted font-monospace mt-1" style="font-size: 0.68rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                        </a>
                                    </li>
                                @empty
                                    <li id="emptyNotifMsg">
                                        <div class="px-3 py-4 text-center text-muted small uppercase fw-bold">
                                            Belum ada notifikasi baru.
                                        </div>
                                    </li>
                                @endforelse

                                <li>
                                    <a href="{{ route('notifikasi.index') }}" class="dropdown-item text-center py-2 text-dark fw-bold uppercase bg-light" style="font-size: 0.75rem;">
                                        Lihat Semua Riwayat
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown ms-xl-1">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="user-avatar">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="fw-bold uppercase user-name-truncate" style="font-size: 0.8rem;">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <div class="px-3 py-2 text-center border-bottom mb-1 bg-light">
                                        <div class="small text-muted uppercase" style="font-size: 0.7rem;">Peran Aktif</div>
                                        <div class="fw-bold uppercase text-dark" style="font-size: 0.8rem;">{{ Auth::user()->roles->first()->name ?? 'USER' }}</div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Pengaturan Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger fw-bold">Keluar (Logout)</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn btn-sm btn-dark fw-bold px-4 py-2 uppercase shadow-sm">
                                Login
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-3 mt-md-4 mb-5">
        @include('partials._flash_cadangan')
        @yield('content')
    </main>

    {{-- MOBILE STICKY BOTTOM NAVIGATION BAR (KHUSUS LAYAR HP < 992px) --}}
    @auth
        <div class="mobile-bottom-nav d-lg-none">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i>
                <span>Beranda</span>
            </a>

            @if(Auth::user()->hasRole('mahasiswa'))
                @if(optional(Auth::user()->mahasiswa)->status_mahasiswa === 'Lulus')
                    <a href="{{ route('tracer.index') }}" class="{{ request()->routeIs('tracer.*') ? 'active' : '' }}">
                        <i class="bi bi-briefcase"></i>
                        <span>Tracer</span>
                    </a>
                    <a href="{{ route('transkrip.index') }}" class="{{ request()->routeIs('transkrip.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Transkrip</span>
                    </a>
                @else
                    <a href="{{ route('krs.index') }}" class="{{ request()->routeIs('krs.*') ? 'active' : '' }}">
                        <i class="bi bi-card-checklist"></i>
                        <span>KRS</span>
                    </a>
                    <a href="{{ route('khs.index') }}" class="{{ request()->routeIs('khs.*') ? 'active' : '' }}">
                        <i class="bi bi-award"></i>
                        <span>KHS</span>
                    </a>
                    <a href="{{ route('verum.index') }}" class="{{ request()->routeIs('verum.*') ? 'active' : '' }}">
                        <i class="bi bi-laptop"></i>
                        <span>Verum</span>
                    </a>
                @endif
                <a href="{{ route('pembayaran.riwayat') }}" class="{{ request()->routeIs('pembayaran.riwayat') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Keuangan</span>
                </a>
            @elseif(Auth::user()->hasRole('dosen') && !Auth::user()->hasRole('admin'))
                <a href="{{ route('verum.index') }}" class="{{ request()->routeIs('verum.*') ? 'active' : '' }}">
                    <i class="bi bi-laptop"></i>
                    <span>Verum</span>
                </a>
                <a href="{{ route('perwalian.index') }}" class="{{ request()->routeIs('perwalian.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>{{ $istilahPerwalian }}</span>
                </a>
                <a href="{{ route('bimbingan.index') }}" class="{{ request()->routeIs('bimbingan.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-check"></i>
                    <span>Bimbingan</span>
                </a>
                <a href="{{ route('dosen.tridharma.index') }}" class="{{ request()->routeIs('dosen.tridharma.*') ? 'active' : '' }}">
                    <i class="bi bi-folder2-open"></i>
                    <span>Tridharma</span>
                </a>
            @elseif(Auth::user()->hasRole('admin'))
                <a href="{{ route('admin.mahasiswa.index') }}" class="{{ request()->is('admin/mahasiswa*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Mahasiswa</span>
                </a>
                <a href="{{ route('admin.mata-kuliah.index') }}" class="{{ request()->is('admin/mata-kuliah*') ? 'active' : '' }}">
                    <i class="bi bi-book"></i>
                    <span>Matkul</span>
                </a>
                <a href="{{ route('admin.feeder.index') }}" class="{{ request()->is('admin/feeder*') ? 'active' : '' }}">
                    <i class="bi bi-database-down"></i>
                    <span>Feeder</span>
                </a>
                <a href="{{ route('admin.pengaturan.index') }}" class="{{ request()->is('admin/pengaturan*') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>
                    <span>Pengaturan</span>
                </a>
            @else
                <a href="{{ route('kalender.halaman') }}" class="{{ request()->routeIs('kalender.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Kalender</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person"></i>
                    <span>Profil</span>
                </a>
            @endif
        </div>
    @endauth

    <footer class="footer mt-auto py-4 d-none d-lg-block">
        <div class="container text-center">
            <p class="mb-0 small uppercase">&copy; {{ date('Y') }} <strong>{{ $namaBrand }}</strong> — {{ $namaLengkap }}.</p>
        </div>
    </footer>

    {{-- Pengingat kelengkapan data: hanya Admin (bukan saat Admin menyamar sebagai pengguna lain) --}}
    @auth
        @if(Auth::user()->hasRole('admin') && !session()->has('impersonate_by'))
            @include('partials._pengingat_admin')
        @endif
    @endauth

    @include('partials.chatbot')

    @auth
    <div class="modal fade" id="globalSearchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 border-top border-dark border-4 shadow-lg">
                <div class="modal-header bg-dark text-white py-2.5 px-3">
                    <span class="fw-bold uppercase small font-monospace">Pencarian Cepat SIAKAD (Ctrl + K)</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 bg-light">
                    <div class="input-group mb-2">
                        <span class="input-group-text bg-white border-dark"><i class="bi bi-search text-dark"></i></span>
                        <input type="text" id="globalSearchInput" class="form-control border-dark font-monospace uppercase" placeholder="Ketik NIM, Nama Mahasiswa, Dosen, Kode MK, atau Nomor SK..." autocomplete="off">
                    </div>
                    <div id="globalSearchResults" class="list-group border-0" style="max-height: 380px; overflow-y: auto;">
                        <div class="text-center py-4 text-muted small font-monospace uppercase">
                            Ketik minimal 2 karakter untuk mencari...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 1090; margin-top: 60px;">
        <div id="fcmToast" class="toast align-items-center text-bg-dark border-start border-light border-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-dark text-white border-bottom border-secondary p-3">
                <strong class="me-auto uppercase fw-bold" id="fcmToastTitle" style="font-size: 0.8rem;">Notifikasi Baru</strong>
                <small class="text-white-50 font-monospace" style="font-size: 0.65rem;">BARU SAJA</small>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body p-3 text-light uppercase small" id="fcmToastBody" style="line-height: 1.5;"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Ikon "?" penjelasan aturan & rumus (komponen info-aturan)
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
            new bootstrap.Popover(el);
        });

        document.addEventListener('DOMContentLoaded', function () {
            const firstInvalid = document.querySelector('.tab-pane .is-invalid');
            if (firstInvalid) {
                const pane = firstInvalid.closest('.tab-pane');
                if (pane && pane.id) {
                    const tabBtn = document.querySelector('[data-bs-target="#' + pane.id + '"], [href="#' + pane.id + '"]');
                    if (tabBtn) new bootstrap.Tab(tabBtn).show();
                }
                firstInvalid.focus();
            }

            document.querySelectorAll('form').forEach(function (form) {
                form.querySelectorAll('input, select, textarea').forEach(function (input) {
                    input.addEventListener('invalid', function () {
                        const pane = input.closest('.tab-pane');
                        if (pane && !pane.classList.contains('active') && pane.id) {
                            const tabBtn = document.querySelector('[data-bs-target="#' + pane.id + '"], [href="#' + pane.id + '"]');
                            if (tabBtn) new bootstrap.Tab(tabBtn).show();
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
                        searchResults.innerHTML = '<div class="text-center py-4 text-muted small font-monospace uppercase">Ketik minimal 2 karakter...</div>';
                        return;
                    }

                    searchResults.innerHTML = '<div class="text-center py-4 text-muted small font-monospace uppercase">Mencari...</div>';

                    searchTimeout = setTimeout(function () {
                        fetch('{{ route("global.search") }}?q=' + encodeURIComponent(q), {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            if (!data.results || data.results.length === 0) {
                                searchResults.innerHTML = '<div class="text-center py-4 text-muted small font-monospace uppercase">Data tidak ditemukan.</div>';
                                return;
                            }

                            data.results.forEach(item => {
                                const a = document.createElement('a');
                                a.href = item.url;
                                a.className = 'list-group-item list-group-item-action border-bottom py-2 px-3 cmd-item d-flex align-items-center justify-content-between';

                                const left = document.createElement('div');
                                const badge = document.createElement('span');
                                badge.className = 'badge bg-dark text-white font-monospace me-2';
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

                                a.appendChild(left);
                                searchResults.appendChild(a);
                            });
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
                idleTimer = setTimeout(function() {
                    const form = document.getElementById('logout-form');
                    if (form) form.submit();
                }, idleDuration);
            }
            window.onload = resetTimer;
            window.onmousemove = resetTimer;
            window.onmousedown = resetTimer;
            window.ontouchstart = resetTimer;
            window.onclick = resetTimer;
            window.onkeydown = resetTimer;
        })();
    </script>
    @include('partials._push_web')
</body>
</html>