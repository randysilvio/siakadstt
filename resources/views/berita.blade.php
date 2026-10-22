<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita & Pengumuman - STT GPI Papua</title>

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        .glass-effect { background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

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
                            <div class="h-52 overflow-hidden relative bg-slate-100">
                                <img src="{{ $berita->foto ? asset('storage/' . $berita->foto) : asset('images/latar-welcome.jpg') }}" class="w-full h-full object-cover transition duration-700 ease-out group-hover:scale-105" alt="{{ $berita->judul }}">
                                <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur text-white text-[10px] font-bold px-2.5 py-1 rounded tracking-wide uppercase">
                                    {{ $berita->kategori ?? 'UMUM' }}
                                </span>
                            </div>

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
                                        {{ Str::limit(strip_tags($berita->konten), 120) }}
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-gray-50 mt-auto">
                                    <a href="{{ route('pengumuman.public.show', $berita) }}" class="inline-flex items-center text-xs font-bold text-brand-600 hover:text-brand-800 transition">
                                        Baca Selengkapnya <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            
                <div class="mt-14 flex justify-center">
                    {{ $semuaBerita->links('pagination::tailwind') }}
                </div>
            @endif
        </section>
    </main>

    @include('partials.public_footer')

</body>
</html>