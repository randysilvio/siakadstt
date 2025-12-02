<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Administrasi Kampus - STT GPI Papua</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800&display=swap" rel="stylesheet" />
    
    {{-- Libraries --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Custom Styles --}}
    <style>
        body { font-family: 'Figtree', sans-serif; background-color: #f8fafc; }
        .font-heading { font-family: 'Raleway', sans-serif; }
        
        /* Slideshow Height */
        .hero-height { height: 75vh; }
        @media (max-width: 768px) { .hero-height { height: 50vh; } }
        
        .splide__slide img { width: 100%; height: 100%; object-fit: cover; }
        
        /* Hover Effects */
        .hover-card { transition: all 0.3s ease; }
        .hover-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        /* Date Box Style */
        .date-box { 
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); 
            color: white; 
        }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen">

    {{-- ================= HEADER / NAVBAR ================= --}}
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            {{-- Logo & Brand --}}
            <a href="{{ route('welcome') }}" class="flex items-center space-x-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI Papua" class="h-12 w-12 transition-transform group-hover:scale-110">
                <div class="hidden sm:block">
                    <span class="font-heading font-bold text-xl text-slate-800 block leading-tight">STT GPI PAPUA</span>
                    <span class="text-xs text-teal-600 font-semibold tracking-wide">SISTEM INFORMASI AKADEMIK</span>
                </div>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center space-x-6">
                 <a href="#berita" class="text-sm font-medium text-gray-600 hover:text-teal-600 transition">Berita</a>
                 <a href="#prodi" class="text-sm font-medium text-gray-600 hover:text-teal-600 transition">Program Studi</a>
                 <a href="#dokumen" class="text-sm font-medium text-gray-600 hover:text-teal-600 transition">Dokumen</a>
                 <a href="{{ route('login') }}" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-full transition duration-300 shadow-md transform hover:scale-105">
                    LOGIN SIAKAD
                </a>
            </div>

            {{-- Mobile Menu Button (Hamburger) --}}
            <button class="md:hidden text-gray-600 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </nav>
    </header>

    <main class="flex-grow">
        {{-- ================= HERO SLIDESHOW ================= --}}
        <section class="relative bg-slate-900">
            @if($slides->isNotEmpty())
                <div id="hero-slideshow" class="splide hero-height" aria-label="Galeri Kegiatan Kampus">
                    <div class="splide__track h-full">
                        <ul class="splide__list h-full">
                            @foreach($slides as $slide)
                            <li class="splide__slide relative">
                                <img src="{{ asset('storage/' . $slide->gambar) }}" alt="{{ $slide->judul }}">
                                {{-- Gradient Overlay --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 w-full p-8 md:p-16 pb-24 md:pb-32">
                                    <div class="container mx-auto">
                                        @if($slide->judul)
                                            <h2 class="text-3xl md:text-5xl font-heading font-bold text-white mb-2 leading-tight max-w-4xl drop-shadow-lg opacity-0 translate-y-4 animate-fade-in-up">
                                                {{ $slide->judul }}
                                            </h2>
                                        @endif
                                        <div class="h-1 w-20 bg-teal-500 rounded mt-4"></div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                {{-- Fallback jika tidak ada slide --}}
                <div class="hero-height relative bg-slate-800 flex items-center justify-center">
                    <div class="text-center text-white px-4">
                        <h1 class="text-4xl md:text-6xl font-heading font-bold mb-4">Selamat Datang</h1>
                        <p class="text-xl text-gray-300">Sistem Informasi Akademik STT GPI Papua</p>
                    </div>
                </div>
            @endif
        </section>

        {{-- ================= AKSES CEPAT (QUICK LINKS) ================= --}}
        <section class="relative z-20 -mt-16 container mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 border-b-4 border-teal-500">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                    {{-- Item 1: Berita --}}
                    <a href="#berita" class="group flex flex-col items-center text-center p-2 rounded-xl hover:bg-slate-50 transition">
                        <div class="h-14 w-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300 shadow-sm group-hover:bg-blue-600 group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3h2m-4 3h2m-4 3h2m-4 3h2" /></svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-blue-600">Berita & Info</span>
                    </a>

                    {{-- Item 2: Direktori Dosen --}}
                    <a href="{{ route('dosen.public.index') }}" class="group flex flex-col items-center text-center p-2 rounded-xl hover:bg-slate-50 transition">
                        <div class="h-14 w-14 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300 shadow-sm group-hover:bg-teal-600 group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-1a6 6 0 00-1.781-4.121" /></svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-teal-600">Direktori Dosen</span>
                    </a>

                    {{-- Item 3: Dokumen --}}
                    <a href="#dokumen" class="group flex flex-col items-center text-center p-2 rounded-xl hover:bg-slate-50 transition">
                        <div class="h-14 w-14 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300 shadow-sm group-hover:bg-indigo-600 group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-600">Unduh Dokumen</span>
                    </a>

                    {{-- Item 4: Kontak --}}
                    <a href="#kontak" class="group flex flex-col items-center text-center p-2 rounded-xl hover:bg-slate-50 transition">
                        <div class="h-14 w-14 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300 shadow-sm group-hover:bg-orange-600 group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-orange-600">Kontak Kami</span>
                    </a>

                    {{-- Item 5: Login --}}
                    <a href="{{ route('login') }}" class="group flex flex-col items-center text-center p-2 rounded-xl hover:bg-slate-50 transition">
                        <div class="h-14 w-14 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300 shadow-sm group-hover:bg-slate-800 group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-slate-800">Login SIAKAD</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- ================= STATISTIK (KAMPUS DALAM ANGKA) ================= --}}
        <section class="py-16 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-10">
                    <h2 class="text-3xl md:text-4xl font-heading font-bold text-slate-800">Kampus dalam Angka</h2>
                    <p class="text-gray-500 mt-2">Data statistik terkini komunitas akademik STT GPI Papua.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    {{-- Stat Mahasiswa --}}
                    <div class="bg-gradient-to-br from-teal-50 to-white border border-teal-100 p-8 rounded-2xl shadow-sm text-center hover:shadow-md transition duration-300">
                        <h3 class="text-5xl font-bold text-teal-600 mb-2" data-counter-target="{{ $totalMahasiswa ?? 0 }}">0</h3>
                        <p class="text-lg font-semibold text-slate-700">Mahasiswa Aktif</p>
                    </div>
                    {{-- Stat Dosen --}}
                    <div class="bg-gradient-to-br from-sky-50 to-white border border-sky-100 p-8 rounded-2xl shadow-sm text-center hover:shadow-md transition duration-300">
                        <h3 class="text-5xl font-bold text-sky-600 mb-2" data-counter-target="{{ $totalDosen ?? 0 }}">0</h3>
                        <p class="text-lg font-semibold text-slate-700">Dosen Pengajar</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= BERITA & AGENDA (SPLIT LAYOUT) ================= --}}
        <section id="berita" class="py-16 bg-slate-50">
            <div class="container mx-auto px-6">
                <div class="flex flex-col lg:flex-row gap-12">
                    
                    {{-- KOLOM KIRI: BERITA TERBARU (65%) --}}
                    <div class="lg:w-2/3">
                        <div class="flex justify-between items-end mb-8">
                            <div>
                                <h2 class="text-3xl font-heading font-bold text-slate-800">Berita Terkini</h2>
                                <div class="h-1 w-20 bg-teal-500 rounded mt-2"></div>
                            </div>
                            <a href="{{ route('berita.index') }}" class="text-teal-600 hover:text-teal-800 font-semibold text-sm flex items-center">
                                Lihat Semua <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>

                        {{-- Berita Utama --}}
                        @if($beritaUtama)
                        <div class="group relative rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition duration-300 mb-8 bg-white h-[400px]">
                            <img src="{{ $beritaUtama->foto ? asset('storage/' . $beritaUtama->foto) : 'https://via.placeholder.com/800x600/e2e8f0/475569?text=STT+GPI' }}" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="{{ $beritaUtama->judul }}">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 p-8 w-full">
                                <span class="inline-block px-3 py-1 bg-teal-600 text-white text-xs font-bold rounded-full mb-3 uppercase tracking-wider">
                                    {{ $beritaUtama->kategori }}
                                </span>
                                <a href="{{ route('pengumuman.public.show', $beritaUtama) }}">
                                    <h3 class="text-2xl md:text-3xl font-bold text-white hover:text-teal-200 transition mb-2 leading-tight">
                                        {{ $beritaUtama->judul }}
                                    </h3>
                                </a>
                                <p class="text-gray-300 text-sm mb-4 line-clamp-2">{{ Str::limit(strip_tags($beritaUtama->konten), 150) }}</p>
                                <div class="flex items-center text-gray-400 text-xs">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ $beritaUtama->created_at->isoFormat('D MMMM YYYY') }}
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Berita Lainnya (Grid) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @forelse($beritaLainnya as $item)
                            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden border border-gray-100 flex flex-col h-full">
                                <div class="h-48 overflow-hidden relative">
                                    <img src="{{ $item->foto ? asset('storage/' . $item->foto) : 'https://via.placeholder.com/400x300/e2e8f0/475569?text=News' }}" 
                                         class="w-full h-full object-cover transition-transform duration-500 hover:scale-110" alt="{{ $item->judul }}">
                                    <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-2 py-1 rounded text-xs font-bold text-slate-800">
                                        {{ $item->kategori }}
                                    </div>
                                </div>
                                <div class="p-5 flex-grow flex flex-col">
                                    <div class="text-xs text-teal-600 font-semibold mb-2">{{ $item->created_at->isoFormat('D MMMM YYYY') }}</div>
                                    <a href="{{ route('pengumuman.public.show', $item) }}" class="block mb-2">
                                        <h4 class="font-bold text-lg text-slate-800 hover:text-teal-600 transition line-clamp-2">{{ $item->judul }}</h4>
                                    </a>
                                    <p class="text-gray-500 text-sm line-clamp-2 flex-grow">{{ Str::limit(strip_tags($item->konten), 100) }}</p>
                                </div>
                            </div>
                            @empty
                                <div class="col-span-2 text-center text-gray-500 py-4">Tidak ada berita lain.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- KOLOM KANAN: AGENDA KAMPUS (35%) --}}
                    <div class="lg:w-1/3">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden sticky top-24">
                            <div class="p-6 border-b border-gray-100 bg-slate-50 flex justify-between items-center">
                                <h3 class="font-heading font-bold text-xl text-slate-800">Agenda Kampus</h3>
                                <span class="bg-teal-100 text-teal-700 text-xs font-bold px-2 py-1 rounded">Terdekat</span>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @forelse($kegiatanTerdekat as $kegiatan)
                                <div class="p-5 hover:bg-slate-50 transition group">
                                    <div class="flex items-start space-x-4">
                                        {{-- Kotak Tanggal --}}
                                        <div class="date-box rounded-lg p-2 w-16 text-center flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                                            <span class="block text-2xl font-bold leading-none">{{ $kegiatan->tanggal_mulai->format('d') }}</span>
                                            <span class="block text-xs uppercase font-semibold opacity-90">{{ $kegiatan->tanggal_mulai->isoFormat('MMM') }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800 leading-snug mb-1 group-hover:text-teal-600 transition">{{ $kegiatan->judul_kegiatan }}</h4>
                                            <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ Str::limit($kegiatan->deskripsi, 60) }}</p>
                                            <span class="inline-flex items-center text-xs text-gray-400">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $kegiatan->tanggal_mulai->format('H:i') }} WIT
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="p-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p>Belum ada agenda terdekat.</p>
                                </div>
                                @endforelse
                            </div>
                            <div class="p-4 bg-gray-50 text-center border-t border-gray-100">
                                <a href="#" class="text-sm font-semibold text-teal-600 hover:text-teal-800">Lihat Kalender Lengkap &rarr;</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ================= PROGRAM STUDI (TANPA FOTO) ================= --}}
        <section id="prodi" class="py-16 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-heading font-bold text-slate-800">Program Studi</h2>
                    <p class="text-gray-500 mt-2">Pilihan program studi unggulan untuk masa depan pelayanan Anda.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    @forelse ($programStudi as $prodi)
                    <div class="bg-white border border-gray-200 rounded-2xl p-8 hover:shadow-xl hover:border-teal-200 transition-all duration-300 group relative overflow-hidden">
                        {{-- Hiasan Background --}}
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-teal-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                        
                        <div class="relative z-10">
                            <h3 class="text-2xl font-bold text-slate-800 mb-3 group-hover:text-teal-600 transition">{{ $prodi->nama_prodi }}</h3>
                            <div class="h-1 w-12 bg-gray-200 group-hover:bg-teal-500 transition mb-4 rounded"></div>
                            <p class="text-gray-600 leading-relaxed mb-6">
                                {{ $prodi->deskripsi_singkat ?? 'Program studi ini dirancang untuk memperlengkapi mahasiswa dengan pengetahuan teologi yang mendalam dan keterampilan praktis.' }}
                            </p>
                            <a href="#" class="inline-flex items-center text-teal-600 font-bold hover:text-teal-800 transition">
                                Pelajari Selengkapnya 
                                <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-2 text-center text-gray-500">Informasi program studi segera tersedia.</div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ================= DOKUMEN PUBLIK ================= --}}
        <section id="dokumen" class="py-16 bg-slate-50">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-10">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-3xl font-heading font-bold text-slate-800">Unduh Dokumen</h2>
                        <p class="text-gray-500 mt-1">Berkas akademik dan formulir resmi.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($dokumen as $doc)
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition flex items-start space-x-4 group">
                        <div class="bg-indigo-50 text-indigo-600 p-3 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="flex-grow">
                            <h4 class="font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition">{{ $doc->judul_dokumen }}</h4>
                            <p class="text-xs text-gray-400 mb-3">{{ $doc->created_at->format('d M Y') }}</p>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" download class="text-sm font-semibold text-indigo-500 hover:text-indigo-700 inline-flex items-center">
                                Download <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-8 text-gray-500">Belum ada dokumen publik.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </main>

    {{-- ================= FOOTER ================= --}}
    <footer id="kontak" class="bg-slate-900 text-white pt-16 pb-8 border-t-4 border-teal-600">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                {{-- Kolom 1: Identitas --}}
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-10 brightness-200 grayscale">
                        <span class="font-bold text-xl tracking-wider">STT GPI PAPUA</span>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">
                        Institusi pendidikan teologi yang berdedikasi melahirkan pemimpin-pemimpin berintegritas, berilmu, dan siap melayani.
                    </p>
                    <div class="flex space-x-4">
                        {{-- Social Icons Placeholders --}}
                        <a href="#" class="text-slate-400 hover:text-white transition"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-slate-400 hover:text-white transition"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-slate-400 hover:text-white transition"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>

                {{-- Kolom 2: Kontak --}}
                <div>
                    <h3 class="font-bold text-lg mb-6 border-l-4 border-teal-500 pl-3">Hubungi Kami</h3>
                    <ul class="space-y-4 text-sm text-slate-300">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-3 text-teal-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Jl. Jenderal Sudirman, Fakfak, Papua Barat
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-teal-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            info@sttgpipapua.ac.id
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-teal-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            (0956) 123-456
                        </li>
                    </ul>
                </div>

                {{-- Kolom 3: Tautan --}}
                <div>
                    <h3 class="font-bold text-lg mb-6 border-l-4 border-teal-500 pl-3">Akademik</h3>
                    <ul class="space-y-3 text-sm text-slate-300">
                        <li><a href="#prodi" class="hover:text-teal-400 transition flex items-center"><span class="w-1 h-1 bg-teal-500 rounded-full mr-2"></span> Program Studi</a></li>
                        <li><a href="{{ route('dosen.public.index') }}" class="hover:text-teal-400 transition flex items-center"><span class="w-1 h-1 bg-teal-500 rounded-full mr-2"></span> Direktori Dosen</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition flex items-center"><span class="w-1 h-1 bg-teal-500 rounded-full mr-2"></span> Kalender Akademik</a></li>
                        <li><a href="#dokumen" class="hover:text-teal-400 transition flex items-center"><span class="w-1 h-1 bg-teal-500 rounded-full mr-2"></span> Unduh Dokumen</a></li>
                    </ul>
                </div>

                {{-- Kolom 4: Mahasiswa --}}
                <div>
                    <h3 class="font-bold text-lg mb-6 border-l-4 border-teal-500 pl-3">Mahasiswa</h3>
                    <ul class="space-y-3 text-sm text-slate-300">
                        <li><a href="{{ route('login') }}" class="hover:text-teal-400 transition flex items-center"><span class="w-1 h-1 bg-teal-500 rounded-full mr-2"></span> Login SIAKAD</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition flex items-center"><span class="w-1 h-1 bg-teal-500 rounded-full mr-2"></span> Pendaftaran Baru</a></li>
                        <li><a href="{{ route('perpustakaan.index') }}" class="hover:text-teal-400 transition flex items-center"><span class="w-1 h-1 bg-teal-500 rounded-full mr-2"></span> Perpustakaan</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 text-center text-slate-500 text-sm">
                <p>&copy; {{ date('Y') }} STT GPI Papua. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Init Slideshow
        if(document.querySelector('#hero-slideshow')){
          new Splide('#hero-slideshow',{
            type: 'fade', rewind: true, perPage: 1, autoplay: true, interval: 6000, pauseOnHover: false, arrows: false, pagination: true,
          }).mount();
        }

        // Init Counter Animation
        const counters = document.querySelectorAll('[data-counter-target]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-counter-target'), 10);
                    if (target === 0) { counter.innerText = '0'; return; }
                    const duration = 2000; 
                    const increment = target / (duration / 16);
                    let current = 0;
                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            counter.innerText = Math.ceil(current).toLocaleString('id-ID');
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.innerText = target.toLocaleString('id-ID');
                        }
                    };
                    requestAnimationFrame(updateCounter);
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(counter => observer.observe(counter));
      });
    </script>
</body>
</html>