@php
    $pubCfg      = \App\Models\Pengaturan::allCached();
    $pubTheme    = \App\Models\Pengaturan::themeColors();
    $pubLogo     = \App\Models\Pengaturan::logoUrl();
    $eventLogo   = \App\Models\Pengaturan::eventLogoUrl();
    $eventLabel  = $pubCfg['label_event_publik'] ?? '';
    $wmCardUrl   = \App\Models\Pengaturan::cardWatermarkUrl();
    $wmSecUrl    = \App\Models\Pengaturan::sectionWatermarkUrl();
    $pubProdis   = \App\Models\ProgramStudi::orderBy('nama_prodi')->get();
    $singkatanPt = $pubCfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
    $namaPt      = $pubCfg['nama_kampus'] ?? 'Sekolah Tinggi Teologi GPI Papua';
@endphp

<style>
    :root {
        --pub-primary: {{ $pubTheme['primary'] }};
        --pub-accent: {{ $pubTheme['accent'] }};
        --pub-radius: {{ $pubTheme['radius'] ?? '10px' }};
        --wm-opacity: {{ $pubTheme['watermark_opacity'] ?? '0.06' }};
    }
    .bg-pub-primary { background-color: var(--pub-primary) !important; }
    .text-pub-primary { color: var(--pub-primary) !important; }
    .border-pub-primary { border-color: var(--pub-primary) !important; }
    .border-t-pub-primary { border-top-color: var(--pub-primary) !important; }
    .border-l-pub-primary { border-left-color: var(--pub-primary) !important; }
    .bg-pub-accent { background-color: var(--pub-accent) !important; }
    .text-pub-accent { color: var(--pub-accent) !important; }
    .border-pub-accent { border-color: var(--pub-accent) !important; }
    .hover-bg-pub-primary:hover { background-color: var(--pub-primary) !important; color: #ffffff !important; }
    .hover-border-pub-primary:hover { border-color: var(--pub-primary) !important; }
    .pub-radius { border-radius: var(--pub-radius) !important; }
    .shadow-2xs { box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.04); }
    @media (min-width: 768px) {
        .items-md-center { align-items: center !important; }
    }

    /* Kartu Standar Publik: Bersih, Radius Proporsional, & Hover Halus */
    .wm-card {
        border-radius: var(--pub-radius);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .wm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -8px rgba(15, 23, 42, 0.08);
    }

    .hover-lift {
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 28px -8px rgba(15, 23, 42, 0.08), 0 4px 10px -4px rgba(15, 23, 42, 0.04);
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* Watermark Eksklusif Hanya pada Kartu Highlight Utama (.wm-featured & .wm-card-dark) */
    .wm-featured, .wm-card-dark {
        position: relative;
        overflow: hidden;
        border-radius: var(--pub-radius);
    }
    .wm-featured::after, .wm-card-dark::after {
        content: "";
        position: absolute;
        right: -20px;
        bottom: -20px;
        width: 175px;
        height: 175px;
        background-image: url('{{ $wmCardUrl }}');
        background-size: contain;
        background-repeat: no-repeat;
        background-position: bottom right;
        filter: grayscale(100%) contrast(120%);
        opacity: var(--wm-opacity);
        pointer-events: none;
        z-index: 0;
    }
    .wm-featured-dark::after, .wm-card-dark::after {
        filter: grayscale(100%) invert(100%) brightness(200%);
        opacity: 0.08;
    }
    .wm-featured > *, .wm-card-dark > * {
        position: relative;
        z-index: 1;
    }

    .wm-banner {
        position: relative;
        overflow: hidden;
        background-color: var(--pub-primary);
    }
    .wm-banner::before {
        content: "";
        position: absolute;
        right: 3%;
        top: 50%;
        transform: translateY(-50%);
        width: 240px;
        height: 240px;
        background-image: url('{{ $wmSecUrl }}');
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        filter: grayscale(100%) invert(100%);
        opacity: 0.06;
        pointer-events: none;
    }
</style>

{{-- TOP UTILITY BAR --}}
<div class="bg-pub-primary text-white text-[11px] border-b border-white/15">
    <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-2 flex flex-wrap justify-between items-center gap-2">
        <div class="flex items-center space-x-4 text-white/85 font-mono">
            <span>Terakreditasi {{ $pubCfg['akreditasi_institusi'] ?? 'BAN-PT' }}</span>
            @if(!empty($eventLabel))
                <span class="hidden sm:inline px-2 py-0.5 bg-white/15 text-white rounded font-sans font-semibold">{{ $eventLabel }}</span>
            @endif
            @if(!empty($pubCfg['email_kampus']))
                <span class="hidden md:inline">| {{ $pubCfg['email_kampus'] }}</span>
            @endif
        </div>

        <div class="flex items-center space-x-4 ml-auto font-semibold uppercase text-[10px] tracking-wider">
            <a href="{{ route('publik.perpustakaan') }}" class="text-white/85 hover:text-white transition">Katalog Perpustakaan</a>
            <a href="{{ route('publik.riset') }}" class="text-white/85 hover:text-white transition hidden sm:inline">Penelitian & PkM</a>
            <a href="{{ route('dokumen.publik') }}" class="text-white/85 hover:text-white transition">Dokumen</a>
            <a href="{{ route('pmb.register') }}" class="bg-white text-slate-900 font-bold px-2.5 py-0.5 rounded transition">Admisi PMB</a>
        </div>
    </div>
</div>

{{-- MAIN STICKY NAVBAR --}}
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-2xs">
    <nav class="container mx-auto px-4 lg:px-8 py-3.5 flex justify-between items-center max-w-7xl">
        <a href="{{ url('/') }}" class="flex items-center gap-4 group min-w-0" title="Halaman Utama {{ $singkatanPt }}">
            <img src="{{ $pubLogo }}" alt="Logo {{ $singkatanPt }}" class="h-11 w-11 object-contain transition-transform duration-300 group-hover:scale-105 shrink-0">
            @if($eventLogo)
                <div class="h-8 w-px bg-slate-200 shrink-0"></div>
                <img src="{{ $eventLogo }}" alt="{{ $eventLabel ?: 'Logo Kegiatan' }}" title="{{ $eventLabel }}" class="h-11 w-auto max-w-[90px] object-contain transition-transform duration-300 group-hover:scale-105 shrink-0">
            @endif
            <div class="pl-0.5 min-w-0">
                <span class="font-bold text-base sm:text-lg text-slate-900 tracking-tight block leading-none uppercase truncate">{{ $singkatanPt }}</span>
                <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block mt-1.5 max-w-[200px] sm:max-w-[260px] truncate">
                    {{ !empty($eventLabel) ? $eventLabel : $namaPt }}
                </span>
            </div>
        </a>

        <div class="hidden lg:flex items-center space-x-6">
            <div class="relative group">
                <button type="button" class="text-xs uppercase tracking-wider {{ request()->routeIs('publik.profil') || request()->routeIs('dosen.public.*') ? 'font-extrabold text-pub-primary' : 'font-semibold text-slate-600 group-hover:text-slate-950' }} flex items-center gap-1 py-2">
                    Tentang Kampus <i class="fa-solid fa-chevron-down text-[9px] opacity-60 transition-transform duration-200 group-hover:rotate-180"></i>
                </button>
                <div class="absolute left-0 top-full w-64 bg-white rounded-xl shadow-xl border border-slate-200 p-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <a href="{{ route('publik.profil') }}#sambutan" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">Sambutan {{ $pubCfg['sebutan_pimpinan'] ?? 'Ketua' }}</a>
                    <a href="{{ route('publik.profil') }}#sejarah" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">Sejarah & Visi-Misi</a>
                    <a href="{{ route('publik.profil') }}#struktur-yayasan" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">Struktur {{ $pubCfg['jenis_badan_penyelenggara'] ?? 'Sinode / Yayasan' }}</a>
                    <a href="{{ route('publik.profil') }}#struktur-kampus" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">Struktur Pimpinan Kampus</a>
                    <a href="{{ route('dosen.public.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">Direktori Dosen</a>
                </div>
            </div>

            <div class="relative group">
                <button type="button" class="text-xs uppercase tracking-wider {{ request()->routeIs('publik.prodi.show') ? 'font-extrabold text-pub-primary' : 'font-semibold text-slate-600 group-hover:text-slate-950' }} flex items-center gap-1 py-2">
                    Program Studi <i class="fa-solid fa-chevron-down text-[9px] opacity-60 transition-transform duration-200 group-hover:rotate-180"></i>
                </button>
                <div class="absolute left-0 top-full w-64 bg-white rounded-xl shadow-xl border border-slate-200 p-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    @foreach($pubProdis as $pr)
                        <a href="{{ route('publik.prodi.show', $pr->id) }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">
                            {{ $pr->nama_prodi }} ({{ $pr->jenjang ?? 'S1' }})
                        </a>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('publik.riset') }}" class="text-xs uppercase tracking-wider {{ request()->routeIs('publik.riset') ? 'font-extrabold text-pub-primary border-b-2 border-pub-primary pb-1' : 'font-semibold text-slate-600 hover:text-slate-950' }} transition">
                Riset & PkM
            </a>

            <a href="{{ route('publik.perpustakaan') }}" class="text-xs uppercase tracking-wider {{ request()->routeIs('publik.perpustakaan') ? 'font-extrabold text-pub-primary border-b-2 border-pub-primary pb-1' : 'font-semibold text-slate-600 hover:text-slate-950' }} transition">
                Perpustakaan
            </a>

            <a href="{{ route('berita.index') }}" class="text-xs uppercase tracking-wider {{ request()->routeIs('berita.index') || request()->routeIs('pengumuman.public.show') ? 'font-extrabold text-pub-primary border-b-2 border-pub-primary pb-1' : 'font-semibold text-slate-600 hover:text-slate-950' }} transition">
                Berita
            </a>
        </div>

        <div class="hidden lg:flex items-center space-x-2.5">
            @auth
                <a href="{{ route('dashboard') }}" class="bg-pub-primary text-white font-bold px-4 py-2 pub-radius text-xs uppercase tracking-wider transition shadow-2xs hover:opacity-95">
                    Dashboard SIAKAD
                </a>
            @else
                <a href="{{ route('pmb.register') }}" class="border border-slate-300 text-slate-800 hover:border-slate-900 font-bold px-4 py-2 pub-radius text-xs uppercase tracking-wider transition">
                    Daftar PMB
                </a>
                <a href="{{ route('login') }}" class="bg-pub-primary text-white font-bold px-4 py-2 pub-radius text-xs uppercase tracking-wider transition shadow-2xs hover:opacity-95">
                    Portal SIAKAD
                </a>
            @endauth
        </div>

        <div class="lg:hidden shrink-0">
            <button id="mobile-menu-button" type="button" class="text-slate-800 border border-slate-300 rounded-lg px-3 py-1.5 text-xs font-bold uppercase">
                Menu
            </button>
        </div>
    </nav>

    <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-slate-200 px-6 py-4 space-y-2 shadow-lg">
        <a href="{{ route('publik.profil') }}" class="block py-1.5 text-xs font-bold uppercase text-slate-800 border-b border-slate-100">Profil & Struktur Organisasi</a>
        @foreach($pubProdis as $pr)
            <a href="{{ route('publik.prodi.show', $pr->id) }}" class="block py-1.5 pl-3 text-xs font-semibold text-slate-600 border-b border-slate-100">&bull; Prodi {{ $pr->nama_prodi }}</a>
        @endforeach
        <a href="{{ route('dosen.public.index') }}" class="block py-1.5 text-xs font-bold uppercase text-slate-800 border-b border-slate-100">Direktori Dosen</a>
        <a href="{{ route('publik.riset') }}" class="block py-1.5 text-xs font-bold uppercase text-slate-800 border-b border-slate-100">Penelitian & Pengabdian</a>
        <a href="{{ route('publik.perpustakaan') }}" class="block py-1.5 text-xs font-bold uppercase text-slate-800 border-b border-slate-100">Katalog Perpustakaan</a>
        <a href="{{ route('berita.index') }}" class="block py-1.5 text-xs font-bold uppercase text-slate-800 border-b border-slate-100">Berita & Pengumuman</a>
        <a href="{{ route('dokumen.publik') }}" class="block py-1.5 text-xs font-bold uppercase text-slate-800 border-b border-slate-100">Dokumen Publik</a>
        <div class="pt-2 flex flex-col gap-2">
            <a href="{{ route('pmb.register') }}" class="block text-center border border-slate-300 text-slate-800 font-bold py-2 pub-radius text-xs uppercase">Pendaftaran PMB</a>
            @auth
                <a href="{{ route('dashboard') }}" class="block text-center bg-pub-primary text-white font-bold py-2 pub-radius text-xs uppercase">Dashboard SIAKAD</a>
            @else
                <a href="{{ route('login') }}" class="block text-center bg-pub-primary text-white font-bold py-2 pub-radius text-xs uppercase">Portal SIAKAD</a>
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