<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dosen->nama_lengkap }} - Profil Dosen</title>

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
        {{-- ================= HERO BACKGROUND ================= --}}
        <div class="h-64 w-full bg-gradient-to-r from-teal-700 to-teal-900 relative">
            <div class="absolute inset-0 bg-black/20"></div>
            {{-- Pattern Overlay (Optional) --}}
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>
        </div>

        {{-- ================= KONTEN PROFIL ================= --}}
        <div class="container mx-auto px-4 pb-12 relative z-10 -mt-24">
            
            {{-- Tombol Kembali --}}
            <div class="mb-6">
                <a href="{{ route('dosen.public.index') }}" class="inline-flex items-center text-white/90 hover:text-white font-semibold text-sm bg-black/30 hover:bg-black/50 px-4 py-2 rounded-full transition backdrop-blur-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Direktori
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-5xl mx-auto flex flex-col md:flex-row">
                
                {{-- KOLOM KIRI: FOTO & KONTAK --}}
                <div class="w-full md:w-1/3 bg-slate-50 p-8 md:p-10 text-center border-b md:border-b-0 md:border-r border-gray-100">
                    {{-- Avatar --}}
                    <div class="relative w-48 h-48 mx-auto mb-6">
                        <img src="{{ $dosen->foto_profil }}" 
                             class="w-full h-full rounded-full object-cover border-4 border-white shadow-lg" 
                             alt="{{ $dosen->nama_lengkap }}">
                        <div class="absolute bottom-2 right-2 bg-green-500 w-5 h-5 rounded-full border-2 border-white" title="Status Aktif"></div>
                    </div>

                    <h1 class="text-xl font-bold text-slate-800 leading-tight mb-2">{{ $dosen->nama_lengkap }}</h1>
                    <p class="text-teal-600 font-semibold mb-6">{{ $dosen->jabatan_akademik ?? 'Dosen Pengajar' }}</p>

                    {{-- Tombol Kontak --}}
                    @if($dosen->email_institusi)
                        <a href="mailto:{{ $dosen->email_institusi }}" class="flex items-center justify-center gap-2 w-full bg-slate-800 hover:bg-slate-700 text-white py-2.5 rounded-lg transition mb-4 shadow-sm text-sm font-bold">
                            <i class="fa-solid fa-envelope"></i> Hubungi Email
                        </a>
                    @endif

                    <div class="flex justify-center gap-3">
                        @if($dosen->link_google_scholar)
                            <a href="{{ $dosen->link_google_scholar }}" target="_blank" class="flex-1 bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:border-blue-200 py-2 rounded-lg text-xs font-bold transition shadow-sm flex flex-col items-center justify-center h-16">
                                <i class="fa-solid fa-graduation-cap text-lg mb-1"></i> Scholar
                            </a>
                        @endif
                        @if($dosen->link_sinta)
                            <a href="{{ $dosen->link_sinta }}" target="_blank" class="flex-1 bg-white border border-gray-200 text-gray-600 hover:text-orange-500 hover:border-orange-200 py-2 rounded-lg text-xs font-bold transition shadow-sm flex flex-col items-center justify-center h-16">
                                <i class="fa-solid fa-book-journal-whills text-lg mb-1"></i> SINTA
                            </a>
                        @endif
                    </div>
                </div>

                {{-- KOLOM KANAN: DETAIL INFORMASI --}}
                <div class="w-full md:w-2/3 p-8 md:p-12">
                    
                    {{-- Tentang Dosen --}}
                    <div class="mb-10">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Tentang Dosen</h3>
                        <p class="text-gray-600 leading-relaxed text-justify">
                            {{ $dosen->deskripsi_diri ?? 'Belum ada deskripsi profil untuk dosen ini. Informasi akan segera diperbarui.' }}
                        </p>
                    </div>

                    {{-- Bidang Keahlian --}}
                    @if($dosen->bidang_keahlian)
                        <div class="mb-10">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Bidang Keahlian</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $dosen->bidang_keahlian) as $keahlian)
                                    <span class="inline-block bg-teal-50 text-teal-700 text-sm px-4 py-1.5 rounded-full border border-teal-100 font-medium">
                                        {{ trim($keahlian) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Mata Kuliah --}}
                    @if($dosen->mataKuliahs->isNotEmpty())
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Mata Kuliah Diampu</h3>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($dosen->mataKuliahs->unique('nama_mk') as $matkul)
                                    <li class="flex items-center text-gray-700 bg-gray-50 px-3 py-2 rounded border border-gray-100 text-sm">
                                        <i class="fa-solid fa-book-open text-teal-500 mr-2"></i>
                                        {{ $matkul->nama_mk }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Footer Kecil di dalam container --}}
            <div class="text-center mt-8 text-gray-500 text-sm">
                &copy; {{ date('Y') }} STT GPI Papua. Data diperbarui secara otomatis dari SIAKAD.
            </div>
        </div>
    </main>

    {{-- Footer global tidak diperlukan di sini karena sudah ada di layout utama, 
         namun jika ingin konsistensi penuh, bisa ditambahkan footer yang sama dengan index.blade.php --}}

</body>
</html>