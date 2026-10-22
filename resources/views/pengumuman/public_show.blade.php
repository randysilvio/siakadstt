<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- META TAGS OPEN GRAPH --}}
    <meta property="og:title" content="{{ $pengumuman->judul }} - STT GPI Papua" />
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($pengumuman->konten), 150) }}" />
    <meta property="og:image" content="{{ $pengumuman->foto ? asset('storage/' . $pengumuman->foto) : asset('images/logo.png') }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />

    <title>{{ $pengumuman->judul }} - STT GPI Papua</title>

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
        .prose p { margin-bottom: 1.4em; line-height: 1.8; color: #334155; }
        .prose h1, .prose h2, .prose h3 { color: #0f172a; font-weight: 700; margin-top: 1.5em; margin-bottom: 0.75em; }
        .prose ul { list-style-type: disc; padding-left: 1.5em; margin-bottom: 1.4em; color: #334155; }
        .prose ol { list-style-type: decimal; padding-left: 1.5em; margin-bottom: 1.4em; color: #334155; }
        .prose a { color: #0d9488; text-decoration: underline; font-weight: 600; }
        .prose blockquote { border-left: 4px solid #0d9488; padding: 0.75em 1.25em; background-color: #f0fdfa; border-radius: 0 0.75rem 0.75rem 0; font-style: italic; color: #134e4a; margin-bottom: 1.5em; }
        .prose img { border-radius: 0.75rem; border: 1px solid #e2e8f0; margin: 1.5em 0; }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow py-12 px-4 md:px-6">
        <div class="max-w-4xl mx-auto">
            
            {{-- Navigasi Kembali --}}
            <div class="mb-6">
                <a href="{{ route('berita.index') }}" class="inline-flex items-center text-xs font-bold uppercase tracking-wider text-slate-700 bg-white border border-gray-200 py-2.5 px-5 rounded-full hover:bg-brand-600 hover:text-white hover:border-brand-600 transition shadow-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Indeks Berita
                </a>
            </div>

            {{-- Bingkai Utama Artikel --}}
            <article class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                
                {{-- Bagian Judul & Meta --}}
                <div class="p-8 md:p-12 pb-8 border-b border-gray-100">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                        <span class="bg-brand-50 text-brand-700 border border-brand-100 text-xs font-bold px-3.5 py-1 rounded-full uppercase tracking-wider">
                            {{ $pengumuman->kategori ?? 'PENGUMUMAN' }}
                        </span>
                        <span class="text-slate-400 text-xs font-semibold flex items-center">
                            <i class="fa-regular fa-calendar mr-2 text-brand-500"></i>
                            Dipublikasikan: {{ $pengumuman->created_at->translatedFormat('d F Y') }}
                        </span>
                    </div>

                    <h1 class="text-2xl md:text-4xl font-heading font-extrabold text-slate-900 leading-tight tracking-tight mb-0">
                        {{ $pengumuman->judul }}
                    </h1>
                </div>

                {{-- Sampul Gambar --}}
                @if($pengumuman->foto)
                    <div class="w-full max-h-[480px] overflow-hidden bg-slate-100 border-b border-gray-100">
                        <img src="{{ asset('storage/' . $pengumuman->foto) }}" 
                             class="w-full h-full object-cover object-center" 
                             alt="{{ $pengumuman->judul }}">
                    </div>
                @endif

                {{-- Isi Teks Utama --}}
                <div class="p-8 md:p-12 pt-8">
                    <div class="prose max-w-none text-base">
                        {!! $pengumuman->konten !!}
                    </div>
                </div>

                {{-- Panel Berbagi --}}
                <div class="bg-slate-50 px-8 py-5 border-t border-gray-100 flex flex-wrap justify-between items-center gap-4">
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Bagikan Informasi Ini:</span>
                    <div class="flex gap-2.5">
                        <button type="button" class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center hover:bg-blue-600 transition shadow-sm" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href))" title="Bagikan ke Facebook">
                            <i class="fa-brands fa-facebook-f text-xs"></i>
                        </button>
                        <button type="button" class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center hover:bg-emerald-600 transition shadow-sm" onclick="window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(document.title + ' - ' + window.location.href))" title="Bagikan ke WhatsApp">
                            <i class="fa-brands fa-whatsapp text-xs"></i>
                        </button>
                        <button type="button" class="w-9 h-9 rounded-full bg-white text-slate-700 border border-gray-200 flex items-center justify-center hover:bg-brand-600 hover:text-white hover:border-brand-600 transition shadow-sm" onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan berhasil disalin!');" title="Salin Tautan">
                            <i class="fa-solid fa-link text-xs"></i>
                        </button>
                    </div>
                </div>

            </article>

        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>