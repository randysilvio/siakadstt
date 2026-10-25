@php
    $pubCfg      = \App\Models\Pengaturan::allCached();
    $pubTheme    = \App\Models\Pengaturan::themeColors();
    $pubLogo     = \App\Models\Pengaturan::logoUrl();
    $pubProdis   = \App\Models\ProgramStudi::orderBy('nama_prodi')->get();
    $singkatanPt = $pubCfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
    $namaPt      = $pubCfg['nama_kampus'] ?? 'Sekolah Tinggi Teologi GPI Papua';
@endphp

<style>
    :root {
        --pub-primary: {{ $pubTheme['primary'] }};
        --pub-accent: {{ $pubTheme['accent'] }};
    }
    .bg-pub-primary { background-color: var(--pub-primary) !important; }
    .text-pub-primary { color: var(--pub-primary) !important; }
    .border-pub-primary { border-color: var(--pub-primary) !important; }
    .bg-pub-accent { background-color: var(--pub-accent) !important; }
    .text-pub-accent { color: var(--pub-accent) !important; }
    .hover-pub-accent:hover { color: var(--pub-accent) !important; }
</style>

{{-- ================= LAPIS 1: TOP UTILITY BAR (GAYA UKSW) ================= --}}
<div class="bg-pub-primary text-white text-[11px] border-b border-white/10">
    <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-2 flex flex-wrap justify-between items-center gap-2">
        <div class="flex items-center space-x-4">
            <span class="hidden sm:inline-flex items-center font-semibold text-white/90">
                <i class="fa-solid fa-award text-amber-400 mr-1.5"></i> Terakreditasi {{ $pubCfg['akreditasi_institusi'] ?? 'BAN-PT' }}
            </span>
            @if(!empty($pubCfg['email_kampus']))
                <a href="mailto:{{ $pubCfg['email_kampus'] }}" class="hover:text-amber-300 transition hidden md:inline-flex items-center text-white/80">
                    <i class="fa-regular fa-envelope mr-1.5"></i> {{ $pubCfg['email_kampus'] }}
                </a>
            @endif
            @if(!empty($pubCfg['telepon_kampus']))
                <span class="hidden lg:inline-flex items-center text-white/80">
                    <i class="fa-solid fa-phone mr-1.5"></i> {{ $pubCfg['telepon_kampus'] }}
                </span>
            @endif
        </div>

        <div class="flex items-center space-x-3 md:space-x-5 ml-auto font-semibold tracking-wide uppercase text-[10px]">
            <a href="{{ route('publik.perpustakaan') }}" class="text-white/85 hover:text-amber-300 transition flex items-center">
                <i class="fa-solid fa-book-open mr-1"></i> E-Library (OPAC)
            </a>
            <a href="{{ route('publik.riset') }}" class="text-white/85 hover:text-amber-300 transition hidden sm:flex items-center">
                <i class="fa-solid fa-microscope mr-1"></i> Riset & PkM
            </a>
            <a href="{{ route('dokumen.publik') }}" class="text-white/85 hover:text-amber-300 transition flex items-center">
                <i class="fa-solid fa-file-arrow-down mr-1"></i> Unduhan
            </a>
            <a href="{{ route('pmb.register') }}" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold px-2.5 py-0.5 rounded transition">
                Admisi PMB
            </a>
        </div>
    </div>
</div>

{{-- ================= LAPIS 2: MAIN STICKY MEGA-MENU NAVBAR ================= --}}
<header class="sticky top-0 z-50 transition-all duration-300 glass-effect border-b border-gray-200/80 shadow-sm">
    <nav class="container mx-auto px-4 lg:px-8 py-3.5 flex justify-between items-center max-w-7xl">
        {{-- Identitas Brand Kampus --}}
        <a href="{{ url('/') }}" class="flex items-center space-x-3.5 group">
            <img src="{{ $pubLogo }}" alt="Logo {{ $singkatanPt }}" class="h-12 w-12 object-contain transition-transform duration-300 group-hover:scale-105">
            <div>
                <span class="font-heading font-black text-lg md:text-xl text-slate-900 tracking-tight block leading-none uppercase">{{ $singkatanPt }}</span>
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block mt-1 max-w-[230px] truncate">{{ $namaPt }}</span>
            </div>
        </a>

        {{-- Menu Desktop dengan Mega-Dropdown --}}
        <div class="hidden lg:flex items-center space-x-6">
            <a href="{{ url('/') }}" class="text-xs uppercase tracking-wider {{ request()->routeIs('welcome') ? 'font-extrabold text-pub-accent border-b-2 border-teal-600 pb-1' : 'font-bold text-slate-700 hover:text-teal-600' }} transition">
                Beranda
            </a>

            {{-- Dropdown 1: Tentang Kampus & Struktur Organisasi --}}
            <div class="relative group">
                <button type="button" class="text-xs uppercase tracking-wider {{ request()->routeIs('publik.profil') || request()->routeIs('dosen.public.*') ? 'font-extrabold text-pub-accent' : 'font-bold text-slate-700 group-hover:text-teal-600' }} flex items-center gap-1 py-2 transition">
                    Tentang Kampus <i class="fa-solid fa-chevron-down text-[9px] transition-transform group-hover:rotate-180"></i>
                </button>
                <div class="absolute left-0 top-full w-[520px] bg-white rounded-xl shadow-2xl border border-gray-100 p-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-teal-600 block mb-2 border-b pb-1">Profil Institusi</span>
                        <ul class="space-y-1.5 text-xs">
                            <li><a href="{{ route('publik.profil') }}#sambutan" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600"><i class="fa-solid fa-quote-left w-5 text-slate-400"></i> Sambutan {{ $pubCfg['sebutan_pimpinan'] ?? 'Ketua' }}</a></li>
                            <li><a href="{{ route('publik.profil') }}#sejarah" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600"><i class="fa-solid fa-landmark w-5 text-slate-400"></i> Sejarah & Jati Diri</a></li>
                            <li><a href="{{ route('publik.profil') }}#visi-misi" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600"><i class="fa-solid fa-bullseye w-5 text-slate-400"></i> Visi, Misi & Nilai Dasar</a></li>
                        </ul>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-teal-600 block mb-2 border-b pb-1">Tata Kelola & SDM</span>
                        <ul class="space-y-1.5 text-xs">
                            <li><a href="{{ route('publik.profil') }}#struktur-yayasan" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600"><i class="fa-solid fa-sitemap w-5 text-slate-400"></i> Struktur Sinode / Yayasan</a></li>
                            <li><a href="{{ route('publik.profil') }}#struktur-kampus" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600"><i class="fa-solid fa-users-gear w-5 text-slate-400"></i> Senat & Pimpinan Kampus</a></li>
                            <li><a href="{{ route('dosen.public.index') }}" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600"><i class="fa-solid fa-chalkboard-user w-5 text-slate-400"></i> Direktori Dosen & Peneliti</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Dropdown 2: Akademik & Program Studi --}}
            <div class="relative group">
                <button type="button" class="text-xs uppercase tracking-wider font-bold text-slate-700 group-hover:text-teal-600 flex items-center gap-1 py-2 transition">
                    Akademik <i class="fa-solid fa-chevron-down text-[9px] transition-transform group-hover:rotate-180"></i>
                </button>
                <div class="absolute left-0 top-full w-72 bg-white rounded-xl shadow-2xl border border-gray-100 p-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-teal-600 block mb-2 border-b pb-1">Program Studi Terakreditasi</span>
                    <ul class="space-y-1.5 text-xs mb-3">
                        @foreach($pubProdis as $pr)
                            <li>
                                <a href="{{ url('/#prodi-finder') }}" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600">
                                    <i class="fa-solid fa-graduation-cap mr-1.5 text-teal-600"></i> {{ $pr->nama_prodi }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="border-t pt-2 space-y-1 text-xs">
                        <a href="{{ route('publik.perpustakaan') }}" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600">
                            <i class="fa-solid fa-book mr-1.5 text-slate-400"></i> Katalog Perpustakaan (OPAC)
                        </a>
                        <a href="{{ route('dokumen.publik') }}" class="block px-2.5 py-1.5 rounded-lg hover:bg-slate-50 font-semibold text-slate-700 hover:text-teal-600">
                            <i class="fa-solid fa-folder-open mr-1.5 text-slate-400"></i> Dokumen & Kalender Akademik
                        </a>
                    </div>
                </div>
            </div>

            <a href="{{ route('publik.riset') }}" class="text-xs uppercase tracking-wider {{ request()->routeIs('publik.riset') ? 'font-extrabold text-pub-accent border-b-2 border-teal-600 pb-1' : 'font-bold text-slate-700 hover:text-teal-600' }} transition">
                Riset & PkM
            </a>

            <a href="{{ route('publik.perpustakaan') }}" class="text-xs uppercase tracking-wider {{ request()->routeIs('publik.perpustakaan') ? 'font-extrabold text-pub-accent border-b-2 border-teal-600 pb-1' : 'font-bold text-slate-700 hover:text-teal-600' }} transition">
                Perpustakaan
            </a>

            <a href="{{ route('berita.index') }}" class="text-xs uppercase tracking-wider {{ request()->routeIs('berita.index') || request()->routeIs('pengumuman.public.show') ? 'font-extrabold text-pub-accent border-b-2 border-teal-600 pb-1' : 'font-bold text-slate-700 hover:text-teal-600' }} transition">
                Warta
            </a>
        </div>

        {{-- Tombol CTA Kanan --}}
        <div class="hidden lg:flex items-center space-x-2.5">
            @auth
                <a href="{{ route('dashboard') }}" class="bg-pub-primary hover:opacity-90 text-white font-bold px-5 py-2.5 rounded-full text-xs uppercase tracking-wider transition shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard SIAKAD
                </a>
            @else
                <a href="{{ route('pmb.register') }}" class="border-2 border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white font-bold px-4 py-2 rounded-full text-xs uppercase tracking-wider transition">
                    Daftar PMB
                </a>
                <a href="{{ route('login') }}" class="bg-pub-primary hover:opacity-90 text-white font-bold px-5 py-2.5 rounded-full text-xs uppercase tracking-wider transition shadow-md flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-[10px]"></i> Portal SIAKAD
                </a>
            @endauth
        </div>

        {{-- Tombol Burger Mobile --}}
        <div class="lg:hidden">
            <button id="mobile-menu-button" type="button" class="text-slate-800 hover:text-teal-600 focus:outline-none p-2" aria-label="Buka Menu">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>
    </nav>

    {{-- Menu Dropdown Mobile --}}
    <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-gray-100 px-6 py-5 space-y-2.5 shadow-xl">
        <a href="{{ url('/') }}" class="block py-2 text-xs font-bold uppercase text-slate-800 border-b border-gray-100">Beranda</a>
        <a href="{{ route('publik.profil') }}" class="block py-2 text-xs font-bold uppercase text-slate-800 border-b border-gray-100">Profil, Visi-Misi & Struktur Organisasi</a>
        <a href="{{ route('dosen.public.index') }}" class="block py-2 text-xs font-bold uppercase text-slate-800 border-b border-gray-100">Direktori Dosen & Peneliti</a>
        <a href="{{ route('publik.riset') }}" class="block py-2 text-xs font-bold uppercase text-slate-800 border-b border-gray-100">Riset, Publikasi & Pengabdian</a>
        <a href="{{ route('publik.perpustakaan') }}" class="block py-2 text-xs font-bold uppercase text-slate-800 border-b border-gray-100">Katalog Perpustakaan (OPAC)</a>
        <a href="{{ route('berita.index') }}" class="block py-2 text-xs font-bold uppercase text-slate-800 border-b border-gray-100">Berita & Pengumuman</a>
        <a href="{{ route('dokumen.publik') }}" class="block py-2 text-xs font-bold uppercase text-slate-800 border-b border-gray-100">Dokumen Publik</a>
        <div class="pt-2 flex flex-col gap-2">
            <a href="{{ route('pmb.register') }}" class="block text-center bg-amber-500 text-slate-950 font-extrabold py-2.5 rounded-xl text-xs uppercase">Pendaftaran Mahasiswa Baru (PMB)</a>
            @auth
                <a href="{{ route('dashboard') }}" class="block text-center bg-pub-primary text-white font-bold py-2.5 rounded-xl text-xs uppercase">Dashboard SIAKAD</a>
            @else
                <a href="{{ route('login') }}" class="block text-center bg-pub-primary text-white font-bold py-2.5 rounded-xl text-xs uppercase">Login Portal SIAKAD</a>
            @endauth
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', function () {
                menu.classList.toggle('hidden');
            });
        }
    });
</script>