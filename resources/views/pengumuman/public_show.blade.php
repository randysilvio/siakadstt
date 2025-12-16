<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pengumuman->judul }} - STT GPI Papua</title>

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
    
    {{-- Custom Styles untuk Konten WYSIWYG --}}
    <style>
        .prose p { margin-bottom: 1.5em; line-height: 1.75; color: #374151; }
        .prose h1, .prose h2, .prose h3 { color: #1f2937; font-weight: 700; margin-top: 2em; margin-bottom: 1em; }
        .prose ul { list-style-type: disc; padding-left: 1.5em; margin-bottom: 1.5em; }
        .prose ol { list-style-type: decimal; padding-left: 1.5em; margin-bottom: 1.5em; }
        .prose a { color: #0d9488; text-decoration: underline; }
        .prose blockquote { border-left: 4px solid #e5e7eb; padding-left: 1em; font-style: italic; color: #4b5563; }
        .prose img { border-radius: 0.5rem; margin-top: 2em; margin-bottom: 2em; }
    </style>
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

    <main class="flex-grow py-12 px-4 md:px-6">
        <div class="max-w-4xl mx-auto">
            
            {{-- Tombol Kembali --}}
            <div class="mb-8">
                <a href="{{ url()->previous() }}" class="inline-flex items-center text-gray-500 hover:text-teal-600 font-semibold transition group">
                    <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center mr-3 group-hover:border-teal-500 group-hover:bg-teal-50 transition shadow-sm">
                        <i class="fa-solid fa-arrow-left text-sm group-hover:-translate-x-0.5 transition-transform"></i>
                    </div>
                    Kembali ke Daftar Berita
                </a>
            </div>

            {{-- Artikel Card --}}
            <article class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                
                {{-- Header Artikel --}}
                <div class="p-8 md:p-12 pb-6 border-b border-gray-50">
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <span class="bg-teal-100 text-teal-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                            {{ $pengumuman->kategori ?? 'Umum' }}
                        </span>
                        <span class="text-gray-400 text-sm font-medium flex items-center">
                            <i class="fa-regular fa-clock mr-2"></i>
                            {{ $pengumuman->created_at->translatedFormat('d F Y') }}
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-5xl font-heading font-bold text-slate-800 leading-tight mb-0">
                        {{ $pengumuman->judul }}
                    </h1>
                </div>

                {{-- Featured Image --}}
                @if($pengumuman->foto)
                    <div class="w-full h-auto md:h-[500px] overflow-hidden bg-slate-100">
                        <img src="{{ asset('storage/' . $pengumuman->foto) }}" 
                             class="w-full h-full object-cover object-center" 
                             alt="{{ $pengumuman->judul }}">
                    </div>
                @endif

                {{-- Konten Artikel --}}
                <div class="p-8 md:p-12">
                    <div class="prose max-w-none text-lg text-slate-600">
                        {!! $pengumuman->konten !!}
                    </div>
                </div>

                {{-- Share / Footer Artikel (Opsional) --}}
                <div class="bg-slate-50 px-8 py-6 border-t border-gray-100 flex justify-between items-center">
                    <span class="text-sm text-gray-500 font-medium">Bagikan berita ini:</span>
                    <div class="flex gap-3">
                        <button class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition"><i class="fa-brands fa-facebook-f text-sm"></i></button>
                        <button class="w-8 h-8 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 transition"><i class="fa-brands fa-twitter text-sm"></i></button>
                        <button class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center hover:bg-green-600 transition"><i class="fa-brands fa-whatsapp text-sm"></i></button>
                        <button class="w-8 h-8 rounded-full bg-gray-600 text-white flex items-center justify-center hover:bg-gray-700 transition" onclick="navigator.clipboard.writeText(window.location.href); alert('Link disalin!')"><i class="fa-solid fa-link text-sm"></i></button>
                    </div>
                </div>

            </article>

        </div>
    </main>

    {{-- ================= FOOTER STANDAR ================= --}}
    <footer class="bg-slate-900 text-white pt-16 pb-6 border-t-4 border-teal-600 mt-12">
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