<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Administrasi Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1 0 auto;
        }
        .footer {
            flex-shrink: 0;
        }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            {{-- PERBAIKAN: Semua tautan brand sekarang mengarah ke satu rute 'dashboard' --}}
            @auth
                <a class="navbar-brand" href="{{ route('dashboard') }}">SIAKAD</a>
            @else
                <a class="navbar-brand" href="/">SIAKAD</a>
            @endauth
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        {{-- Tautan umum untuk semua user --}}
                        <li class="nav-item"><a class="nav-link" href="{{ route('kalender.halaman') }}">Kalender Akademik</a></li>
                        
                        {{-- Tautan untuk Verum (Hanya untuk Admin, Dosen, dan Mahasiswa) --}}
                        @if(Auth::user()->role == 'admin' || Auth::user()->role == 'dosen' || Auth::user()->role == 'mahasiswa')
                            <li class="nav-item"><a class="nav-link" href="{{ route('verum.index') }}">Verum</a></li>
                        @endif

                        {{-- Tautan Khusus Berdasarkan Role --}}
                        @if(Auth::user()->role == 'admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="masterDataDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Master Data
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="masterDataDropdown">
                                    <li><a class="dropdown-item" href="/mahasiswa">Mahasiswa</a></li>
                                    <li><a class="dropdown-item" href="/dosen">Dosen</a></li>
                                    <li><a class="dropdown-item" href="/program-studi">Program Studi</a></li>
                                    <li><a class="dropdown-item" href="/mata-kuliah">Mata Kuliah</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('tendik.create') }}">Buat Akun Tendik</a></li>
                                </ul>
                            </li>
                             <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="akademikDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Akademik
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="akademikDropdown">
                                    <li><a class="dropdown-item" href="/tahun-akademik">Tahun Akademik</a></li>
                                    <li><a class="dropdown-item" href="/nilai">Input Nilai</a></li>
                                    <li><a class="dropdown-item" href="/kalender">Manajemen Kalender</a></li>
                                    {{-- Tautan manajemen pengumuman hanya untuk admin --}}
                                    <li><a class="dropdown-item" href="{{ route('pengumuman.index') }}">Manajemen Pengumuman</a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="/pembayaran">Pembayaran</a></li>
                            <li class="nav-item"><a class="nav-link" href="/pengaturan">Pengaturan</a></li>

                        @elseif(Auth::user()->role == 'dosen')
                            <li class="nav-item"><a class="nav-link" href="{{ route('perwalian.index') }}">Mahasiswa Wali</a></li>
                            @if(Auth::user()->isKaprodi())
                                <li class="nav-item"><a class="nav-link" href="{{ route('kaprodi.dashboard') }}">Portal Kaprodi</a></li>
                            @endif
                            @if(Auth::user()->dosen && Auth::user()->dosen->is_keuangan)
                                <li class="nav-item"><a class="nav-link" href="/pembayaran">Pembayaran</a></li>
                            @endif

                        @elseif(Auth::user()->role == 'tendik')
                            @if(Auth::user()->jabatan == 'pustakawan')
                                <li class="nav-item"><a class="nav-link" href="{{ route('perpustakaan.koleksi.index') }}">Manajemen Koleksi</a></li>
                            @elseif(Auth::user()->jabatan == 'keuangan')
                                <li class="nav-item"><a class="nav-link" href="/pembayaran">Pembayaran</a></li>
                            @endif
                        
                        @elseif(Auth::user()->role == 'mahasiswa')
                            <li class="nav-item"><a class="nav-link" href="/krs">KRS</a></li>
                            <li class="nav-item"><a class="nav-link" href="/khs">KHS</a></li>
                            <li class="nav-item"><a class="nav-link" href="/transkrip">Transkrip</a></li>
                            <li class="nav-item"><a class="nav-link" href="/riwayat-pembayaran">Pembayaran</a></li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="nav-link">Log in</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="nav-link">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4 mb-5">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer mt-auto py-3 bg-dark text-white">
        <div class="container text-center">
            <span>&copy; {{ date('Y') }} SIAKAD STT GPI Papua</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
