{{-- ================= NAVBAR PUBLIK TERPUSAT (STICKY GLASSMORPHISM) ================= --}}
<header class="sticky top-0 z-50 transition-all duration-300 glass-effect border-b border-gray-100 shadow-sm">
    <nav class="container mx-auto px-6 py-3 flex justify-between items-center max-w-6xl">
        {{-- Identitas Brand Kampus --}}
        <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
            <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI Papua" class="h-11 w-11 object-contain transition-transform duration-500 group-hover:rotate-6">
            <div>
                <span class="font-heading font-extrabold text-xl text-slate-900 tracking-tight block leading-none">STT GPI PAPUA</span>
                <span class="text-[10px] text-brand-600 font-bold uppercase tracking-widest block mt-1">Sistem Informasi Akademik</span>
            </div>
        </a>

        {{-- Tautan Menu Desktop --}}
        <div class="hidden md:flex items-center space-x-7">
            <a href="{{ url('/') }}" class="text-sm {{ request()->routeIs('welcome') ? 'font-bold text-brand-600 border-b-2 border-brand-600 pb-0.5' : 'font-semibold text-slate-600 hover:text-brand-600' }} transition">
                Beranda
            </a>
            <a href="{{ route('berita.index') }}" class="text-sm {{ request()->routeIs('berita.index') || request()->routeIs('pengumuman.public.show') ? 'font-bold text-brand-600 border-b-2 border-brand-600 pb-0.5' : 'font-semibold text-slate-600 hover:text-brand-600' }} transition">
                Berita
            </a>
            <a href="{{ route('dosen.public.index') }}" class="text-sm {{ request()->routeIs('dosen.public.*') ? 'font-bold text-brand-600 border-b-2 border-brand-600 pb-0.5' : 'font-semibold text-slate-600 hover:text-brand-600' }} transition">
                Direktori Dosen
            </a>
            <a href="{{ route('dokumen.publik') }}" class="text-sm {{ request()->routeIs('dokumen.publik') ? 'font-bold text-brand-600 border-b-2 border-brand-600 pb-0.5' : 'font-semibold text-slate-600 hover:text-brand-600' }} transition">
                Dokumen
            </a>
            <a href="{{ route('pmb.register') }}" class="text-sm {{ request()->routeIs('pmb.register') ? 'font-bold text-brand-600 border-b-2 border-brand-600 pb-0.5' : 'font-semibold text-slate-600 hover:text-brand-600' }} transition">
                PMB
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-2.5 font-bold text-white rounded-full shadow-md bg-brand-600 hover:bg-slate-900 border border-brand-600 transition duration-300">
                    <i class="fa-solid fa-gauge-high mr-2 text-xs"></i>
                    <span class="text-xs tracking-widest uppercase font-heading text-white">Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 font-bold text-white rounded-full shadow-md bg-slate-900 hover:bg-brand-600 border border-slate-800 transition duration-300">
                    <span class="text-xs tracking-widest uppercase font-heading text-white">Portal Login</span>
                </a>
            @endauth
        </div>

        {{-- Tombol Hamburger Mobile --}}
        <button id="public-mobile-menu-btn" type="button" class="md:hidden text-slate-700 focus:outline-none p-2.5 rounded-xl hover:bg-brand-50 transition" aria-label="Buka Menu">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
    </nav>

    {{-- Dropdown Menu Mobile --}}
    <div id="public-mobile-menu" class="hidden md:hidden absolute top-full left-0 w-full bg-white border-t border-gray-100 shadow-xl z-40 transition-all">
        <div class="flex flex-col p-6 space-y-3">
            <a href="{{ url('/') }}" class="public-mobile-link text-sm font-bold {{ request()->routeIs('welcome') ? 'text-brand-600' : 'text-slate-700 hover:text-brand-600' }} py-2 border-b border-gray-50">
                Beranda Utama
            </a>
            <a href="{{ route('berita.index') }}" class="public-mobile-link text-sm font-bold {{ request()->routeIs('berita.index') || request()->routeIs('pengumuman.public.show') ? 'text-brand-600' : 'text-slate-700 hover:text-brand-600' }} py-2 border-b border-gray-50">
                Berita & Pengumuman
            </a>
            <a href="{{ route('dosen.public.index') }}" class="public-mobile-link text-sm font-bold {{ request()->routeIs('dosen.public.*') ? 'text-brand-600' : 'text-slate-700 hover:text-brand-600' }} py-2 border-b border-gray-50">
                Direktori Dosen
            </a>
            <a href="{{ route('dokumen.publik') }}" class="public-mobile-link text-sm font-bold {{ request()->routeIs('dokumen.publik') ? 'text-brand-600' : 'text-slate-700 hover:text-brand-600' }} py-2 border-b border-gray-50">
                Laci Dokumen Publik
            </a>
            <a href="{{ route('pmb.register') }}" class="public-mobile-link text-sm font-bold {{ request()->routeIs('pmb.register') ? 'text-brand-600' : 'text-slate-700 hover:text-brand-600' }} py-2 border-b border-gray-50">
                Pendaftaran Mahasiswa Baru (PMB)
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="bg-brand-600 text-white text-center py-3 rounded-xl font-bold tracking-widest text-xs uppercase shadow-md hover:bg-slate-900 mt-2 block">
                    Buka Dashboard SIAKAD
                </a>
            @else
                <a href="{{ route('login') }}" class="bg-slate-900 text-white text-center py-3 rounded-xl font-bold tracking-widest text-xs uppercase shadow-md hover:bg-brand-600 mt-2 block">
                    Masuk Portal SIAKAD
                </a>
            @endauth
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('public-mobile-menu-btn');
        const menu = document.getElementById('public-mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', function () {
                menu.classList.toggle('hidden');
            });
            document.querySelectorAll('.public-mobile-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    menu.classList.add('hidden');
                });
            });
        }
    });
</script>