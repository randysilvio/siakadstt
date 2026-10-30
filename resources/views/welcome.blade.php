<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $cfg = $cfg ?? \App\Models\Pengaturan::allCached();
        $logoUrl   = \App\Models\Pengaturan::logoUrl();
        $eventLogo = \App\Models\Pengaturan::eventLogoUrl();
        $favUrl    = !empty($cfg['favicon_path']) ? asset('storage/' . $cfg['favicon_path']) : $logoUrl;
    @endphp
    <title>{{ $cfg['singkatan_kampus'] }} — {{ $cfg['nama_kampus'] }}</title>
    @include('partials._meta_sosial', [])

    <link rel="icon" href="{{ $favUrl }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|merriweather:700,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Merriweather', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .hero-height { height: 550px; }
        @media (max-width: 768px) { .hero-height { height: 430px; } }
        .splide__slide img { width: 100%; height: 100%; object-fit: cover; }

        /* Efek Majalah Pop-Out untuk Foto PNG Pimpinan */
        .magazine-portrait-stage {
            position: relative;
            overflow: visible;
        }
        .magazine-portrait-img {
            filter: drop-shadow(0 14px 20px rgba(15, 23, 42, 0.22));
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .magazine-portrait-stage:hover .magazine-portrait-img {
            transform: translateY(-4px) scale(1.02);
        }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        {{-- ================= 1. HERO BANNER ================= --}}
        <section class="relative bg-slate-900 overflow-hidden border-b border-slate-200">
            @if(isset($slides) && $slides->isNotEmpty())
                <div id="hero-slideshow" class="splide hero-height" aria-label="Galeri Kampus">
                    <div class="splide__track h-full">
                        <ul class="splide__list h-full">
                            @foreach($slides as $slide)
                            <li class="splide__slide relative">
                                <img src="{{ asset('storage/' . $slide->gambar) }}" alt="{{ $slide->judul }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/55 to-slate-950/20 flex items-end pb-14 md:pb-16">
                                    <div class="container mx-auto px-4 lg:px-8 max-w-7xl text-white animate-fade-up">
                                        <span class="inline-block px-3 py-1 bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-mono uppercase tracking-widest mb-3 pub-radius">
                                            {{ $cfg['nama_badan_penyelenggara'] ?? $cfg['singkatan_kampus'] }}
                                        </span>
                                        <h1 class="font-serif text-2xl md:text-4xl lg:text-5xl font-bold mb-3 leading-tight max-w-3xl">
                                            {{ $slide->judul }}
                                        </h1>
                                        @if($slide->kutipan)
                                            <p class="text-sm md:text-base text-slate-200 max-w-2xl mb-6 leading-relaxed">
                                                {{ $slide->kutipan }}
                                            </p>
                                        @endif
                                        <div class="flex flex-wrap gap-3">
                                            <a href="{{ route('pmb.register') }}" class="bg-white text-slate-900 hover:bg-slate-100 font-bold py-2.5 px-6 pub-radius text-xs uppercase tracking-wider transition shadow-sm">
                                                Pendaftaran Mahasiswa Baru
                                            </a>
                                            <a href="#prodi-finder" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/30 font-bold py-2.5 px-6 pub-radius text-xs uppercase tracking-wider transition">
                                                Program Studi
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
                <div class="hero-height relative flex items-end pb-14 md:pb-16 bg-cover bg-center" style="background-image: url('{{ asset('images/latar-welcome.jpg') }}');">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/60 to-slate-950/25"></div>
                    <div class="relative z-10 container mx-auto px-4 lg:px-8 max-w-7xl text-white animate-fade-up">
                        <span class="inline-block px-3 py-1 bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-mono uppercase tracking-widest mb-3 pub-radius">
                            {{ $cfg['nama_badan_penyelenggara'] ?? $cfg['singkatan_kampus'] }}
                        </span>
                        <h1 class="font-serif text-2xl md:text-4xl lg:text-5xl font-bold mb-3 leading-tight max-w-4xl">
                            {{ $cfg['nama_kampus'] }}
                        </h1>
                        <p class="text-sm md:text-base text-slate-200 max-w-2xl mb-6 leading-relaxed">
                            {{ $cfg['motto_kampus'] }}
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('pmb.register') }}" class="bg-white text-slate-900 hover:bg-slate-100 font-bold py-2.5 px-6 pub-radius text-xs uppercase tracking-wider transition shadow-sm">
                                Pendaftaran Mahasiswa Baru
                            </a>
                            <a href="{{ route('publik.profil') }}" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/30 font-bold py-2.5 px-6 pub-radius text-xs uppercase tracking-wider transition">
                                Profil Institusi
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- ================= 2. PINTASAN DIREKTORI KAMPUS (BERSIH TANPA WATERMARK BERULANG) ================= --}}
        <section class="bg-white border-b border-slate-200 py-5">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
                    <a href="#prodi-finder" class="hover-lift pub-radius p-3.5 border border-slate-200 hover-border-pub-primary bg-slate-50/60 hover:bg-white block">
                        <span class="text-[10px] font-mono uppercase text-slate-500 block">Akademik</span>
                        <span class="text-xs font-bold text-slate-900 mt-0.5 block">Program Studi</span>
                    </a>
                    <a href="{{ route('publik.profil') }}#struktur-yayasan" class="hover-lift pub-radius p-3.5 border border-slate-200 hover-border-pub-primary bg-slate-50/60 hover:bg-white block">
                        <span class="text-[10px] font-mono uppercase text-slate-500 block">Tata Kelola</span>
                        <span class="text-xs font-bold text-slate-900 mt-0.5 block">Struktur Organisasi</span>
                    </a>
                    <a href="{{ route('dosen.public.index') }}" class="hover-lift pub-radius p-3.5 border border-slate-200 hover-border-pub-primary bg-slate-50/60 hover:bg-white block">
                        <span class="text-[10px] font-mono uppercase text-slate-500 block">SDM</span>
                        <span class="text-xs font-bold text-slate-900 mt-0.5 block">Direktori Dosen</span>
                    </a>
                    <a href="{{ route('publik.perpustakaan') }}" class="hover-lift pub-radius p-3.5 border border-slate-200 hover-border-pub-primary bg-slate-50/60 hover:bg-white block">
                        <span class="text-[10px] font-mono uppercase text-slate-500 block">Pustaka</span>
                        <span class="text-xs font-bold text-slate-900 mt-0.5 block">Katalog Perpustakaan</span>
                    </a>
                    <a href="{{ route('publik.riset') }}" class="col-span-2 md:col-span-1 hover-lift pub-radius p-3.5 border border-slate-200 hover-border-pub-primary bg-slate-50/60 hover:bg-white block">
                        <span class="text-[10px] font-mono uppercase text-slate-500 block">Tridharma</span>
                        <span class="text-xs font-bold text-slate-900 mt-0.5 block">Penelitian & PkM</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- ================= 3. BANNER GELOMBANG PMB AKTIF ================= --}}
        @if(isset($periodePmbAktif) && $periodePmbAktif)
            <section class="container mx-auto px-4 lg:px-8 max-w-7xl mt-8">
                <div class="wm-featured wm-featured-dark bg-pub-primary text-white p-6 md:px-8 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-white/75 block mb-1">Penerimaan Mahasiswa Baru</span>
                        <h2 class="text-base md:text-lg font-bold uppercase">{{ $periodePmbAktif->nama_gelombang }}</h2>
                        <p class="text-xs text-white/80 mt-0.5">
                            Periode Pendaftaran: {{ \Carbon\Carbon::parse($periodePmbAktif->tanggal_buka)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($periodePmbAktif->tanggal_tutup)->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <a href="{{ route('pmb.register') }}" class="bg-white text-slate-900 hover:bg-slate-100 font-bold px-6 py-2.5 pub-radius text-xs uppercase tracking-wider transition shrink-0">
                        Daftar Sekarang
                    </a>
                </div>
            </section>
        @endif

        {{-- ================= 4. SAMBUTAN PIMPINAN (EFEK MAJALAH POP-OUT KELUAR FRAME) & STATISTIK ================= --}}
        <section class="py-16 container mx-auto px-4 lg:px-8 max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch pt-6">
                
                {{-- KARTU SAMBUTAN PIMPINAN DENGAN POTRET POP-OUT KELUAR BINGKAI + WATERMARK EKSKLUSIF --}}
                <div class="lg:col-span-8 bg-white pub-radius shadow-2xs border border-slate-200 relative magazine-portrait-stage flex flex-col md:flex-row">
                    
                    @if(!empty($cfg['foto_pimpinan_path']))
                        {{-- Panggung Kiri: Potret PNG Pimpinan Menembus Bingkai Atas Kartu --}}
                        <div class="md:w-5/12 bg-slate-100/80 border-b md:border-b-0 md:border-r border-slate-200/80 flex flex-col items-center justify-end px-4 pt-6 md:pt-0 relative" style="border-top-left-radius: var(--pub-radius); border-bottom-left-radius: var(--pub-radius);">
                            <img src="{{ asset('storage/' . $cfg['foto_pimpinan_path']) }}"
                                 alt="{{ $cfg['nama_rektor'] }}"
                                 class="magazine-portrait-img w-auto h-64 md:h-80 object-contain object-bottom -mt-12 md:-mt-14 relative z-20 select-none">
                        </div>
                    @endif

                    {{-- Kolom Teks Sambutan dengan Watermark Eksklusif di Latar Kanan Bawah --}}
                    <div class="{{ !empty($cfg['foto_pimpinan_path']) ? 'md:w-7/12' : 'w-full' }} wm-featured p-7 md:p-9 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-xs font-mono uppercase tracking-widest text-slate-500">
                                    {{ $cfg['judul_sambutan'] ?? 'Sambutan Pimpinan Institusi' }}
                                </span>
                                @if($eventLogo)
                                    <img src="{{ $eventLogo }}" alt="Event Logo" class="h-8 w-auto object-contain" title="{{ $cfg['label_event_publik'] ?? '' }}">
                                @endif
                            </div>

                            <h2 class="font-serif text-xl md:text-2xl font-bold text-slate-900 mb-4 leading-snug">
                                {{ $cfg['nama_kampus'] }}
                            </h2>

                            <p class="text-slate-600 text-sm leading-relaxed mb-6 text-justify">
                                {{ $cfg['isi_sambutan'] }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 mt-2">
                            <div>
                                <h4 class="font-bold text-sm text-slate-900 uppercase tracking-wide">{{ $cfg['nama_rektor'] }}</h4>
                                <span class="text-xs text-slate-500 font-medium">{{ $cfg['sebutan_pimpinan'] }} {{ $cfg['singkatan_kampus'] }}</span>
                            </div>
                            <a href="{{ route('publik.profil') }}" class="text-xs font-bold text-pub-primary hover:underline uppercase tracking-wider">
                                Profil Lengkap &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                {{-- 4 KARTU STATISTIK: HANYA 1 KARTU HIGHLIGHT UTAMA YANG MEMILIKI WATERMARK EKSKLUSIF, 3 LAINNYA BERSIH --}}
                <div class="lg:col-span-4 grid grid-cols-2 gap-4">
                    {{-- Kartu 1 (Highlight Utama Ber-Watermark) --}}
                    <div class="wm-featured wm-featured-dark hover-lift bg-pub-primary text-white p-6 shadow-2xs flex flex-col justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-white/75">Akademik</span>
                        <div class="my-2">
                            <div class="text-3xl font-serif font-bold">{{ $programStudi->count() }}</div>
                            <div class="text-xs text-white/90 font-semibold uppercase tracking-wide mt-1">Program Studi</div>
                        </div>
                    </div>

                    {{-- Kartu 2 (Bersih Polos) --}}
                    <div class="hover-lift pub-radius bg-white p-6 shadow-2xs border border-slate-200 border-t-4 border-t-pub-primary flex flex-col justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-slate-400">Civitas</span>
                        <div class="my-2">
                            <div class="text-3xl font-serif font-bold text-slate-900">{{ number_format($totalMahasiswa) }}</div>
                            <div class="text-xs text-slate-600 font-semibold uppercase tracking-wide mt-1">Mahasiswa Aktif</div>
                        </div>
                    </div>

                    {{-- Kartu 3 (Bersih Polos) --}}
                    <div class="hover-lift pub-radius bg-white p-6 shadow-2xs border border-slate-200 border-t-4 border-t-pub-primary flex flex-col justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-slate-400">Pendidik</span>
                        <div class="my-2">
                            <div class="text-3xl font-serif font-bold text-slate-900">{{ number_format($totalDosen) }}</div>
                            <div class="text-xs text-slate-600 font-semibold uppercase tracking-wide mt-1">Dosen Pengajar</div>
                        </div>
                    </div>

                    {{-- Kartu 4 (Bersih Polos) --}}
                    <div class="hover-lift pub-radius bg-white p-6 shadow-2xs border border-slate-200 border-t-4 border-t-pub-primary flex flex-col justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-slate-400">Lulusan</span>
                        <div class="my-2">
                            <div class="text-3xl font-serif font-bold text-slate-900">{{ number_format($totalAlumni ?? 0) }}</div>
                            <div class="text-xs text-slate-600 font-semibold uppercase tracking-wide mt-1">Alumni Terdaftar</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= 5. DAFTAR PROGRAM STUDI (TERHUBUNG KE HALAMAN KURIKULUM SEMESTER 1-8) ================= --}}
        <section id="prodi-finder" class="py-14 bg-white border-y border-slate-200">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                    <div>
                        <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block mb-1">Akademik</span>
                        <h2 class="font-serif text-xl md:text-2xl font-bold text-slate-900">Program Studi & Kurikulum</h2>
                    </div>
                    <div class="w-full md:w-72">
                        <input type="text" id="prodiSearchInput" class="w-full px-3.5 py-2 pub-radius border border-slate-300 text-xs font-medium focus:outline-none focus:border-slate-900" placeholder="Cari program studi...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="prodiGrid">
                    @foreach($programStudi as $prodi)
                        <div class="prodi-card hover-lift pub-radius bg-slate-50/50 p-6 border border-slate-200 border-t-4 border-t-pub-primary hover:bg-white flex flex-col justify-between" data-name="{{ strtolower($prodi->nama_prodi . ' ' . $prodi->kode_prodi) }}">
                            <div>
                                <div class="flex justify-between items-center text-[11px] font-mono text-slate-500 mb-3 pb-2 border-b border-slate-200/80">
                                    <span>KODE: <strong class="text-slate-800">{{ $prodi->kode_prodi ?? '-' }}</strong></span>
                                    <span class="px-2 py-0.5 bg-slate-200/70 text-slate-800 font-bold rounded">JENJANG {{ $prodi->jenjang ?? 'S1' }}</span>
                                </div>
                                <h3 class="font-serif text-lg font-bold text-slate-900 mb-4">
                                    <a href="{{ route('publik.prodi.show', $prodi->id) }}" class="hover:underline">{{ $prodi->nama_prodi }}</a>
                                </h3>
                                <div class="space-y-1.5 text-xs text-slate-600 mb-5">
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Ketua Program Studi:</span>
                                        <strong class="text-slate-900">{{ optional($prodi->kaprodi)->nama_lengkap ?? '-' }}</strong>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Mahasiswa Aktif:</span>
                                        <strong class="text-slate-900 font-mono">{{ $prodi->mahasiswas_count ?? 0 }} Mahasiswa</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-slate-200/80 flex justify-between items-center text-xs">
                                <a href="{{ route('publik.prodi.show', $prodi->id) }}" class="font-bold text-slate-700 hover:text-slate-950 uppercase text-[11px]">
                                    Profil & Kurikulum
                                </a>
                                <a href="{{ route('pmb.register') }}" class="font-bold text-pub-primary hover:underline uppercase text-[11px]">
                                    Daftar &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= 6. BERITA, PENGUMUMAN & AGENDA AKADEMIK ================= --}}
        <section class="py-14 container mx-auto px-4 lg:px-8 max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-8">
                    <div class="flex justify-between items-end mb-6 pb-2 border-b border-slate-200">
                        <div>
                            <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block">Informasi</span>
                            <h2 class="font-serif text-xl font-bold text-slate-900">Berita & Pengumuman</h2>
                        </div>
                        <a href="{{ route('berita.index') }}" class="text-xs font-bold text-pub-primary hover:underline uppercase">Semua Berita &rarr;</a>
                    </div>

                    @if($beritaUtama)
                        <div class="hover-lift pub-radius bg-white border border-slate-200 overflow-hidden mb-6 grid grid-cols-1 md:grid-cols-12 shadow-2xs">
                            @if($beritaUtama->foto)
                                <div class="md:col-span-5 bg-slate-100 min-h-[200px]">
                                    <img src="{{ asset('storage/' . $beritaUtama->foto) }}" alt="{{ $beritaUtama->judul }}" class="w-full h-full object-cover">
                                </div>
                            @endif
                            <div class="{{ $beritaUtama->foto ? 'md:col-span-7' : 'md:col-span-12' }} p-6 flex flex-col justify-between">
                                <div>
                                    <div class="text-[11px] text-slate-500 font-mono mb-2">
                                        {{ strtoupper($beritaUtama->kategori ?? 'UMUM') }} — {{ $beritaUtama->created_at->translatedFormat('d F Y') }}
                                    </div>
                                    <h3 class="font-serif font-bold text-lg text-slate-900 mb-2">
                                        <a href="{{ route('pengumuman.public.show', $beritaUtama) }}" class="hover:underline">{{ $beritaUtama->judul }}</a>
                                    </h3>
                                    <p class="text-xs text-slate-600 leading-relaxed">{{ Str::limit(strip_tags($beritaUtama->konten), 160) }}</p>
                                </div>
                                <a href="{{ route('pengumuman.public.show', $beritaUtama) }}" class="text-xs font-bold text-pub-primary hover:underline mt-4 inline-block">Baca Selengkapnya &rarr;</a>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($beritaLainnya as $b)
                            <a href="{{ route('pengumuman.public.show', $b) }}" class="hover-lift pub-radius bg-white border border-slate-200 p-4 hover-border-pub-primary flex flex-col justify-between shadow-2xs">
                                <div>
                                    <span class="text-[10px] font-mono text-slate-500 block mb-1">{{ $b->created_at->format('d/m/Y') }}</span>
                                    <h4 class="font-bold text-xs text-slate-900 leading-snug line-clamp-2">{{ $b->judul }}</h4>
                                </div>
                                <span class="text-[11px] font-bold text-pub-primary mt-3 block">Baca &rarr;</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    <div class="pub-radius bg-white border border-slate-200 p-5 shadow-2xs">
                        <h3 class="font-serif font-bold text-sm text-slate-900 mb-4 pb-2 border-b border-slate-200">
                            Agenda Akademik
                        </h3>
                        <div class="divide-y divide-slate-100">
                            @forelse($kegiatanTerdekat as $keg)
                                <div class="py-2.5 flex items-start gap-3">
                                    <div class="bg-pub-primary text-white rounded-lg px-2.5 py-1 text-center shrink-0 min-w-[44px]">
                                        <span class="block text-xs font-mono font-bold">{{ \Carbon\Carbon::parse($keg->tanggal_mulai)->format('d') }}</span>
                                        <span class="block text-[9px] font-mono uppercase opacity-80">{{ \Carbon\Carbon::parse($keg->tanggal_mulai)->translatedFormat('M') }}</span>
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 leading-snug">{{ $keg->judul_kegiatan }}</h5>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ \Carbon\Carbon::parse($keg->tanggal_mulai)->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 py-3">Belum ada agenda akademik terdekat.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pub-radius bg-white border border-slate-200 p-5 shadow-2xs">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-200">
                            <h3 class="font-serif font-bold text-sm text-slate-900">Dokumen Resmi</h3>
                            <a href="{{ route('dokumen.publik') }}" class="text-[11px] font-bold text-pub-primary hover:underline">Semua</a>
                        </div>
                        <ul class="divide-y divide-slate-100 text-xs">
                            @forelse($dokumen as $doc)
                                <li class="py-2.5">
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="flex items-center justify-between hover:underline text-slate-800 font-medium">
                                        <span class="truncate pr-2">{{ $doc->judul_dokumen ?? $doc->judul }}</span>
                                        <span class="font-mono text-[10px] text-slate-500 shrink-0">[PDF]</span>
                                    </a>
                                </li>
                            @empty
                                <li class="text-xs text-slate-500 py-3">Belum ada dokumen publik.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= 7. PENELITIAN & PENGABDIAN DOSEN ================= --}}
        @if(isset($risetTerbaru) && $risetTerbaru->isNotEmpty())
        <section class="py-14 bg-white border-t border-slate-200">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
                <div class="flex justify-between items-end mb-6 pb-2 border-b border-slate-200">
                    <div>
                        <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block">Tridharma</span>
                        <h2 class="font-serif text-xl font-bold text-slate-900">Penelitian & Pengabdian Dosen</h2>
                    </div>
                    <a href="{{ route('publik.riset') }}" class="text-xs font-bold text-pub-primary hover:underline uppercase">Lihat Semua &rarr;</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($risetTerbaru as $karya)
                        <div class="hover-lift pub-radius bg-slate-50/50 border border-slate-200 p-5 flex flex-col justify-between shadow-2xs">
                            <div>
                                <div class="flex justify-between items-center text-[10px] font-mono text-slate-500 mb-2">
                                    <span class="uppercase font-bold text-slate-700">{{ $karya->unsur }}</span>
                                    <span>{{ $karya->tahun }}</span>
                                </div>
                                <h4 class="font-bold text-xs text-slate-900 leading-snug mb-2">{{ $karya->judul }}</h4>
                                <p class="text-[11px] text-slate-500 mb-3">{{ $karya->nama_jurnal_penerbit_lokasi ?? $karya->kategori }}</p>
                            </div>
                            <div class="border-t border-slate-200/70 pt-2.5 flex justify-between items-center text-[11px]">
                                <span class="text-slate-700 font-semibold truncate">{{ $karya->dosen->nama_lengkap ?? '-' }}</span>
                                @if($karya->tautan_eksternal)
                                    <a href="{{ $karya->tautan_eksternal }}" target="_blank" class="text-pub-primary font-bold hover:underline">Buka</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
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