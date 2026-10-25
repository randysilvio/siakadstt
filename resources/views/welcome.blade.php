<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $cfg = $cfg ?? \App\Models\Pengaturan::allCached();
        $logoUrl = \App\Models\Pengaturan::logoUrl();
        $favUrl  = !empty($cfg['favicon_path']) ? asset('storage/' . $cfg['favicon_path']) : $logoUrl;
    @endphp
    <title>{{ $cfg['singkatan_kampus'] }} — {{ $cfg['nama_kampus'] }}</title>

    <link rel="icon" href="{{ $favUrl }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#f0fdfa', 100: '#ccfbf1', 400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 900: '#134e4a' },
                    },
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                        heading: ['Raleway', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .hero-height { height: 76vh; min-height: 500px; }
        @media (max-width: 768px) { .hero-height { height: 62vh; min-height: 420px; } }
        .splide__slide img { width: 100%; height: 100%; object-fit: cover; }
        .glass-effect { background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        {{-- ================= 1. HERO SLIDESHOW SINEMATIS GAYA UKSW ================= --}}
        <section class="relative bg-slate-950 overflow-hidden">
            @if(isset($slides) && $slides->isNotEmpty())
                <div id="hero-slideshow" class="splide hero-height" aria-label="Galeri Kampus">
                    <div class="splide__track h-full">
                        <ul class="splide__list h-full">
                            @foreach($slides as $slide)
                            <li class="splide__slide relative">
                                <img src="{{ asset('storage/' . $slide->gambar) }}" alt="{{ $slide->judul }}">
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/65 to-slate-950/30 flex items-center">
                                    <div class="container mx-auto px-4 lg:px-8 max-w-7xl text-white">
                                        <span class="inline-flex items-center gap-2 py-1 px-3.5 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-bold tracking-widest uppercase mb-4">
                                            <i class="fa-solid fa-building-columns"></i> {{ $cfg['nama_badan_penyelenggara'] ?? $cfg['singkatan_kampus'] }}
                                        </span>
                                        <h1 class="font-heading text-3xl md:text-5xl lg:text-6xl font-black mb-4 leading-tight max-w-3xl">
                                            {{ $slide->judul }}
                                        </h1>
                                        @if($slide->kutipan)
                                            <p class="text-sm md:text-lg text-slate-200 max-w-2xl mb-8 font-light leading-relaxed">
                                                {{ $slide->kutipan }}
                                            </p>
                                        @endif
                                        <div class="flex flex-wrap gap-3">
                                            <a href="{{ route('pmb.register') }}" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold py-3 px-7 rounded-full transition shadow-lg text-xs uppercase tracking-wider">
                                                Daftar Mahasiswa Baru <i class="fa-solid fa-arrow-right ml-1.5"></i>
                                            </a>
                                            <a href="#prodi-finder" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/30 font-bold py-3 px-7 rounded-full transition text-xs uppercase tracking-wider">
                                                Jelajahi Program Studi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="hero-height relative flex items-center bg-cover bg-center" style="background-image: url('{{ asset('images/latar-welcome.jpg') }}');">
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-900/75 to-slate-900/40"></div>
                    <div class="relative z-10 container mx-auto px-4 lg:px-8 max-w-7xl text-white">
                        <span class="inline-flex items-center gap-2 py-1 px-3.5 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-bold tracking-widest uppercase mb-4">
                            <i class="fa-solid fa-graduation-cap"></i> {{ $cfg['bentuk_institusi'] }} Terakreditasi {{ $cfg['akreditasi_institusi'] }}
                        </span>
                        <h1 class="font-heading text-3xl md:text-5xl lg:text-6xl font-black mb-4 leading-tight max-w-4xl uppercase">
                            {{ $cfg['nama_kampus'] }}
                        </h1>
                        <p class="text-sm md:text-lg text-slate-200 max-w-2xl mb-8 font-light leading-relaxed">
                            {{ $cfg['motto_kampus'] }}
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('pmb.register') }}" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold py-3.5 px-8 rounded-full transition shadow-lg text-xs uppercase tracking-wider">
                                Pendaftaran Mahasiswa Baru (PMB) <i class="fa-solid fa-arrow-right ml-1.5"></i>
                            </a>
                            <a href="{{ route('publik.profil') }}" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/30 font-bold py-3.5 px-7 rounded-full transition text-xs uppercase tracking-wider">
                                Profil & Struktur Organisasi
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- ================= 2. FLOATING QUICK-ACTION BAR (PINTASAN EKOSISTEM KAMPUS) ================= --}}
        <section class="relative z-20 -mt-12 container mx-auto px-4 lg:px-8 max-w-7xl">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-4 md:p-6 grid grid-cols-2 md:grid-cols-5 gap-4">
                <a href="#prodi-finder" class="flex items-center gap-3.5 p-2.5 rounded-xl hover:bg-slate-50 transition group">
                    <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition shrink-0">
                        <i class="fa-solid fa-compass text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold text-slate-900 block uppercase">Cari Prodi</span>
                        <span class="text-[11px] text-slate-500">Kurikulum OBE</span>
                    </div>
                </a>
                <a href="{{ route('publik.profil') }}#struktur-yayasan" class="flex items-center gap-3.5 p-2.5 rounded-xl hover:bg-slate-50 transition group">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition shrink-0">
                        <i class="fa-solid fa-sitemap text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold text-slate-900 block uppercase">Struktur</span>
                        <span class="text-[11px] text-slate-500">Sinode & Kampus</span>
                    </div>
                </a>
                <a href="{{ route('publik.riset') }}" class="flex items-center gap-3.5 p-2.5 rounded-xl hover:bg-slate-50 transition group">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition shrink-0">
                        <i class="fa-solid fa-book-journal-whills text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold text-slate-900 block uppercase">Riset & PkM</span>
                        <span class="text-[11px] text-slate-500">Publikasi Dosen</span>
                    </div>
                </a>
                <a href="{{ route('publik.perpustakaan') }}" class="flex items-center gap-3.5 p-2.5 rounded-xl hover:bg-slate-50 transition group">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition shrink-0">
                        <i class="fa-solid fa-book-open-reader text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold text-slate-900 block uppercase">E-Library</span>
                        <span class="text-[11px] text-slate-500">{{ number_format($totalKoleksi ?? 0) }} Judul Buku</span>
                    </div>
                </a>
                <a href="{{ route('dosen.public.index') }}" class="col-span-2 md:col-span-1 flex items-center gap-3.5 p-2.5 rounded-xl hover:bg-slate-50 transition group">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition shrink-0">
                        <i class="fa-solid fa-user-tie text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold text-slate-900 block uppercase">Direktori</span>
                        <span class="text-[11px] text-slate-500">{{ number_format($totalDosen ?? 0) }} Pengajar</span>
                    </div>
                </a>
            </div>
        </section>

        {{-- ================= 3. BANNER GELOMBANG PMB AKTIF (JIKA ADA) ================= --}}
        @if(isset($periodePmbAktif) && $periodePmbAktif)
            <section class="container mx-auto px-4 lg:px-8 max-w-7xl mt-8">
                <div class="bg-gradient-to-r from-teal-900 via-slate-900 to-slate-950 rounded-2xl p-6 md:px-10 text-white shadow-lg flex flex-col md:flex-row justify-between items-center gap-4 border border-teal-700/40">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-xl shrink-0">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-amber-300 block">Penerimaan Mahasiswa Baru Sedang Dibuka</span>
                            <h3 class="text-lg md:text-xl font-heading font-extrabold uppercase">{{ $periodePmbAktif->nama_gelombang }}</h3>
                            <p class="text-xs text-slate-300 mt-0.5">
                                Periode Pendaftaran: <strong>{{ \Carbon\Carbon::parse($periodePmbAktif->tanggal_buka)->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($periodePmbAktif->tanggal_tutup)->translatedFormat('d M Y') }}</strong>
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('pmb.register') }}" class="bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold px-6 py-3 rounded-xl text-xs uppercase tracking-wider transition shrink-0 shadow">
                        Registrasi Online Sekarang <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </section>
        @endif

        {{-- ================= 4. SAMBUTAN PIMPINAN & KAMPUS DALAM ANGKA ================= --}}
        <section class="py-16 container mx-auto px-4 lg:px-8 max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-7 bg-white p-8 md:p-10 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-teal-600 font-extrabold text-xs uppercase tracking-widest block mb-2">
                        {{ $cfg['judul_sambutan'] ?? 'Sambutan Pimpinan' }}
                    </span>
                    <h2 class="font-heading text-2xl md:text-3xl font-extrabold text-slate-900 mb-4">
                        Membangun Generasi Pelayan & Cendekiawan yang Berintegritas
                    </h2>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6 text-justify">
                        {{ $cfg['isi_sambutan'] }}
                    </p>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-5">
                        <div class="flex items-center gap-3.5">
                            @if(!empty($cfg['foto_pimpinan_path']))
                                <img src="{{ asset('storage/' . $cfg['foto_pimpinan_path']) }}" alt="Pimpinan" class="w-14 h-14 rounded-full object-cover border-2 border-teal-600">
                            @else
                                <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold">
                                    {{ substr($cfg['nama_rektor'], 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h4 class="font-bold text-sm text-slate-900 uppercase">{{ $cfg['nama_rektor'] }}</h4>
                                <span class="text-xs text-teal-600 font-semibold">{{ $cfg['sebutan_pimpinan'] }} {{ $cfg['singkatan_kampus'] }}</span>
                            </div>
                        </div>
                        <a href="{{ route('publik.profil') }}" class="text-xs font-extrabold text-slate-900 hover:text-teal-600 uppercase tracking-wider">
                            Selengkapnya <i class="fa-solid fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Statistik Impact Kampus (Gaya UKSW) --}}
                <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                    <div class="bg-pub-primary text-white p-6 rounded-2xl shadow-md">
                        <i class="fa-solid fa-graduation-cap text-2xl text-amber-400 mb-3 block"></i>
                        <div class="text-3xl font-heading font-black">{{ $programStudi->count() }}</div>
                        <div class="text-xs text-white/80 font-semibold uppercase tracking-wider mt-1">Program Studi Aktif</div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <i class="fa-solid fa-users text-2xl text-teal-600 mb-3 block"></i>
                        <div class="text-3xl font-heading font-black text-slate-900">{{ number_format($totalMahasiswa) }}</div>
                        <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mt-1">Mahasiswa Aktif</div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <i class="fa-solid fa-chalkboard-user text-2xl text-teal-600 mb-3 block"></i>
                        <div class="text-3xl font-heading font-black text-slate-900">{{ number_format($totalDosen) }}</div>
                        <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mt-1">Dosen & Peneliti</div>
                    </div>
                    <div class="bg-teal-700 text-white p-6 rounded-2xl shadow-md">
                        <i class="fa-solid fa-briefcase text-2xl text-amber-300 mb-3 block"></i>
                        <div class="text-3xl font-heading font-black">{{ $persenSerapanAlumni }}%</div>
                        <div class="text-xs text-teal-100 font-semibold uppercase tracking-wider mt-1">Serapan Pelayanan Alumni</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= 5. INTERACTIVE PROGRAM STUDI FINDER (GAYA UKSW) ================= --}}
        <section id="prodi-finder" class="py-16 bg-white border-y border-gray-100">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
                <div class="flex flex-col md:flex-row justify-between items-md-end mb-10 gap-4">
                    <div>
                        <span class="text-teal-600 font-extrabold text-xs uppercase tracking-widest block mb-2">Program Akademik Unggulan</span>
                        <h2 class="font-heading text-2xl md:text-3xl font-black text-slate-900 uppercase">Temukan Program Studi Pilihan Anda</h2>
                    </div>
                    <div class="w-full md:w-80">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                            <input type="text" id="prodiSearchInput" class="w-full pl-9 pr-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold focus:outline-none focus:border-teal-600" placeholder="Ketik nama program studi...">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="prodiGrid">
                    @foreach($programStudi as $prodi)
                        <div class="prodi-card bg-slate-50 rounded-2xl p-7 border border-gray-100 hover:border-teal-500 hover:bg-white hover:shadow-xl transition-all duration-300 flex flex-col justify-between" data-name="{{ strtolower($prodi->nama_prodi . ' ' . $prodi->kode_prodi) }}">
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-[10px] font-mono font-bold uppercase px-2.5 py-1 bg-teal-100 text-teal-800 rounded-md">
                                        KODE: {{ $prodi->kode_prodi ?? 'S1' }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase text-slate-400">
                                        <i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i> Kurikulum OBE
                                    </span>
                                </div>
                                <h3 class="font-heading text-lg font-extrabold text-slate-900 mb-2 uppercase">{{ $prodi->nama_prodi }}</h3>
                                <p class="text-xs text-slate-500 leading-relaxed mb-5">
                                    Menyelenggarakan pendidikan akademik berbasis capaian pembelajaran (OBE), riset kontekstual, dan praktik pelayanan terbimbing.
                                </p>
                            </div>
                            <div class="border-t border-gray-200/70 pt-4 flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">
                                    Kaprodi: <strong class="text-slate-800">{{ optional($prodi->kaprodi)->nama_lengkap ?? 'Terjadwal' }}</strong>
                                </span>
                                <a href="{{ route('pmb.register') }}" class="text-teal-600 font-extrabold uppercase text-[11px] hover:text-teal-800 shrink-0">
                                    Daftar <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= 6. SOROTAN RISET, PUBLIKASI & PENGABDIAN DOSEN ================= --}}
        <section class="py-16 bg-slate-900 text-white">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
                <div class="flex flex-col md:flex-row justify-between items-md-end mb-10 gap-4">
                    <div>
                        <span class="text-amber-400 font-extrabold text-xs uppercase tracking-widest block mb-2">Tridharma Perguruan Tinggi</span>
                        <h2 class="font-heading text-2xl md:text-3xl font-black uppercase">Publikasi Ilmiah & Pengabdian Masyarakat</h2>
                    </div>
                    <a href="{{ route('publik.riset') }}" class="inline-flex items-center text-xs font-extrabold uppercase tracking-wider text-amber-400 hover:text-amber-300">
                        Lihat Seluruh Repositori Riset <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @forelse($risetTerbaru as $karya)
                        <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 flex flex-col justify-between hover:border-amber-400/60 transition">
                            <div>
                                <div class="flex justify-between items-center gap-2 mb-3">
                                    <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded {{ $karya->unsur == 'penelitian' ? 'bg-amber-400/20 text-amber-300' : 'bg-emerald-400/20 text-emerald-300' }}">
                                        {{ strtoupper($karya->unsur) }}
                                    </span>
                                    <span class="text-xs font-mono text-slate-400 font-bold">{{ $karya->tahun }}</span>
                                </div>
                                <h4 class="font-bold text-sm text-white leading-snug mb-2 line-clamp-3">{{ $karya->judul }}</h4>
                                <p class="text-[11px] text-slate-400 mb-4">{{ $karya->nama_jurnal_penerbit_lokasi ?? $karya->kategori }}</p>
                            </div>
                            <div class="border-t border-slate-700 pt-3 flex justify-between items-center text-[11px]">
                                <span class="text-slate-300 font-semibold truncate max-w-[160px]">
                                    <i class="fa-solid fa-user-pen text-amber-400 mr-1"></i> {{ $karya->dosen->nama_lengkap ?? 'Dosen' }}
                                </span>
                                @if($karya->tautan_eksternal)
                                    <a href="{{ $karya->tautan_eksternal }}" target="_blank" class="text-amber-400 hover:underline font-bold">Buka <i class="fa-solid fa-up-right-from-square text-[9px]"></i></a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-4 text-center py-8 text-slate-400 text-sm">
                            Data publikasi ilmiah dan pengabdian masyarakat dosen terhubung langsung dengan modul Portofolio Tridharma SIAKAD.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ================= 7. WARTA KAMPUS, AGENDA & DOKUMEN PUBLIK ================= --}}
        <section class="py-16 container mx-auto px-4 lg:px-8 max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                {{-- Kolom Kiri (8): Berita & Warta Kampus --}}
                <div class="lg:col-span-8">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <span class="text-teal-600 font-extrabold text-xs uppercase tracking-widest block mb-1">Aktualita Kampus</span>
                            <h2 class="font-heading text-2xl font-black text-slate-900 uppercase">Berita & Pengumuman Terbaru</h2>
                        </div>
                        <a href="{{ route('berita.index') }}" class="text-xs font-extrabold text-teal-600 hover:text-teal-800 uppercase">Semua Berita <i class="fa-solid fa-arrow-right ml-1"></i></a>
                    </div>

                    @if($beritaUtama)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6 grid grid-cols-1 md:grid-cols-12">
                            <div class="md:col-span-5 bg-slate-200 min-h-[200px]">
                                @if($beritaUtama->foto)
                                    <img src="{{ asset('storage/' . $beritaUtama->foto) }}" alt="{{ $beritaUtama->judul }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-slate-800 text-white/30 text-4xl"><i class="fa-regular fa-newspaper"></i></div>
                                @endif
                            </div>
                            <div class="md:col-span-7 p-6 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 font-mono mb-2">
                                        <span class="bg-teal-50 text-teal-700 font-sans font-bold px-2 py-0.5 rounded uppercase">{{ $beritaUtama->kategori ?? 'Warta' }}</span>
                                        <span>{{ $beritaUtama->created_at->translatedFormat('d M Y') }}</span>
                                    </div>
                                    <h3 class="font-heading font-bold text-lg text-slate-900 mb-2">
                                        <a href="{{ route('pengumuman.public.show', $beritaUtama) }}" class="hover:text-teal-600 transition">{{ $beritaUtama->judul }}</a>
                                    </h3>
                                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">{{ Str::limit(strip_tags($beritaUtama->konten), 160) }}</p>
                                </div>
                                <a href="{{ route('pengumuman.public.show', $beritaUtama) }}" class="text-xs font-bold text-teal-600 mt-4 inline-block">Baca Selengkapnya &rarr;</a>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($beritaLainnya as $b)
                            <a href="{{ route('pengumuman.public.show', $b) }}" class="bg-white p-5 rounded-xl border border-gray-100 hover:border-teal-500 transition shadow-2xs flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] font-mono text-slate-400 block mb-1">{{ $b->created_at->translatedFormat('d M Y') }}</span>
                                    <h4 class="font-bold text-xs text-slate-800 line-clamp-2 leading-snug">{{ $b->judul }}</h4>
                                </div>
                                <span class="text-[11px] font-bold text-teal-600 mt-3 block">Selengkapnya &rarr;</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Kolom Kanan (4): Agenda Akademik & Dokumen Resmi --}}
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="font-heading font-extrabold text-sm uppercase text-slate-900 mb-4 pb-2 border-b flex items-center justify-between">
                            <span><i class="fa-regular fa-calendar-check text-teal-600 mr-2"></i> Agenda Akademik</span>
                        </h3>
                        <div class="space-y-3">
                            @forelse($kegiatanTerdekat as $keg)
                                <div class="flex items-center gap-3.5 p-2.5 rounded-xl bg-slate-50 border border-gray-100">
                                    <div class="bg-pub-primary text-white rounded-lg px-2.5 py-1.5 text-center shrink-0">
                                        <span class="block text-sm font-black leading-none">{{ \Carbon\Carbon::parse($keg->tanggal_mulai)->format('d') }}</span>
                                        <span class="block text-[9px] uppercase">{{ \Carbon\Carbon::parse($keg->tanggal_mulai)->translatedFormat('M') }}</span>
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-800 leading-snug">{{ $keg->judul_kegiatan }}</h5>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ \Carbon\Carbon::parse($keg->tanggal_mulai)->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada agenda akademik terdekat.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="font-heading font-extrabold text-sm uppercase text-slate-900">
                                <i class="fa-solid fa-file-pdf text-rose-600 mr-2"></i> Dokumen Publik
                            </h3>
                            <a href="{{ route('dokumen.publik') }}" class="text-[11px] font-bold text-teal-600">Semua</a>
                        </div>
                        <ul class="space-y-2">
                            @forelse($dokumen as $doc)
                                <li>
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-gray-200 transition text-xs">
                                        <span class="font-semibold text-slate-700 truncate pr-2">{{ $doc->judul }}</span>
                                        <i class="fa-solid fa-download text-teal-600 shrink-0"></i>
                                    </a>
                                </li>
                            @empty
                                <li class="text-xs text-slate-400 text-center py-3">Belum ada dokumen publik.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('partials.public_footer')

    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('hero-slideshow')) {
                new Splide('#hero-slideshow', {
                    type: 'fade',
                    rewind: true,
                    autoplay: true,
                    interval: 5500,
                    arrows: false,
                    pagination: true,
                }).mount();
            }

            // Live Filter Program Studi Finder
            const prodiInput = document.getElementById('prodiSearchInput');
            if (prodiInput) {
                prodiInput.addEventListener('input', function () {
                    const q = this.value.toLowerCase().trim();
                    document.querySelectorAll('.prodi-card').forEach(card => {
                        const name = card.getAttribute('data-name') || '';
                        card.style.display = name.includes(q) ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>