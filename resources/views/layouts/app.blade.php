<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Informasi Akademik - STT GPI Papua</title>
    
    {{-- Favicon (Logo di Tab Browser) --}}
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

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
            
            {{-- BRAND / LOGO (Klik disini untuk ke Dashboard) --}}
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
                        {{-- =========================================================
                             1. MENU UMUM (Dashboard dihapus karena ada di Logo)
                             ========================================================= --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kalender.*') ? 'active' : '' }}" href="{{ route('kalender.halaman') }}">
                                <i class="bi bi-calendar-event"></i>Kalender
                            </a>
                        </li>

                        {{-- =========================================================
                             2. MENU KHUSUS ADMIN (6 KELOMPOK UTAMA)
                             ========================================================= --}}
                        @if(Auth::user()->hasRole('admin'))

                            {{-- KELOMPOK 1: MAHASISWA & PMB --}}
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

                            {{-- KELOMPOK 2: AKADEMIK --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/tahun-akademik*', 'admin/program-studi*', 'admin/mata-kuliah*', 'admin/kurikulum*', 'admin/nilai*', 'admin/kalender*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
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
                                    <li><a class="dropdown-item" href="{{ route('admin.kalender.index') }}">Event Kalender</a></li>
                                </ul>
                            </li>

                            {{-- KELOMPOK 3: SDM & USER --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/dosen*', 'admin/user*', 'admin/absensi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-badge-fill"></i>SDM & User
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Personalia</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dosen.index') }}">Data Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.user.index') }}">Akun User System</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Absensi Pegawai</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.laporan.index') }}">Laporan Kehadiran</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.lokasi.index') }}">Lokasi Kantor</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.pengaturan.index') }}">Setting Jam Kerja</a></li>
                                </ul>
                            </li>

                            {{-- KELOMPOK 4: KEUANGAN --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('pembayaran*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                                    <i class="bi bi-wallet2"></i>Keuangan
                                </a>
                            </li>

                            {{-- KELOMPOK 5: WEBSITE --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/pengumuman*', 'admin/slideshows*', 'admin/dokumen-publik*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-globe"></i>Website
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.pengumuman.index') }}">Pengumuman</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.slideshows.index') }}">Slideshow Depan</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dokumen-publik.index') }}">Dokumen Publik</a></li>
                                </ul>
                            </li>

                            {{-- KELOMPOK 6: PENGATURAN --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/evaluasi*', 'admin/pengaturan*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear-wide-connected"></i>Pengaturan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Penjaminan Mutu</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-sesi.index') }}">Sesi Evaluasi Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-pertanyaan.index') }}">Bank Pertanyaan</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-hasil.index') }}">Laporan Hasil Evaluasi</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Konfigurasi</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pengaturan.index') }}">Pengaturan Global</a></li>
                                </ul>
                            </li>

                        @endif
                        {{-- =========================================================
                             AKHIR MENU ADMIN
                             ========================================================= --}}


                        {{-- =========================================================
                             3. MENU PERAN LAIN (Dosen, Mahasiswa, Pustakawan)
                             ========================================================= --}}
                        
                        {{-- E-LEARNING (Bisa diakses Dosen & Mahasiswa) --}}
                        @if(Auth::user()->hasRole('dosen') || Auth::user()->hasRole('mahasiswa'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('verum.*') ? 'active' : '' }}" href="{{ route('verum.index') }}">
                                    <i class="bi bi-laptop"></i>E-Learning
                                </a>
                            </li>
                        @endif

                        {{-- MENU DOSEN --}}
                        @if(Auth::user()->hasRole('dosen') && !Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perwalian*', 'kaprodi*', 'dosen*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-video3"></i>Dosen
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('dosen.dashboard') }}">Dashboard Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perwalian.index') }}">Perwalian Mahasiswa</a></li>
                                    @if(Auth::user()->isKaprodi())
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Kaprodi Area</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('kaprodi.dashboard') }}">Dashboard Kaprodi</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- MENU MAHASISWA --}}
                        @if(Auth::user()->hasRole('mahasiswa'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('krs*', 'khs*', 'transkrip*', 'pembayaran*', 'evaluasi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-mortarboard"></i>Akademik
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Studi</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('krs.index') }}">KRS (Kartu Rencana Studi)</a></li>
                                    <li><a class="dropdown-item" href="{{ route('khs.index') }}">KHS (Kartu Hasil Studi)</a></li>
                                    <li><a class="dropdown-item" href="{{ route('transkrip.index') }}">Transkrip Nilai</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Administrasi</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('pembayaran.riwayat') }}">Riwayat Pembayaran</a></li>
                                    <li><a class="dropdown-item" href="{{ route('evaluasi.index') }}">Evaluasi Dosen</a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- MENU PUSTAKAWAN --}}
                        @if (Auth::user()->hasRole('pustakawan'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perpustakaan*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-book"></i>Perpustakaan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.index') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.koleksi.index') }}">Koleksi Buku</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.peminjaman.index') }}">Sirkulasi</a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- MENU STAFF KEUANGAN (Jika login sbg staff khusus, bukan Admin) --}}
                        @if(Auth::user()->hasRole('keuangan') && !Auth::user()->hasRole('admin'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('pembayaran.index') }}">Keuangan</a></li>
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
                                    {{-- [PERBAIKAN] Tambahkan ID pada Form Logout --}}
                                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
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

    {{-- [PERBAIKAN] Script Auto Logout (Idle 10 Menit) --}}
    <script>
        (function() {
            // Waktu dalam milidetik (10 menit = 600.000 ms)
            const idleDuration = 600 * 1000;
            let idleTimer;

            function resetTimer() {
                clearTimeout(idleTimer);
                // Set timer baru untuk logout
                idleTimer = setTimeout(logoutUser, idleDuration);
            }

            function logoutUser() {
                // Opsional: Tampilkan pesan alert sebelum redirect
                alert("Sesi Anda telah berakhir karena tidak ada aktivitas selama 10 menit.");
                
                // Cari form logout dan submit
                const form = document.getElementById('logout-form');
                if (form) {
                    form.submit();
                } else {
                    // Fallback jika form tidak ketemu (misal halaman login)
                    window.location.href = '/'; 
                }
            }

            // Event listener untuk mereset timer saat ada aktivitas user
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onmousedown = resetTimer; // Clicks
            document.ontouchstart = resetTimer; // Touchscreen
            document.onclick = resetTimer;     // Touchpad clicks
            document.onkeydown = resetTimer;   // Typing
            document.addEventListener('scroll', resetTimer, true); 
        })();
    </script>
</body>
</html>