<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita & Pengumuman - STT GPI Papua</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    {{-- Fonts Premium Integration --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#f0fdfa', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e' },
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
        .glass-effect { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    {{-- ================= NAVBAR HEADER (KONSISTEN) ================= --}}
    <header class="sticky top-0 z-50 transition-all duration-300 glass-effect border-b border-gray-100 shadow-sm">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center max-w-6xl">
            <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI" class="h-11 w-11 transition-transform duration-500 group-hover:rotate-6"> 
                <div>
                    <span class="font-heading font-extrabold text-xl text-slate-900 block leading-none tracking-tight">STT GPI PAPUA</span>
                    <span class="text-[10px] text-brand-600 font-bold uppercase tracking-widest block mt-1">Sistem Informasi Akademik</span>
                </div>
            </a>

            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ url('/#berita') }}" class="text-sm font-bold text-brand-600">Berita</a>
                <a href="{{ url('/#prodi') }}" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Program Studi</a>
                <a href="{{ url('/#dokumen') }}" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Dokumen</a>
                
                {{-- TOMBOL LOGIN KONTRAS TINGGI MUTLAK --}}
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 font-bold text-white rounded-full shadow-md bg-slate-900 hover:bg-brand-600 border border-slate-800 transition duration-300">
                    <span class="text-xs tracking-widest uppercase font-heading text-white">Portal Login</span>
                </a>
            </div>

            <button id="mobile-btn" class="md:hidden text-slate-700 p-2 focus:outline-none"><i class="fa-solid fa-bars text-lg"></i></button>
        </nav>
        
        {{-- Dropdown Mobile --}}
        <div id="mobile-menu" class="hidden md:hidden absolute top-full left-0 w-full bg-white border-t border-gray-100 shadow-xl p-5 flex flex-col space-y-3">
            <a href="{{ url('/#berita') }}" class="text-sm font-bold text-brand-600">Berita Terkini</a>
            <a href="{{ url('/#prodi') }}" class="text-sm font-semibold text-slate-700">Program Studi</a>
            <a href="{{ url('/#dokumen') }}" class="text-sm font-semibold text-slate-700">Dokumen Publik</a>
            <a href="{{ route('login') }}" class="bg-slate-900 text-white text-center py-3 rounded-xl tracking-widest text-xs font-bold uppercase block shadow-md hover:bg-brand-600">Masuk Portal</a>
        </div>
    </header>

    <main class="flex-grow">
        {{-- ================= SPANDUK HALAMAN ================= --}}
        <section class="bg-slate-950 py-16 relative overflow-hidden border-b border-slate-800 text-center">
            <div class="absolute inset-0 bg-gradient-to-r from-brand-900/20 via-transparent to-brand-900/20"></div>
            <div class="container mx-auto px-6 relative z-10 max-w-3xl">
                <span class="text-xs font-bold text-brand-400 uppercase tracking-widest block mb-2">Pusat Publikasi</span>
                <h1 class="text-3xl md:text-5xl font-heading font-extrabold text-white tracking-tight mb-4">Berita & Pengumuman</h1>
                <p class="text-slate-400 text-sm md:text-base font-light">Ikuti terus pembaruan informasi terkini seputar aktivitas akademik, pengumuman jadwal, dan warta civitas kampus.</p>
            </div>
        </section>

        {{-- ================= KISI ARSIP BERITA ================= --}}
        <section class="py-16 container mx-auto px-6 max-w-6xl">
            @if($semuaBerita->isEmpty())
                <div class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100 max-w-xl mx-auto">
                    <i class="fa-regular fa-folder-open text-5xl text-slate-300 block mb-3"></i>
                    <h4 class="text-base font-bold text-slate-700">Arsip Publikasi Kosong</h4>
                    <p class="text-xs text-slate-500 mt-1">Sistem belum mendeteksi adanya data berita atau pengumuman yang dibagikan secara publik.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($semuaBerita as $berita)
                        <article class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 overflow-hidden flex flex-col h-full group">
                            {{-- Area Sampul --}}
                            <div class="h-52 overflow-hidden relative bg-slate-100">
                                <img src="{{ $berita->foto ? asset('storage/' . $berita->foto) : 'https://via.placeholder.com/600x400/f8fafc/64748b?text=STT+GPI+Papua' }}" class="w-full h-full object-cover transition duration-700 ease-out group-hover:scale-105" alt="{{ $berita->judul }}">
                                <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur text-white text-[10px] font-bold px-2.5 py-1 rounded tracking-wide">
                                    {{ $berita->kategori ?? 'UMUM' }}
                                </span>
                            </div>

                            {{-- Uraian Berita --}}
                            <div class="p-6 flex-grow flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] text-brand-600 font-bold block mb-2 flex items-center">
                                        <i class="fa-regular fa-calendar mr-1.5 text-xs"></i> {{ \Carbon\Carbon::parse($berita->created_at)->translatedFormat('d F Y') }}
                                    </span>
                                    <h2 class="text-lg font-bold text-slate-900 mb-2 leading-snug line-clamp-2 group-hover:text-brand-600 transition">
                                        <a href="{{ route('pengumuman.public.show', $berita) }}" class="block">
                                            {{ $berita->judul }}
                                        </a>
                                    </h2>
                                    <p class="text-slate-500 text-xs leading-relaxed line-clamp-3 mb-4 font-light text-justify">
                                        {!! Str::limit(strip_tags($berita->konten), 120) !!}
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-gray-50 mt-auto">
                                    <a href="{{ route('pengumuman.public.show', $berita) }}" class="inline-flex items-center text-xs font-bold text-brand-600 hover:text-brand-800 transition">
                                        Baca Artikel <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            
                {{-- Penomoran Halaman --}}
                <div class="mt-14 flex justify-center">
                    {{ $semuaBerita->links() }}
                </div>
            @endif
        </section>
    </main>

    {{-- ================= FOOTER STANDAR ================= --}}
    <footer class="bg-slate-950 text-white pt-12 pb-6 border-t-2 border-brand-600">
        <div class="container mx-auto px-6 max-w-6xl text-center md:text-left">
            <div class="flex flex-col md:flex-row justify-between items-center border-b border-slate-900 pb-8 mb-6 gap-4">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo.png') }}" class="h-10 w-10 brightness-0 invert" alt="Logo">
                    <span class="font-heading font-extrabold tracking-wider text-base block">STT GPI PAPUA</span>
                </div>
                <p class="text-slate-400 text-xs font-light max-w-sm">Pendidikan Tinggi Teologi Unggul dan Terpercaya di Tanah Papua.</p>
            </div>
            <div class="text-center text-[11px] text-slate-600 font-light">
                &copy; {{ date('Y') }} STT GPI Papua. Seluruh Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
</body>
</html>