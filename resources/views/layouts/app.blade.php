<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Administrasi Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Stack untuk CSS per halaman --}}
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">SIAKAD</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        {{-- Link Kalender Akademik untuk semua role --}}
                        <li class="nav-item"><a class="nav-link" href="{{ route('kalender.halaman') }}">Kalender Akademik</a></li>
                        
                        {{-- Link Verum untuk semua role --}}
                        <li class="nav-item"><a class="nav-link" href="{{ route('verum.index') }}">Verum</a></li>

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
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="/pembayaran">Pembayaran</a></li>
                            <li class="nav-item"><a class="nav-link" href="/pengumuman">Pengumuman</a></li>
                            <li class="nav-item"><a class="nav-link" href="/pengaturan">Pengaturan</a></li>

                        @elseif(Auth::user()->role == 'dosen')
                            <li class="nav-item"><a class="nav-link" href="{{ route('dosen.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('perwalian.index') }}">Mahasiswa Wali</a></li>
                            @if(Auth::user()->isKaprodi())
                                <li class="nav-item"><a class="nav-link" href="{{ route('kaprodi.dashboard') }}">Portal Kaprodi</a></li>
                            @endif
                            @if(Auth::user()->dosen?->is_keuangan)
                                <li class="nav-item"><a class="nav-link" href="/pembayaran">Pembayaran</a></li>
                            @endif
                        @else {{-- Mahasiswa --}}
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
                                @if(Auth::user()->role == 'admin')
                                    <li><a class="dropdown-item" href="{{ route('kalender.index') }}">Manajemen Kalender</a></li>
                                @endif
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

    <main class="container mt-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Stack untuk JavaScript per halaman --}}
    @stack('scripts')
</body>
</html>
