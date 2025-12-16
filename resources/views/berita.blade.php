<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita & Pengumuman - STT GPI Papua</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|raleway:700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal: { 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e' },
                    },
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                        heading: ['Raleway', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    {{-- ================= HEADER STANDAR ================= --}}
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
        <nav class="container mx-auto px-4 md:px-8 h-20 flex justify-between items-center">
            
            {{-- Branding --}}
            <a href="/" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI" class="h-10 w-auto"> 
                <div class="flex flex-col justify-center">
                    <span class="font-heading font-bold text-xl text-slate-800 leading-none tracking-tight group-hover:text-teal-600 transition">
                        STT GPI PAPUA
                    </span>
                    <span class="text-[11px] font-bold text-teal-600 uppercase tracking-wider mt-1">
                        Sistem Informasi Akademik
                    </span>
                </div>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ url('/#berita') }}" class="text-sm font-semibold text-teal-600 transition">Berita</a>
                <a href="{{ url('/#prodi') }}" class="text-sm font-semibold text-gray-500 hover:text-teal-600 transition">Program Studi</a>
                <a href="{{ url('/#dokumen') }}" class="text-sm font-semibold text-gray-500 hover:text-teal-600 transition">Dokumen</a>
                
                <a href="{{ route('login') }}" class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold py-2.5 px-6 rounded-full shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                    LOGIN SIAKAD
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <button class="md:hidden text-gray-600 focus:outline-none">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </nav>
    </header>

    <main class="flex-grow">
        {{-- ================= HERO SECTION ================= --}}
        <section class="bg-slate-900 py-12 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1504711434969-e33886168f5c?q=80&w=2070&auto=format&fit=crop')] bg-cover bg-center opacity-10"></div>
            <div class="container mx-auto px-6 relative z-10 text-center">
                <h1 class="text-3xl md:text-5xl font-heading font-bold text-white mb-2">Berita & Pengumuman</h1>
                <p class="text-gray-400 max-w-xl mx-auto">
                    Informasi terkini seputar kegiatan akademik dan kemahasiswaan.
                </p>
            </div>
        </section>

        {{-- ================= KONTEN BERITA ================= --}}
        <section class="py-12 container mx-auto px-6">
            
            @if($semuaBerita->isEmpty())
                <div class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="inline-block p-4 rounded-full bg-slate-100 mb-4">
                        <i class="fa-regular fa-folder-open text-4xl text-slate-400"></i>
                    </div>
                    <p class="text-gray-500 text-lg font-medium">Saat ini belum ada berita atau pengumuman yang tersedia.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    
                    @foreach($semuaBerita as $berita)
                        <article class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col h-full group">
                            {{-- Gambar Thumbnail (Jika ada, atau placeholder) --}}
                            <div class="h-48 overflow-hidden relative bg-slate-100">
                                <img src="{{ $berita->foto ? asset('storage/' . $berita->foto) : 'https://via.placeholder.com/600x400/f1f5f9/94a3b8?text=STT+GPI+Papua' }}" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                                     alt="{{ $berita->judul }}">
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded text-xs font-bold text-slate-800 shadow-sm">
                                    {{ $berita->kategori ?? 'Umum' }}
                                </div>
                            </div>

                            <div class="p-6 flex-grow flex flex-col">
                                {{-- Tanggal --}}
                                <div class="text-xs text-teal-600 font-bold mb-2 flex items-center gap-2">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ \Carbon\Carbon::parse($berita->created_at)->translatedFormat('d F Y') }}
                                </div>

                                {{-- Judul --}}
                                <h2 class="text-xl font-bold text-slate-800 mb-3 line-clamp-2 group-hover:text-teal-600 transition">
                                    <a href="{{ route('pengumuman.public.show', $berita) }}">
                                        {{ $berita->judul }}
                                    </a>
                                </h2>

                                {{-- Snippet Konten --}}
                                <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow leading-relaxed">
                                    {!! Str::limit(strip_tags($berita->konten), 120) !!}
                                </p>

                                {{-- Tombol Baca --}}
                                <div class="mt-auto pt-4 border-t border-gray-50">
                                    <a href="{{ route('pengumuman.public.show', $berita) }}" class="inline-flex items-center text-sm font-bold text-teal-600 hover:text-teal-800 transition">
                                        Baca Selengkapnya <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach

                </div>
            
                {{-- Pagination --}}
                <div class="mt-12 flex justify-center">
                    {{ $semuaBerita->links() }}
                </div>
            @endif

        </section>
    </main>

    {{-- ================= FOOTER (SAMA SEPERTI WELCOME) ================= --}}
    <footer class="bg-slate-900 text-white pt-16 pb-6 border-t-4 border-teal-600">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                {{-- Identitas --}}
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ asset('images/logo.png') }}" class="h-12 w-auto brightness-0 invert" alt="Logo Footer">
                        <div>
                            <h4 class="font-bold text-lg tracking-wide">STT GPI PAPUA</h4>
                            <p class="text-xs text-slate-400 uppercase tracking-widest">Sistem Informasi Akademik</p>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-md mb-6">
                        Mewujudkan pendidikan tinggi teologi yang berkualitas dan relevan untuk melayani gereja dan masyarakat di Tanah Papua.
                    </p>
                </div>

                {{-- Kontak --}}
                <div class="col-span-1 md:col-span-2">
                    <h5 class="font-bold text-lg mb-6 border-l-4 border-teal-500 pl-3">Hubungi Kami</h5>
                    <ul class="space-y-4 text-sm text-slate-400">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 text-teal-500"></i>
                            <span>Jl. Jenderal Sudirman, Kabupaten Fakfak, Papua Barat</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-teal-500"></i>
                            <span>info@sttgpipapua.ac.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} STT GPI Papua. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>