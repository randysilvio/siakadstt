<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Direktori Dosen - STT GPI Papua</title>

    {{-- Favicon Integration --}}
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

    {{-- ================= NAVBAR KONSISTEN ================= --}}
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
                <a href="{{ url('/#berita') }}" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Berita</a>
                <a href="{{ url('/#prodi') }}" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Program Studi</a>
                <a href="{{ url('/#dokumen') }}" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Dokumen</a>
                
                {{-- TOMBOL LOGIN KONTRAS TINGGI MUTLAK --}}
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 font-bold text-white rounded-full shadow-md bg-slate-900 hover:bg-brand-600 border border-slate-800 transition duration-300">
                    <span class="text-xs tracking-widest uppercase font-heading text-white">Portal Login</span>
                </a>
            </div>

            <button id="mobile-btn" class="md:hidden text-slate-700 p-2"><i class="fa-solid fa-bars text-lg"></i></button>
        </nav>
        
        <div id="mobile-menu" class="hidden md:hidden absolute top-full left-0 w-full bg-white border-t border-gray-100 shadow-xl p-5 flex flex-col space-y-3">
            <a href="{{ url('/#berita') }}" class="text-sm font-semibold text-slate-700">Berita</a>
            <a href="{{ url('/#prodi') }}" class="text-sm font-semibold text-slate-700">Program Studi</a>
            <a href="{{ url('/#dokumen') }}" class="text-sm font-semibold text-slate-700">Dokumen</a>
            <a href="{{ route('login') }}" class="bg-slate-900 text-white text-center py-3 rounded-xl tracking-widest text-xs font-bold uppercase block shadow-md hover:bg-brand-600">Portal SIAKAD</a>
        </div>
    </header>

    <main class="flex-grow">
        {{-- ================= HERO DIREKTORI ================= --}}
        <section class="bg-slate-950 py-16 relative text-center border-b border-slate-800 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-brand-900/10 via-transparent to-transparent"></div>
            <div class="container mx-auto px-6 relative z-10 max-w-2xl">
                <span class="text-xs font-bold text-brand-400 uppercase tracking-widest block mb-2">Tenaga Pendidik Ahli</span>
                <h1 class="text-3xl md:text-5xl font-heading font-extrabold text-white tracking-tight mb-3">Direktori Dosen</h1>
                <p class="text-slate-400 text-xs md:text-sm font-light">Mengenal lebih dekat profil akademis, keahlian riset, dan publikasi para dosen tetap STT GPI Papua.</p>
            </div>
        </section>

        {{-- ================= AREA PENCARIAN & KARTU ================= --}}
        <section class="py-12 container mx-auto px-6 max-w-6xl -mt-8 relative z-20">
            
            {{-- Kotak Pencarian Mengambang --}}
            <div class="max-w-2xl mx-auto mb-12">
                <div class="bg-white p-1.5 rounded-full shadow-lg border border-gray-100 flex items-center">
                    <form action="{{ route('dosen.public.index') }}" method="GET" class="w-full flex items-center">
                        <span class="pl-5 text-gray-400"><i class="fa-solid fa-search text-sm"></i></span>
                        <input type="text" name="search" class="w-full px-3 py-2.5 text-xs md:text-sm rounded-full outline-none text-slate-700 placeholder-gray-400 font-medium" placeholder="Pencarian nama dosen atau spesialisasi..." value="{{ request('search') }}">
                        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition shadow-sm">
                            Cari
                        </button>
                    </form>
                </div>
            </div>

            {{-- Hasil Daftar Dosen --}}
            @if($dosens->isEmpty())
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 max-w-md mx-auto">
                    <i class="fa-solid fa-user-slash text-4xl text-slate-300 block mb-2"></i>
                    <h4 class="text-sm font-bold text-slate-700">Dosen Tidak Ditemukan</h4>
                    <p class="text-xs text-slate-400 mt-1">Pencarian untuk kriteria yang dimasukkan tidak memiliki padanan master data.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($dosens as $dosen)
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 p-6 flex flex-col items-center text-center group relative overflow-hidden">
                            {{-- Foto Avatar Denyut --}}
                            <div class="relative w-28 h-28 mx-auto mb-4">
                                <div class="absolute inset-0 bg-brand-100 rounded-full animate-pulse opacity-75 group-hover:hidden"></div>
                                <img src="{{ $dosen->foto_profil }}" class="w-full h-full rounded-full object-cover border-4 border-white shadow-sm relative z-10 group-hover:scale-105 transition duration-500" alt="{{ $dosen->nama_lengkap }}">
                            </div>

                            {{-- Identitas Dosen --}}
                            <h3 class="text-base font-bold text-slate-900 mb-1 group-hover:text-brand-600 transition leading-snug truncate w-full" title="{{ $dosen->nama_lengkap }}">
                                {{ $dosen->nama_lengkap }}
                            </h3>
                            <span class="text-xs text-brand-600 font-semibold block mb-3">{{ $dosen->jabatan_akademik ?? 'Dosen Pengajar' }}</span>
                            
                            <div class="w-8 h-0.5 bg-gray-100 rounded-full mb-4 group-hover:bg-brand-300 transition"></div>

                            {{-- Tautan Aksi --}}
                            <div class="mt-auto w-full pt-2">
                                <a href="{{ route('dosen.public.show', $dosen->nidn) }}" class="block w-full py-2 bg-slate-50 hover:bg-brand-600 text-slate-700 hover:text-white rounded-xl text-xs font-bold tracking-wide transition duration-200">
                                    Lihat Profil
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Penomoran Halaman --}}
                <div class="mt-12 flex justify-center">
                    {{ $dosens->appends(request()->query())->links() }} 
                </div>
            @endif
        </section>
    </main>

    {{-- ================= FOOTER KONSISTEN ================= --}}
    <footer class="bg-slate-950 text-white pt-10 pb-5 border-t-2 border-brand-600 mt-auto">
        <div class="container mx-auto px-6 max-w-6xl text-center flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center space-x-2.5">
                <img src="{{ asset('images/logo.png') }}" class="h-8 w-8 brightness-0 invert" alt="Logo">
                <span class="font-heading font-bold text-sm tracking-wider block">STT GPI PAPUA</span>
            </div>
            <p class="text-[11px] text-slate-500 font-light">&copy; {{ date('Y') }} STT GPI Papua. Semua Hak Dilindungi.</p>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
</body>
</html>