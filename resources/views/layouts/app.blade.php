<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Administrasi Kampus</title>
    
    {{-- Memuat semua CSS utama dari CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    {{-- Memuat CSS Tailwind hanya jika di halaman berita untuk menghindari konflik --}}
    @if(request()->routeIs('berita.index'))
        @vite('resources/css/app.css')
    @endif
    
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Navigasi Utama --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
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
                        {{-- Menu Pustakawan --}}
                        @if (Auth::user()->hasRole('pustakawan'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('kalender.halaman') }}">Kalender Akademik</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('perpustakaan.koleksi.index') }}">Manajemen Koleksi</a></li>
                        @else
                            {{-- Menu Default --}}
                            <li class="nav-item"><a class="nav-link" href="{{ route('kalender.halaman') }}">Kalender Akademik</a></li>
                            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('dosen') || Auth::user()->hasRole('mahasiswa'))
                                <li class="nav-item"><a class="nav-link" href="{{ route('verum.index') }}">Verum</a></li>
                            @endif
                            
                            {{-- Menu Admin --}}
                            @if(Auth::user()->hasRole('admin'))
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="akademikDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Akademik</a>
                                    <ul class="dropdown-menu" aria-labelledby="akademikDropdown">
                                        <li><a class="dropdown-item" href="{{ route('admin.tahun-akademik.index') }}">Tahun Akademik</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.program-studi.index') }}">Program Studi</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.kurikulum.index') }}">Kurikulum</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.mata-kuliah.index') }}">Mata Kuliah</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.kalender.index') }}">Manajemen Kalender</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.nilai.index') }}">Input Nilai</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="penggunaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Pengguna</a>
                                    <ul class="dropdown-menu" aria-labelledby="penggunaDropdown">
                                        <li><a class="dropdown-item" href="{{ route('admin.mahasiswa.index') }}">Manajemen Mahasiswa</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.dosen.index') }}">Manajemen Dosen</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.user.index') }}">Manajemen Pengguna & Peran</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="kontenDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Konten Publik</a>
                                    <ul class="dropdown-menu" aria-labelledby="kontenDropdown">
                                        <li><a class="dropdown-item" href="{{ route('admin.pengumuman.index') }}">Manajemen Pengumuman</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.slideshows.index') }}">Manajemen Slideshow</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.dokumen-publik.index') }}">Manajemen Dokumen</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="sistemDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Sistem</a>
                                    <ul class="dropdown-menu" aria-labelledby="sistemDropdown">
                                        <li><a class="dropdown-item" href="{{ route('pembayaran.index') }}">Manajemen Pembayaran</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        
                                        {{-- ===== UPDATE DISINI: Menambahkan Menu Hasil Evaluasi ===== --}}
                                        <li><h6 class="dropdown-header">Manajemen Evaluasi</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.evaluasi-sesi.index') }}">Sesi Evaluasi</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.evaluasi-pertanyaan.index') }}">Pertanyaan Evaluasi</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.evaluasi-hasil.index') }}">Hasil Evaluasi</a></li> {{-- <-- BARU --}}
                                        
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.pengaturan.index') }}">Pengaturan Sistem</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="absensiDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Absensi</a>
                                    <ul class="dropdown-menu" aria-labelledby="absensiDropdown">
                                        <li><a class="dropdown-item" href="{{ route('admin.absensi.laporan.index') }}">Laporan Absensi</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.absensi.lokasi.index') }}">Manajemen Lokasi</a></li>
                                    </ul>
                                </li>
                            @endif

                            {{-- Menu Dosen --}}
                            @if(Auth::user()->hasRole('dosen'))
                                <li class="nav-item"><a class="nav-link" href="{{ route('perwalian.index') }}">Mahasiswa Wali</a></li>
                            @endif
                            @if(Auth::user()->isKaprodi())
                                <li class="nav-item"><a class="nav-link" href="{{ route('kaprodi.dashboard') }}">Portal Kaprodi</a></li>
                            @endif
                            @if(Auth::user()->hasRole('keuangan'))
                                 <li class="nav-item"><a class="nav-link" href="{{ route('pembayaran.index') }}">Manajemen Pembayaran</a></li>
                            @endif
                            
                            {{-- Menu Mahasiswa --}}
                            @if(Auth::user()->hasRole('mahasiswa'))
                                <li class="nav-item"><a class="nav-link" href="{{ route('krs.index') }}">KRS</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('khs.index') }}">KHS</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('transkrip.index') }}">Transkrip</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('pembayaran.riwayat') }}">Pembayaran</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('evaluasi.index') }}">Evaluasi Dosen</a></li>
                            @endif
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ Auth::user()->name }}</a>
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
                        <li class="nav-item"><a href="{{ route('login') }}" class="nav-link">Log in</a></li>
                        <li class="nav-item"><a href="{{ route('register') }}" class="nav-link">Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4 mb-5">
        @yield('content')
    </main>

    <footer class="footer mt-auto py-3 bg-dark text-white">
        <div class="container text-center">
            <span>&copy; {{ date('Y') }} SIAKAD STT GPI Papua</span>
        </div>
    </footer>

    @include('partials.chatbot')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @stack('scripts')
</body>
</html>