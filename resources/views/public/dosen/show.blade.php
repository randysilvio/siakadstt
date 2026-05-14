<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dosen->nama_lengkap }} - Profil Tenaga Pendidik</title>

    {{-- Favicon Integration --}}
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    {{-- Google Fonts Premium Integration --}}
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

    {{-- ================= NAVBAR HEADER ================= --}}
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
        {{-- Latar Spanduk Identitas --}}
        <div class="h-48 w-full bg-gradient-to-r from-slate-900 via-brand-900 to-slate-950 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/30"></div>
        </div>

        {{-- ================= RUANG KONTEN RINCIAN ================= --}}
        <div class="container mx-auto px-4 pb-16 relative z-10 -mt-20 max-w-5xl">
            
            {{-- Bilah Pintas Navigasi --}}
            <div class="mb-5">
                <a href="{{ route('dosen.public.index') }}" class="inline-flex items-center text-xs font-bold text-white bg-slate-900/40 hover:bg-slate-900/70 px-4 py-2 rounded-full transition backdrop-blur-md">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Penelusuran
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 flex flex-col md:flex-row">
                
                {{-- Panel Identitas Kiri --}}
                <div class="w-full md:w-1/3 bg-slate-50 p-8 text-center border-b md:border-b-0 md:border-r border-gray-100 flex flex-col justify-between">
                    <div>
                        {{-- Bingkai Foto Profil Utama --}}
                        <div class="relative w-40 h-40 mx-auto mb-5">
                            <img src="{{ $dosen->foto_profil }}" class="w-full h-full rounded-full object-cover border-4 border-white shadow-md" alt="{{ $dosen->nama_lengkap }}">
                            <span class="absolute bottom-2 right-2 bg-emerald-500 w-4 h-4 rounded-full border-2 border-white" title="Pengajar Aktif"></span>
                        </div>

                        <h1 class="text-lg font-bold text-slate-900 leading-tight mb-1">{{ $dosen->nama_lengkap }}</h1>
                        <span class="text-xs text-brand-600 font-bold block mb-6">{{ $dosen->jabatan_akademik ?? 'Dosen Pengajar' }}</span>
                        
                        {{-- Identitas Registrasi NIDN --}}
                        <div class="bg-white border border-gray-200 rounded-xl p-3 mb-6 text-center shadow-2xs">
                            <span class="text-[10px] text-slate-400 font-medium block uppercase tracking-wider">NIDN Terdaftar</span>
                            <span class="text-xs font-mono font-bold text-slate-700">{{ $dosen->nidn ?? 'BELUM DIVERIFIKASI' }}</span>
                        </div>

                        {{-- Tautan Kontak Surel --}}
                        @if($dosen->email_institusi)
                            <a href="mailto:{{ $dosen->email_institusi }}" class="inline-flex items-center justify-center gap-2 w-full bg-slate-900 hover:bg-brand-600 text-white py-2 rounded-xl transition text-xs font-bold shadow-sm mb-4">
                                <i class="fa-regular fa-envelope text-sm"></i> Kirim Surel Resmi
                            </a>
                        @endif
                    </div>

                    {{-- Bilah Integrasi Repositori Ilmiah --}}
                    <div class="grid grid-cols-2 gap-2 pt-2">
                        @if($dosen->link_google_scholar)
                            <a href="{{ $dosen->link_google_scholar }}" target="_blank" rel="noopener noreferrer" class="bg-white border border-gray-200 text-slate-600 hover:text-brand-600 hover:border-brand-200 py-2.5 rounded-xl text-[11px] font-bold transition flex flex-col items-center justify-center">
                                <i class="fa-solid fa-graduation-cap text-base mb-1 text-slate-400"></i> Scholar
                            </a>
                        @endif
                        @if($dosen->link_sinta)
                            <a href="{{ $dosen->link_sinta }}" target="_blank" rel="noopener noreferrer" class="bg-white border border-gray-200 text-slate-600 hover:text-orange-500 hover:border-orange-200 py-2.5 rounded-xl text-[11px] font-bold transition flex flex-col items-center justify-center">
                                <i class="fa-solid fa-book-bookmark text-base mb-1 text-slate-400"></i> SINTA
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Kanvas Informasi Kanan --}}
                <div class="w-full md:w-2/3 p-8 md:p-10 flex flex-col justify-between">
                    <div>
                        {{-- Deskripsi Profil Singkat --}}
                        <div class="mb-8">
                            <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest block mb-2">Profil Pengajar</span>
                            <p class="text-slate-600 text-xs md:text-sm leading-relaxed text-justify font-light">
                                {{ $dosen->deskripsi_diri ?? 'Informasi portofolio akademik dan deskripsi spesialisasi riset tenaga pendidik ini sedang dalam proses penyusunan.' }}
                            </p>
                        </div>

                        {{-- Klaster Spesialisasi Keahlian --}}
                        @if($dosen->bidang_keahlian)
                            <div class="mb-8 border-t border-gray-50 pt-6">
                                <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest block mb-3">Spesialisasi Riset & Kajian</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(explode(',', $dosen->bidang_keahlian) as $keahlian)
                                        <span class="bg-brand-50 text-brand-700 text-xs px-3.5 py-1 rounded-lg border border-brand-100 font-semibold">
                                            {{ trim($keahlian) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Beban Alokasi Mata Kuliah --}}
                        @if($dosen->mataKuliahs->isNotEmpty())
                            <div class="border-t border-gray-50 pt-6">
                                <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest block mb-3">Mata Kuliah Diampu</span>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach($dosen->mataKuliahs->unique('nama_mk') as $matkul)
                                        <li class="flex items-center text-slate-700 bg-slate-50 px-3 py-2 rounded-xl border border-gray-100 text-xs font-medium">
                                            <i class="fa-solid fa-check text-brand-500 mr-2 text-[10px]"></i>
                                            <span class="truncate">{{ $matkul->nama_mk }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Informasi Hak Cipta Internal --}}
                    <div class="pt-6 mt-8 border-t border-gray-100 text-center md:text-left text-[11px] text-slate-400 font-light">
                        Sinkronisasi Basis Data SIAKAD STT GPI Papua secara berkala.
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Kaki Halaman Sederhana --}}
    <footer class="bg-slate-950 text-white py-6 border-t-2 border-brand-600 mt-auto">
        <div class="container mx-auto px-6 max-w-5xl text-center flex flex-col sm:flex-row justify-between items-center gap-2">
            <span class="font-heading font-bold text-xs tracking-wider text-slate-300">STT GPI PAPUA</span>
            <span class="text-[10px] text-slate-500 font-light">&copy; {{ date('Y') }} Hak Cipta Dilindungi.</span>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
</body>
</html>