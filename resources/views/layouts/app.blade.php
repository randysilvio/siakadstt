<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Administrasi Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">SAK</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(Auth::user()->role == 'admin')
                            <li class="nav-item"><a class="nav-link" href="/mahasiswa">Mahasiswa</a></li>
                            <li class="nav-item"><a class="nav-link" href="/program-studi">Program Studi</a></li>
                            <li class="nav-item"><a class="nav-link" href="/mata-kuliah">Mata Kuliah</a></li>
                            <li class="nav-item"><a class="nav-link" href="/dosen">Dosen</a></li>
                            <li class="nav-item"><a class="nav-link" href="/pengumuman">Pengumuman</a></li>
                            <li class="nav-item"><a class="nav-link" href="/pembayaran">Pembayaran</a></li>
                            <li class="nav-item"><a class="nav-link" href="/nilai">Input Nilai</a></li>
                            <li class="nav-item"><a class="nav-link" href="/pengaturan">Pengaturan</a></li>
                            <li class="nav-item"><a class="nav-link" href="/tahun-akademik">Tahun Akademik</a></li>
                        @elseif(Auth::user()->role == 'dosen')
                            <li class="nav-item"><a class="nav-link" href="{{ route('dosen.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('perwalian.index') }}">Mahasiswa Wali</a></li>
                            {{-- GUNAKAN FUNGSI BARU UNTUK MEMERIKSA PERAN KAPRODI --}}
                            @if(Auth::user()->isKaprodi())
                                <li class="nav-item"><a class="nav-link" href="{{ route('kaprodi.dashboard') }}">Portal Kaprodi</a></li>
                            @endif
                        @else
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

    <main class="container mt-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>