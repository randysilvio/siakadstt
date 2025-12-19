<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Informasi Akademik - STT GPI Papua</title>
    
    {{-- CSS Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- CSS Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    {{-- Font Awesome & Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    {{-- Fonts --}}
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800&display=swap" rel="stylesheet" />

    {{-- Custom Styles (Tema Putih & Teal) --}}
    <style>
        body { font-family: 'Figtree', sans-serif; background-color: #f8fafc; }
        
        /* Navbar Styling */
        .navbar-custom { background-color: #ffffff; border-bottom: 1px solid #e5e7eb; padding-top: 0.8rem; padding-bottom: 0.8rem; }
        .brand-text { font-family: 'Raleway', sans-serif; font-weight: 800; color: #1e293b; letter-spacing: -0.025em; line-height: 1; }
        .brand-sub { font-size: 0.65rem; font-weight: 700; color: #0d9488; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; display: block; }
        
        /* Links */
        .navbar-nav .nav-link { color: #64748b; font-weight: 600; font-size: 0.9rem; padding: 0.5rem 1rem; transition: all 0.2s; }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active, .navbar-nav .show > .nav-link { color: #0d9488; background-color: #f0fdfa; border-radius: 6px; }
        .navbar-nav .nav-link i { margin-right: 6px; color: #94a3b8; }
        .navbar-nav .nav-link:hover i, .navbar-nav .nav-link.active i { color: #0d9488; }

        /* Dropdowns */
        .dropdown-menu { border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 8px; padding: 0.5rem; }
        .dropdown-item { font-size: 0.9rem; color: #475569; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 500; }
        .dropdown-item:hover { background-color: #f0fdfa; color: #0d9488; }
        .dropdown-header { color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; padding: 0.5rem 1rem; }
        .dropdown-divider { border-color: #f1f5f9; }

        /* User Profile */
        .user-avatar { width: 32px; height: 32px; background-color: #f1f5f9; color: #0d9488; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid #e2e8f0; }
        
        /* Footer */
        .footer { background-color: #ffffff; border-top: 1px solid #e5e7eb; color: #64748b; }
    </style>

    {{-- Load Tailwind hanya di halaman publik tertentu agar tidak bentrok --}}
    @if(request()->routeIs('berita.index') || request()->routeIs('dosen.public.*') || request()->routeIs('pengumuman.public.*'))
        @vite('resources/css/app.css')
    @endif
    
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Navigasi Utama --}}
    <nav class="navbar navbar-expand-xl navbar-light navbar-custom sticky-top shadow-sm">
        <div class="container">
            
            {{-- BRAND / LOGO --}}
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

            {{-- MENU ITEMS --}}
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    @auth
                        {{-- ========== MENU GLOBAL (Semua User) ========== --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-grid-fill"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kalender.*') ? 'active' : '' }}" href="{{ route('kalender.halaman') }}">
                                <i class="bi bi-calendar-event"></i>Kalender
                            </a>
                        </li>

                        {{-- ========== MODULE E-LEARNING (Admin, Dosen, Mhs) ========== --}}
                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('dosen') || Auth::user()->hasRole('mahasiswa'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('verum.*') ? 'active' : '' }}" href="{{ route('verum.index') }}">
                                    <i class="bi bi-laptop"></i>E-Learning
                                </a>
                            </li>
                        @endif

                        {{-- ========== MENU PERAN: PUSTAKAWAN ========== --}}
                        @if (Auth::user()->hasRole('pustakawan'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perpustakaan*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-book"></i>Perpustakaan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.index') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.koleksi.index') }}">Manajemen Koleksi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.peminjaman.index') }}">Sirkulasi</a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- ========== MENU PERAN: MAHASISWA ========== --}}
                        @if(Auth::user()->hasRole('mahasiswa'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('krs*', 'khs*', 'transkrip*', 'pembayaran*', 'evaluasi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-mortarboard"></i>Akademik
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Studi</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('krs.index') }}">Kartu Rencana Studi (KRS)</a></li>
                                    <li><a class="dropdown-item" href="{{ route('khs.index') }}">Kartu Hasil Studi (KHS)</a></li>
                                    <li><a class="dropdown-item" href="{{ route('transkrip.index') }}">Transkrip Nilai</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Administrasi</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('pembayaran.riwayat') }}">Riwayat Pembayaran</a></li>
                                    <li><a class="dropdown-item" href="{{ route('evaluasi.index') }}">Evaluasi Dosen</a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- ========== MENU PERAN: DOSEN ========== --}}
                        @if(Auth::user()->hasRole('dosen'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perwalian*', 'kaprodi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-video3"></i>Dosen
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('perwalian.index') }}">Mahasiswa Wali</a></li>
                                    @if(Auth::user()->isKaprodi())
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Struktural</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('kaprodi.dashboard') }}">Portal Kaprodi</a></li>
                                    @endif
                                </ul>
                            </li>
                            @if(Auth::user()->hasRole('keuangan'))
                                <li class="nav-item"><a class="nav-link" href="{{ route('pembayaran.index') }}">Keuangan</a></li>
                            @endif
                        @endif

                        {{-- ========== MENU PERAN: ADMIN ========== --}}
                        @if(Auth::user()->hasRole('admin'))
                            {{-- Grup Akademik --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/tahun-akademik*', 'admin/program-studi*', 'admin/mata-kuliah*', 'admin/nilai*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-bank"></i>Akademik
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.tahun-akademik.index') }}">Tahun Akademik</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.program-studi.index') }}">Program Studi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.kurikulum.index') }}">Kurikulum</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.mata-kuliah.index') }}">Mata Kuliah</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.kalender.index') }}">Manajemen Kalender</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.nilai.index') }}">Input Nilai</a></li>
                                </ul>
                            </li>

                            {{-- Grup Pengguna --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/mahasiswa*', 'admin/dosen*', 'admin/user*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-people"></i>Pengguna
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.mahasiswa.index') }}">Manajemen Mahasiswa</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dosen.index') }}">Manajemen Dosen</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.user.index') }}">Manajemen User</a></li>
                                </ul>
                            </li>

                            {{-- [MENU BARU] Grup PMB (Penerimaan Mahasiswa Baru) --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/pmb*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-plus-fill"></i>PMB
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Penerimaan Baru</h6></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.pmb.index') }}">
                                            <i class="bi bi-file-earmark-person me-2"></i>Data Pendaftar
                                        </a>
                                    </li>
                                    {{-- [SUB MENU] Setting Gelombang --}}
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.pmb-periods.index') }}">
                                            <i class="bi bi-calendar-range me-2"></i>Setting Gelombang
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- Grup Konten & Sistem --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/pengumuman*', 'pembayaran*', 'admin/evaluasi*', 'admin/pengaturan*', 'admin/absensi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-hdd-rack"></i>Sistem
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Konten Publik</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pengumuman.index') }}">Pengumuman</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.slideshows.index') }}">Slideshow</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dokumen-publik.index') }}">Dokumen</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    
                                    <li><h6 class="dropdown-header">Administrasi</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('pembayaran.index') }}">Keuangan</a></li>
                                    
                                    {{-- MENU ABSENSI --}}
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.laporan.index') }}">Laporan Absensi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.lokasi.index') }}">Lokasi Absensi</a></li>
                                    
                                    {{-- Menu Setting Jam Absensi --}}
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.absensi.pengaturan.index') }}">
                                            Setting Jam & Toleransi
                                        </a>
                                    </li>

                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Evaluasi & Pengaturan</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-sesi.index') }}">Sesi Evaluasi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-pertanyaan.index') }}">Pertanyaan Evaluasi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-hasil.index') }}">Hasil Evaluasi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pengaturan.index') }}">Pengaturan Global</a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>

                {{-- USER PROFILE DROPDOWN (KANAN) --}}
                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="user-avatar">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="d-none d-lg-inline small fw-bold">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <div class="px-3 py-2 text-center border-bottom mb-2 bg-light rounded-top">
                                        <div class="small text-muted">Login sebagai</div>
                                        <div class="fw-bold text-teal-600">{{ Auth::user()->roles->first()->name ?? 'User' }}</div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>Edit Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn btn-sm btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm" style="background-color: #0d9488; border-color: #0d9488;">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login Area
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- KONTEN UTAMA --}}
    <main class="container mt-4 mb-5">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="footer mt-auto py-4">
        <div class="container text-center">
            <p class="mb-0 small">&copy; {{ date('Y') }} <strong>STT GPI Papua</strong>. Sistem Informasi Akademik.</p>
        </div>
    </footer>

    {{-- Chatbot --}}
    @include('partials.chatbot')

    {{-- Script JavaScript --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Inisialisasi Tooltip Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
    
    @stack('scripts')
</body>
</html>