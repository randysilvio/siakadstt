<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- === META TAGS OPEN GRAPH === --}}
    <meta property="og:title" content="{{ $pengumuman->judul }} - STT GPI Papua" />
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($pengumuman->konten), 150) }}" />
    <meta property="og:image" content="{{ $pengumuman->foto ? asset('storage/' . $pengumuman->foto) : asset('images/logo.png') }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />
    {{-- ============================== --}}

    <title>{{ $pengumuman->judul }} - STT GPI Papua</title>

    {{-- Fonts Standard Enterprise --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700|jetbrains-mono:400,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 500: '#212529', 600: '#000000', 700: '#1a1d20' },
                        accent: { 600: '#0d6efd' }
                    },
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    {{-- Custom Styles Kaku & Formal --}}
    <style>
        .prose p { margin-bottom: 1.5em; line-height: 1.75; color: #212529; }
        .prose h1, .prose h2, .prose h3 { color: #000000; font-weight: 700; text-transform: uppercase; margin-top: 1.5em; margin-bottom: 0.8em; }
        .prose ul { list-style-type: square; padding-left: 1.5em; margin-bottom: 1.5em; }
        .prose ol { list-style-type: decimal; padding-left: 1.5em; margin-bottom: 1.5em; font-family: 'JetBrains Mono', monospace; font-weight: bold; }
        .prose a { color: #0d6efd; text-decoration: none; font-weight: 600; border-bottom: 1px solid #0d6efd; }
        .prose blockquote { border-left: 4px solid #000000; padding-left: 1.2em; font-style: normal; font-weight: 600; background-color: #f8fafc; padding-top: 0.5em; padding-bottom: 0.5em; }
        .prose img { border-radius: 0px; border: 1px solid #dee2e6; margin-top: 2em; margin-bottom: 2em; }
    </style>
</head>
<body class="antialiased text-brand-500 flex flex-col min-h-screen bg-white">

    {{-- ================= HEADER FORMAL FLAT ================= --}}
    <header class="bg-white border-b-2 border-black sticky top-0 z-50">
        <nav class="container mx-auto px-4 md:px-8 h-20 flex justify-between items-center">
            
            {{-- Branding Identitas Presisi --}}
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI" class="h-11 w-auto rounded-none"> 
                <div class="flex flex-col justify-center">
                    <span class="font-bold text-lg text-black leading-tight uppercase tracking-tight">
                        STT GPI PAPUA FAKFAK
                    </span>
                    <span class="text-[10px] font-mono font-bold text-brand-500 uppercase tracking-widest mt-0.5 border-t border-gray-200 pt-0.5">
                        Sistem Informasi Akademik
                    </span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ url('/#berita') }}" class="text-xs font-bold uppercase tracking-wider text-black hover:underline underline-offset-4 transition">Berita</a>
                <a href="{{ url('/#prodi') }}" class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black transition">Program Studi</a>
                <a href="{{ url('/#dokumen') }}" class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black transition">Dokumen</a>
                
                <a href="{{ route('login') }}" class="bg-black hover:bg-brand-700 text-white text-xs font-mono font-bold py-3 px-6 rounded-none uppercase tracking-widest border border-black">
                    LOGIN PORTAL
                </a>
            </div>
        </nav>
    </header>

    <main class="flex-grow py-10 px-4 md:px-6 bg-slate-50">
        <div class="max-w-4xl mx-auto">
            
            {{-- Navigasi Kembali Siku Tajam --}}
            <div class="mb-6">
                <a href="{{ url('/#berita') }}" class="inline-flex items-center text-xs font-mono font-bold uppercase tracking-wider text-black bg-white border border-black py-2 px-4 rounded-none hover:bg-black hover:text-white transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> KEMBALI KE INDEKS
                </a>
            </div>

            {{-- Bingkai Utama Publikasi --}}
            <article class="bg-white rounded-none border-t-4 border-black border-x border-b border-gray-200 shadow-none">
                
                {{-- Bagian Judul & Meta --}}
                <div class="p-8 md:p-12 pb-8 border-b border-gray-200">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                        <span class="bg-black text-white font-mono text-[11px] font-bold px-3 py-1 rounded-none uppercase tracking-widest">
                            {{ $pengumuman->kategori ?? 'UMUM' }}
                        </span>
                        <span class="text-gray-600 font-mono text-xs font-bold flex items-center">
                            <i class="fa-solid fa-terminal mr-2 text-black"></i>
                            TGL: {{ $pengumuman->created_at->translatedFormat('d.m.Y') }}
                        </span>
                    </div>

                    <h1 class="text-2xl md:text-4xl font-bold text-black leading-tight uppercase tracking-tight mb-0">
                        {{ $pengumuman->judul }}
                    </h1>
                </div>

                {{-- Sampul Gambar Flat --}}
                @if($pengumuman->foto)
                    <div class="w-full h-auto md:h-[450px] overflow-hidden bg-slate-100 border-b border-gray-200">
                        <img src="{{ asset('storage/' . $pengumuman->foto) }}" 
                             class="w-full h-full object-cover object-center rounded-none" 
                             alt="Foto Berita">
                    </div>
                @endif

                {{-- Isi Teks Utama --}}
                <div class="p-8 md:p-12 pt-8">
                    <div class="prose max-w-none text-base text-brand-500">
                        {!! $pengumuman->konten !!}
                    </div>
                </div>

                {{-- Panel Berbagi --}}
                <div class="bg-gray-50 px-8 py-5 border-t border-gray-200 flex flex-wrap justify-between items-center gap-4">
                    <span class="text-xs font-mono font-bold text-black uppercase tracking-wider">BAGIKAN TAUTAN:</span>
                    <div class="flex gap-2">
                        <button class="w-9 h-9 rounded-none bg-black text-white flex items-center justify-center hover:bg-accent-600 transition" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href))"><i class="fa-brands fa-facebook-f text-xs"></i></button>
                        <button class="w-9 h-9 rounded-none bg-black text-white flex items-center justify-center hover:bg-green-600 transition" onclick="window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(window.location.href))"><i class="fa-brands fa-whatsapp text-xs"></i></button>
                        <button class="w-9 h-9 rounded-none bg-white text-black border border-black flex items-center justify-center hover:bg-black hover:text-white transition" onclick="navigator.clipboard.writeText(window.location.href); alert('TAUTAN DISALIN KE KLIPBOR!')" title="Salin Tautan"><i class="fa-solid fa-link text-xs"></i></button>
                    </div>
                </div>

            </article>

        </div>
    </main>

    {{-- ================= FOOTER KAKU & FORMAL ================= --}}
    <footer class="bg-black text-white pt-14 pb-8 border-t-2 border-white">
        <div class="container mx-auto px-6 max-w-5xl">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10 border-b border-gray-800 pb-10">
                
                {{-- Identitas Kiri --}}
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-white p-1 rounded-none">
                            <img src="{{ asset('images/logo.png') }}" class="h-10 w-auto rounded-none" alt="Logo">
                        </div>
                        <div>
                            <h4 class="font-bold text-base tracking-wider uppercase text-white">STT GPI PAPUA FAKFAK</h4>
                            <p class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-widest">Sistem Informasi Akademik</p>
                        </div>
                    </div>
                    <p class="text-gray-400 text-xs leading-relaxed max-w-md uppercase font-mono">
                        Pendidikan tinggi teologi berstandar enterprise guna menghasilkan lulusan berkualitas dan berintegritas di Tanah Papua.
                    </p>
                </div>

                {{-- Alamat Kanan --}}
                <div class="col-span-1">
                    <h5 class="font-mono font-bold text-xs mb-4 text-white uppercase tracking-widest border-b border-gray-700 pb-2">NARAHUBUNG</h5>
                    <ul class="space-y-3 text-xs text-gray-400 font-mono">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-location-dot mt-0.5 text-white"></i>
                            <span>Jl. Jenderal Sudirman, Fakfak, Papua Barat</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-white"></i>
                            <span>info@sttgpipapua.ac.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="text-center text-[10px] font-mono font-bold text-gray-500 uppercase tracking-widest">
                &copy; {{ date('Y') }} STT GPI PAPUA. HAK CIPTA DILINDUNGI UNDANG-UNDANG.
            </div>
        </div>
    </footer>

</body>
</html>