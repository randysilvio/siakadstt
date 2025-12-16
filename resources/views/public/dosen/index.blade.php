<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Direktori Dosen - STT GPI Papua</title>

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

    {{-- ================= HEADER (SAMA SEPERTI WELCOME) ================= --}}
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
                {{-- Gunakan url('/') agar link anchor berfungsi dari halaman lain --}}
                <a href="{{ url('/#berita') }}" class="text-sm font-semibold text-gray-500 hover:text-teal-600 transition">Berita</a>
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
        {{-- ================= HERO SECTION SEDERHANA ================= --}}
        <section class="bg-slate-900 py-16 md:py-24 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=2070&auto=format&fit=crop')] bg-cover bg-center opacity-20"></div>
            <div class="container mx-auto px-6 relative z-10 text-center">
                <h1 class="text-4xl md:text-6xl font-heading font-bold text-white mb-4">Direktori Dosen</h1>
                <p class="text-lg text-gray-300 max-w-2xl mx-auto">
                    Mengenal lebih dekat para tenaga pengajar profesional yang berdedikasi di STT GPI Papua.
                </p>
            </div>
        </section>

        {{-- ================= KONTEN UTAMA ================= --}}
        <section class="py-12 -mt-10 relative z-20 container mx-auto px-6">
            
            {{-- SEARCH BAR --}}
            <div class="max-w-3xl mx-auto mb-16">
                <div class="bg-white p-2 rounded-full shadow-xl border border-gray-100 flex items-center">
                    <form action="{{ route('dosen.public.index') }}" method="GET" class="w-full flex">
                        <div class="pl-6 flex items-center justify-center text-gray-400">
                            <i class="fa-solid fa-search text-lg"></i>
                        </div>
                        <input type="text" name="search" class="w-full px-4 py-3 rounded-full outline-none text-gray-700 placeholder-gray-400" placeholder="Cari nama dosen atau bidang keahlian..." value="{{ request('search') }}">
                        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-8 py-3 rounded-full font-bold transition duration-300 shadow-md">
                            Cari
                        </button>
                    </form>
                </div>
            </div>

            {{-- GRID DOSEN --}}
            @if($dosens->isEmpty())
                <div class="text-center py-16">
                    <div class="inline-block p-6 rounded-full bg-slate-100 mb-4">
                        <i class="fa-solid fa-user-slash text-4xl text-slate-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-700">Dosen Tidak Ditemukan</h3>
                    <p class="text-gray-500 mt-2">Coba kata kunci pencarian yang lain.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach ($dosens as $dosen)
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden group text-center p-6 flex flex-col h-full">
                            
                            {{-- Foto Profil --}}
                            <div class="relative w-32 h-32 mx-auto mb-4">
                                <div class="absolute inset-0 bg-teal-100 rounded-full animate-pulse group-hover:hidden"></div>
                                <img src="{{ $dosen->foto_profil }}" 
                                     class="w-full h-full rounded-full object-cover border-4 border-white shadow-md relative z-10 group-hover:scale-110 transition duration-500" 
                                     alt="{{ $dosen->nama_lengkap }}">
                            </div>

                            {{-- Info Dosen --}}
                            <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-teal-600 transition line-clamp-1" title="{{ $dosen->nama_lengkap }}">
                                {{ $dosen->nama_lengkap }}
                            </h3>
                            <p class="text-sm text-teal-600 font-semibold mb-3">{{ $dosen->jabatan_akademik ?? 'Dosen Pengajar' }}</p>
                            
                            {{-- Divider Kecil --}}
                            <div class="w-10 h-1 bg-gray-100 mx-auto mb-4 rounded-full group-hover:bg-teal-200 transition"></div>

                            {{-- Tombol Detail --}}
                            <div class="mt-auto">
                                <a href="{{ route('dosen.public.show', $dosen->nidn) }}" class="inline-flex items-center justify-center w-full px-4 py-2 rounded-lg border border-teal-600 text-teal-600 text-sm font-bold hover:bg-teal-600 hover:text-white transition duration-300">
                                    Lihat Profil
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination (Tailwind Style) --}}
                <div class="mt-12 flex justify-center">
                    {{ $dosens->appends(request()->query())->links() }} 
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