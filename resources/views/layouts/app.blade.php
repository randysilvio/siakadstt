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
        .navbar-nav .nav-link { color: #495057; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; padding: 0.5rem 1rem; transition: all 0.2s; border-radius: 0 !important; }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active, .navbar-nav .show > .nav-link { color: #ffffff; background-color: #212529; }
        .navbar-nav .nav-link i { margin-right: 6px; color: inherit; }
        .dropdown-menu { border: 1px solid #212529; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 0; padding: 0; margin-top: 0; }
        .dropdown-item { font-size: 0.85rem; text-transform: uppercase; color: #212529; padding: 0.7rem 1rem; border-radius: 0; font-weight: 600; border-bottom: 1px solid #e9ecef; transition: background-color 0.2s ease; }
        .dropdown-item:last-child { border-bottom: none; }
        .dropdown-item:hover { background-color: #212529; color: #ffffff; }
        .dropdown-header { color: #6c757d; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; padding: 0.6rem 1rem; background-color: #f1f3f5; border-bottom: 1px solid #e9ecef; margin: 0; letter-spacing: 0.05em; }
        .dropdown-divider { border-color: #212529; margin: 0; }
        .user-avatar { width: 32px; height: 32px; background-color: #212529; color: #ffffff; border-radius: 0; display: flex; align-items: center; justify-content: center; font-weight: bold; font-family: monospace; font-size: 1rem; }
        .footer { background-color: #ffffff; border-top: 2px solid #212529; color: #212529; }
        @keyframes pulse-dot { 0% { transform: scale(0.95); opacity: 0.8; } 50% { transform: scale(1.2); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.8; } }
        .online-dot { animation: pulse-dot 2s infinite ease-in-out; }
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
        <div class="container">
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
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    @auth
                        {{-- CALENDAR (GLOBAL) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kalender.*') ? 'active' : '' }}" href="{{ route('kalender.halaman') }}">
                                <i class="bi bi-calendar3"></i>Kalender
                            </a>
                        </li>

                        {{-- ADMIN PORTAL (DIPADATKAN MENJADI 2 DROPDOWN) --}}
                        @if(Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/pmb*', 'admin/mahasiswa*', 'admin/tahun-akademik*', 'admin/program-studi*', 'admin/mata-kuliah*', 'admin/kurikulum*', 'admin/kalender*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-mortarboard-fill"></i>Akademik
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Manajemen Mahasiswa</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pmb.index') }}">Pendaftar PMB</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pmb-periods.index') }}">Gelombang PMB</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.mahasiswa.index') }}">Direktori Mahasiswa</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Master Perkuliahan</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tahun-akademik.index') }}">Tahun Akademik</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.program-studi.index') }}">Prodi & Kurikulum</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.mata-kuliah.index') }}">Mata Kuliah & Kelas</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.kalender.index') }}">Agenda Kalender</a></li>
                                </ul>
                            </li>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('admin/dosen*', 'admin/user*', 'admin/absensi*', 'admin/pengumuman*', 'admin/slideshows*', 'admin/dokumen-publik*', 'admin/pengaturan*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-hdd-network-fill"></i>Sistem & Konfigurasi
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Otoritas & Personalia</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dosen.index') }}">Direktori Dosen</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.user.index') }}">Akun Pengguna</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Presensi Internal</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.laporan.index') }}">Laporan Kehadiran</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.absensi.lokasi.index') }}">Pengaturan Shift/Lokasi</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Website & Konten</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pengumuman.index') }}">Warta Kampus</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.slideshows.index') }}">Banner Utama</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dokumen-publik.index') }}">Dokumen Publik</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item bg-light text-dark fw-bold" href="{{ route('admin.pengaturan.index') }}"><i class="bi bi-gear-fill me-2"></i>Konfigurasi Dasar</a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- ADMINISTRASI UMUM / TATA USAHA --}}
                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('administrasi_umum'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('administrasi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-envelope-paper-fill"></i>E-Office
                                </a>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Persuratan Otomatis</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('administrasi.surat-keputusan.create') }}">Buat Dokumen / SK</a></li>
                                    <li><a class="dropdown-item" href="{{ route('administrasi.surat-keputusan.index') }}">Arsip Dokumen Final</a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- PENJAMINAN MUTU (TERPADU) --}}
                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('penjaminan_mutu'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('penjaminan-mutu*', 'admin/evaluasi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-shield-check"></i>Penjaminan Mutu
                                </a>
                                <ul class="dropdown-menu">
                                    @if(Auth::user()->hasRole('penjaminan_mutu'))
                                        <li><h6 class="dropdown-header">Monitoring Center</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('mutu.dashboard') }}">Dashboard Mutu</a></li>
                                        <li><a class="dropdown-item" href="{{ route('mutu.laporan.index') }}">Laporan Institusi</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
                                    <li><h6 class="dropdown-header">Evaluasi Dosen (EDOM)</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-sesi.index') }}">Sesi Evaluasi</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-pertanyaan.index') }}">Bank Kuesioner</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.evaluasi-hasil.index') }}">Analisis Hasil Evaluasi</a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- RUANG KELAS / LMS (Dosen & Mahasiswa Aktif) --}}
                        @if(Auth::user()->hasRole('dosen') || (Auth::user()->hasRole('mahasiswa') && optional(Auth::user()->mahasiswa)->status_mahasiswa !== 'Lulus'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('verum.*') ? 'active' : '' }}" href="{{ route('verum.index') }}">
                                    <i class="bi bi-laptop"></i>LMS (Kelas)
                                </a>
                            </li>
                        @endif

                        {{-- PORTAL DOSEN --}}
                        @if(Auth::user()->hasRole('dosen') && !Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perwalian*', 'kaprodi*', 'dosen*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-workspace"></i>Portal Dosen
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('dosen.dashboard') }}">Dashboard Mengajar</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perwalian.index') }}">Bimbingan Perwalian</a></li>
                                    @if(Auth::user()->isKaprodi())
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Otoritas Program Studi</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('kaprodi.dashboard') }}">Panel Kaprodi</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- PORTAL MAHASISWA --}}
                        @if(Auth::user()->hasRole('mahasiswa'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('krs*', 'khs*', 'transkrip*', 'pembayaran*', 'evaluasi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-mortarboard"></i>Portal Mhs
                                </a>
                                <ul class="dropdown-menu">
                                    @if(optional(Auth::user()->mahasiswa)->status_mahasiswa === 'Lulus')
                                        <li><h6 class="dropdown-header">Layanan Kelulusan</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('transkrip.index') }}">Transkrip Nilai</a></li>
                                        <li><a class="dropdown-item" href="{{ route('pembayaran.riwayat') }}">Riwayat Pembayaran</a></li>
                                        <li><a class="dropdown-item text-primary" href="#">Tracer Study</a></li>
                                    @else
                                        <li><h6 class="dropdown-header">Manajemen Studi</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('krs.index') }}">Pengisian KRS</a></li>
                                        <li><a class="dropdown-item" href="{{ route('khs.index') }}">Hasil Studi (KHS)</a></li>
                                        <li><a class="dropdown-item" href="{{ route('transkrip.index') }}">Transkrip Sementara</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Layanan Pendukung</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('pembayaran.riwayat') }}">Info Pembayaran</a></li>
                                        <li><a class="dropdown-item" href="{{ route('evaluasi.index') }}">Isi Kuesioner (EDOM)</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- PERPUSTAKAAN --}}
                        @if (Auth::user()->hasRole('pustakawan'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('perpustakaan*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-book-half"></i>E-Library
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.index') }}">Dashboard Pustakawan</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.koleksi.index') }}">Katalog Koleksi Buku</a></li>
                                    <li><a class="dropdown-item" href="{{ route('perpustakaan.peminjaman.index') }}">Sistem Sirkulasi</a></li>
                                </ul>
                            </li>
                        @endif

                        {{-- KEUANGAN --}}
                        @if(Auth::user()->hasRole('keuangan'))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('pembayaran*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-wallet2"></i>Keuangan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('pembayaran.index') }}">Dashboard Keuangan</a></li>
                                    <li><a class="dropdown-item" href="{{ route('pembayaran.generate') }}">Generate Tagihan Massal</a></li>
                                    <li><a class="dropdown-item" href="{{ route('pembayaran.cetak') }}">Cetak Rekapitulasi</a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    @auth
                        <li class="nav-item me-1">
                            <div class="bg-light border border-dark rounded-0 px-2 py-1 d-flex align-items-center shadow-none" title="Jumlah Pengguna Daring Saat Ini">
                                <i class="bi bi-circle-fill text-success online-dot me-2" style="font-size: 0.55rem;"></i>
                                <span class="small font-monospace uppercase fw-bold text-dark" style="font-size: 0.75rem;">
                                    <strong class="text-success font-monospace">{{ $onlineUsersCount ?? 1 }}</strong> ONLINE
                                </span>
                            </div>
                        </li>

                        <li class="nav-item dropdown ms-1 me-1">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5 text-secondary"></i>
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <span class="position-absolute top-25 start-75 translate-middle badge rounded-0 bg-danger" style="font-size: 0.65rem;">
                                        {{ Auth::user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="width: 320px; max-height: 400px; overflow-y: auto;">
                                <li>
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
                                        <a class="dropdown-item py-3 border-bottom text-wrap" href="{{ $notification->data['url'] ?? '#' }}" style="white-space: normal;">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="{{ $notification->data['icon'] ?? 'bi-info-circle-fill text-dark' }} fs-4"></i>
                                                <div>
                                                    <div class="fw-bold uppercase" style="font-size: 0.85rem; color: #212529;">{{ $notification->data['title'] }}</div>
                                                    <div class="text-muted" style="font-size: 0.8rem;">{{ $notification->data['message'] }}</div>
                                                    <div class="text-muted font-monospace mt-1" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li>
                                        <div class="px-3 py-4 text-center text-muted">
                                            <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                            <span class="small uppercase fw-bold">Belum ada notifikasi baru.</span>
                                        </div>
                                    </li>
                                @endforelse
                            </ul>
                        </li>

                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="user-avatar">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="d-none d-lg-inline small fw-bold uppercase">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <div class="px-3 py-2 text-center border-bottom mb-2 bg-light">
                                        <div class="small text-muted uppercase">Login Sebagai</div>
                                        <div class="fw-bold uppercase text-dark">{{ Auth::user()->roles->first()->name ?? 'USER' }}</div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
    
    @stack('scripts')

    <script>
        (function() {
            const idleDuration = 600 * 1000;
            let idleTimer;

            function resetTimer() {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(logoutUser, idleDuration);
            }

            function logoutUser() {
                alert("Sesi Anda telah berakhir karena tidak ada aktivitas selama 10 menit.");
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
</body>
</html>