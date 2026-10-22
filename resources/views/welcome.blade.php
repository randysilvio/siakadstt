<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Informasi Akademik - STT GPI Papua</title>

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
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
        .hero-height { height: 80vh; }
        @media (max-width: 768px) { .hero-height { height: 60vh; } }
        .splide__slide img { width: 100%; height: 100%; object-fit: cover; }
        .glass-effect { background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        {{-- ================= HERO SLIDESHOW SINEMATIS ================= --}}
        <section class="relative bg-slate-950 overflow-hidden">
            @if(isset($slides) && $slides->isNotEmpty())
                <div id="hero-slideshow" class="splide hero-height" aria-label="Galeri Kampus">
                    <div class="splide__track h-full">
                        <ul class="splide__list h-full">
                            @foreach($slides as $slide)
                            <li class="splide__slide relative">
                                <img src="{{ asset('storage/' . $slide->gambar) }}" alt="{{ $slide->judul }}" class="scale-105 transform transition-transform duration-1000 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-black/20"></div>
                                <div class="absolute bottom-0 left-0 w-full p-8 md:p-16 pb-20 md:pb-28">
                                    <div class="container mx-auto max-w-6xl">
                                        @if($slide->judul)
                                            <h2 class="text-3xl md:text-5xl lg:text-6xl font-heading font-extrabold text-white mb-4 leading-tight tracking-tight drop-shadow-md max-w-4xl">
                                                {{ $slide->judul }}
                                            </h2>
                                        @endif
                                        <div class="h-1.5 w-24 bg-brand-500 rounded-full mt-2 shadow-lg"></div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="hero-height relative bg-slate-900 flex items-center justify-center bg-cover bg-center" style="background-image: url('{{ asset('images/latar-welcome.jpg') }}');">
                    <div class="absolute inset-0 bg-gradient-to-tr from-slate-950 via-slate-900/80 to-transparent"></div>
                    <div class="relative z-10 text-center text-white px-6 max-w-3xl">
                        <span class="text-brand-400 font-bold uppercase tracking-widest text-xs block mb-3">Selamat Datang di</span>
                        <h1 class="text-4xl md:text-6xl font-heading font-extrabold tracking-tight mb-4">STT GPI PAPUA</h1>
                        <p class="text-lg md:text-xl text-gray-300 font-light">Melahirkan Lulusan Teologi Unggul, Kompeten, dan Siap Melayani di Era Digital.</p>
                    </div>
                </div>
            @endif
        </section>

        {{-- ================= BILAH MENU AKSES CEPAT ================= --}}
        <section class="relative z-20 -mt-12 container mx-auto px-4 max-w-6xl">
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-gray-100">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6">
                    <a href="{{ route('berita.index') }}" class="group flex flex-col items-center text-center p-3 rounded-xl hover:bg-brand-50 transition duration-300">
                        <div class="h-12 w-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 group-hover:scale-110 transition duration-300 shadow-sm group-hover:bg-sky-600 group-hover:text-white">
                            <i class="fa-regular fa-newspaper text-xl"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-sky-600">Portal Berita</span>
                    </a>

                    <a href="{{ route('dosen.public.index') }}" class="group flex flex-col items-center text-center p-3 rounded-xl hover:bg-brand-50 transition duration-300">
                        <div class="h-12 w-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-3 group-hover:scale-110 transition duration-300 shadow-sm group-hover:bg-brand-600 group-hover:text-white">
                            <i class="fa-solid fa-chalkboard-user text-xl"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-brand-600">Direktori Dosen</span>
                    </a>

                    <a href="{{ route('dokumen.publik') }}" class="group flex flex-col items-center text-center p-3 rounded-xl hover:bg-brand-50 transition duration-300">
                        <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:scale-110 transition duration-300 shadow-sm group-hover:bg-indigo-600 group-hover:text-white">
                            <i class="fa-solid fa-cloud-arrow-down text-xl"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-indigo-600">Unduh Dokumen</span>
                    </a>

                    <a href="#kontak" class="group flex flex-col items-center text-center p-3 rounded-xl hover:bg-brand-50 transition duration-300">
                        <div class="h-12 w-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-3 group-hover:scale-110 transition duration-300 shadow-sm group-hover:bg-orange-600 group-hover:text-white">
                            <i class="fa-solid fa-headset text-xl"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-orange-600">Hubungi Kami</span>
                    </a>

                    <a href="{{ route('pmb.register') }}" class="group flex flex-col items-center text-center p-3 rounded-xl hover:bg-brand-50 transition duration-300 col-span-2 md:col-span-1">
                        <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition duration-300 shadow-sm group-hover:bg-emerald-600 group-hover:text-white">
                            <i class="fa-solid fa-user-graduate text-xl"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-600">Pendaftaran PMB</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- ================= STATISTIK EKSKLUSIF ================= --}}
        <section class="py-16 bg-white border-b border-gray-100">
            <div class="container mx-auto px-6 max-w-5xl">
                <div class="text-center mb-10">
                    <span class="text-xs font-bold uppercase tracking-widest text-brand-600 block mb-1">Jendela Statistik</span>
                    <h2 class="text-3xl font-heading font-extrabold text-slate-900 tracking-tight">Komunitas Akademik Kami</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="relative overflow-hidden rounded-2xl p-8 bg-gradient-to-br from-brand-50 via-white to-white border border-brand-100 shadow-sm hover:shadow-md transition duration-300 text-center flex flex-col justify-center items-center">
                        <div class="absolute top-0 right-0 p-6 opacity-5 text-brand-600"><i class="fa-solid fa-users text-8xl"></i></div>
                        <span class="text-4xl md:text-5xl font-extrabold text-brand-600 font-heading block mb-2" data-counter-target="{{ $totalMahasiswa ?? 0 }}">0</span>
                        <p class="text-sm font-bold text-slate-600 uppercase tracking-wider">Mahasiswa Terdaftar Aktif</p>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl p-8 bg-gradient-to-br from-sky-50 via-white to-white border border-sky-100 shadow-sm hover:shadow-md transition duration-300 text-center flex flex-col justify-center items-center">
                        <div class="absolute top-0 right-0 p-6 opacity-5 text-sky-600"><i class="fa-solid fa-user-tie text-8xl"></i></div>
                        <span class="text-4xl md:text-5xl font-extrabold text-sky-600 font-heading block mb-2" data-counter-target="{{ $totalDosen ?? 0 }}">0</span>
                        <p class="text-sm font-bold text-slate-600 uppercase tracking-wider">Dosen & Pengajar Ahli</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= BERITA & AGENDA ================= --}}
        <section id="berita" class="py-20 bg-slate-50">
            <div class="container mx-auto px-6 max-w-6xl">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    
                    {{-- Berita Terkini --}}
                    <div class="lg:col-span-2">
                        <div class="flex justify-between items-end mb-8">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-widest text-brand-600 block mb-1">Warta Kampus</span>
                                <h2 class="text-3xl font-heading font-extrabold text-slate-900 tracking-tight">Berita Terkini</h2>
                            </div>
                            <a href="{{ route('berita.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-800 transition flex items-center group">
                                Lihat Indeks <i class="fa-solid fa-arrow-right ml-1.5 transition transform group-hover:translate-x-1"></i>
                            </a>
                        </div>

                        @if(isset($beritaUtama) && $beritaUtama)
                        <article class="group relative rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition duration-500 mb-8 bg-white border border-gray-100 h-[380px]">
                            <img src="{{ $beritaUtama->foto ? asset('storage/' . $beritaUtama->foto) : asset('images/latar-welcome.jpg') }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-700 ease-out" alt="{{ $beritaUtama->judul }}">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 p-6 md:p-8 w-full z-10">
                                <span class="inline-block px-3 py-1 bg-brand-600 text-white text-[10px] font-bold rounded uppercase tracking-wider mb-3">
                                    {{ $beritaUtama->kategori }}
                                </span>
                                <a href="{{ route('pengumuman.public.show', $beritaUtama) }}" class="block mb-2">
                                    <h3 class="text-xl md:text-2xl font-bold text-white group-hover:text-brand-300 transition leading-snug">
                                        {{ $beritaUtama->judul }}
                                    </h3>
                                </a>
                                <p class="text-gray-300 text-xs line-clamp-2 max-w-2xl font-light mb-3">
                                    {{ Str::limit(strip_tags($beritaUtama->konten), 120) }}
                                </p>
                                <span class="text-[11px] text-brand-400 font-semibold flex items-center">
                                    <i class="fa-regular fa-clock mr-1.5"></i> {{ $beritaUtama->created_at->isoFormat('D MMMM YYYY') }}
                                </span>
                            </div>
                        </article>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @forelse($beritaLainnya as $item)
                            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition duration-300 border border-gray-100 flex flex-col overflow-hidden h-full">
                                <div class="h-44 overflow-hidden relative bg-slate-100">
                                    <img src="{{ $item->foto ? asset('storage/' . $item->foto) : asset('images/latar-welcome.jpg') }}" class="w-full h-full object-cover transition duration-500 hover:scale-105" alt="{{ $item->judul }}">
                                    <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                        {{ $item->kategori }}
                                    </span>
                                </div>
                                <div class="p-5 flex-grow flex flex-col justify-between">
                                    <div>
                                        <span class="text-[11px] text-brand-600 font-bold block mb-1.5">{{ $item->created_at->isoFormat('D MMMM YYYY') }}</span>
                                        <a href="{{ route('pengumuman.public.show', $item) }}" class="block mb-2 text-slate-900 hover:text-brand-600 transition font-bold leading-snug text-base line-clamp-2">
                                            {{ $item->judul }}
                                        </a>
                                    </div>
                                    <p class="text-xs text-slate-500 line-clamp-2">{{ Str::limit(strip_tags($item->konten), 90) }}</p>
                                </div>
                            </div>
                            @empty
                                @if(!isset($beritaUtama))
                                <div class="col-span-2 text-center text-slate-400 py-6 text-sm">Arsip berita sedang disiapkan.</div>
                                @endif
                            @endforelse
                        </div>
                    </div>

                    {{-- Kolom Agenda --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition duration-300 border border-gray-100 overflow-hidden sticky top-24">
                            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex justify-between items-center">
                                <h3 class="font-heading font-bold text-base tracking-wide">Agenda Kampus</h3>
                                <span class="bg-brand-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">Mendatang</span>
                            </div>
                            <div class="divide-y divide-gray-50">
                                @forelse($kegiatanTerdekat as $kegiatan)
                                <div class="p-5 hover:bg-slate-50/80 transition duration-200 group">
                                    <div class="flex items-start space-x-3">
                                        <div class="bg-brand-50 border border-brand-100 rounded-xl p-2 w-14 text-center flex-shrink-0 group-hover:bg-brand-600 transition duration-300 text-brand-700 group-hover:text-white">
                                            <span class="block text-xl font-extrabold leading-tight">{{ $kegiatan->tanggal_mulai->format('d') }}</span>
                                            <span class="block text-[10px] uppercase font-bold tracking-tighter">{{ $kegiatan->tanggal_mulai->isoFormat('MMM') }}</span>
                                        </div>
                                        <div class="flex-grow">
                                            <h4 class="font-bold text-slate-900 text-sm leading-snug mb-1 group-hover:text-brand-600 transition">{{ $kegiatan->judul_kegiatan }}</h4>
                                            <p class="text-xs text-slate-500 mb-2 line-clamp-1">{{ $kegiatan->deskripsi }}</p>
                                            <span class="inline-flex items-center text-[11px] font-medium text-slate-400">
                                                <i class="fa-regular fa-calendar mr-1 text-brand-500"></i> {{ $kegiatan->tanggal_mulai->isoFormat('D MMM Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="p-8 text-center text-slate-400">
                                    <i class="fa-regular fa-calendar-xmark text-3xl mb-2 opacity-50 block"></i>
                                    <span class="text-xs">Belum ada agenda akademik terdekat.</span>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ================= PROGRAM STUDI ================= --}}
        <section id="prodi" class="py-20 bg-white border-b border-gray-100">
            <div class="container mx-auto px-6 max-w-5xl">
                <div class="text-center mb-12">
                    <span class="text-xs font-bold uppercase tracking-widest text-brand-600 block mb-1">Masa Depan Pelayanan</span>
                    <h2 class="text-3xl font-heading font-extrabold text-slate-900 tracking-tight">Program Studi Unggulan</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @forelse ($programStudi as $prodi)
                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:border-brand-200 transition duration-300 relative overflow-hidden flex flex-col justify-between group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-brand-50 to-transparent rounded-bl-full z-0 opacity-50 group-hover:scale-125 transition duration-500"></div>
                        <div class="relative z-10 mb-6">
                            <span class="text-xs font-extrabold text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-100 mb-3 inline-block">
                                {{ $prodi->kode_prodi ? 'KODE: ' . $prodi->kode_prodi : 'Terakreditasi BAN-PT' }}
                            </span>
                            <h3 class="text-2xl font-bold text-slate-900 mb-2 group-hover:text-brand-600 transition">{{ $prodi->nama_prodi }}</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mt-3">
                                {{ $prodi->deskripsi_singkat ?? 'Membekali mahasiswa dengan penguasaan teologi kontekstual, kepemimpinan pastoral, dan dedikasi pelayanan gerejawi yang mendalam.' }}
                            </p>
                        </div>
                        <div class="relative z-10 pt-4 border-t border-gray-50">
                            <a href="{{ route('pmb.register') }}" class="inline-flex items-center text-xs font-bold text-brand-600 group-hover:translate-x-1 transition duration-300">
                                Daftar Program Studi Ini <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-2 text-center text-slate-400 text-sm py-4">Data spesifikasi program studi sedang diperbarui.</div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ================= UNDUH DOKUMEN (PREVIEW TERBARU) ================= --}}
        <section id="dokumen" class="py-16 bg-slate-50">
            <div class="container mx-auto px-6 max-w-5xl">
                <div class="text-center md:text-left mb-10">
                    <h2 class="text-2xl font-heading font-extrabold text-slate-900 tracking-tight">Dokumen Publik Terbaru</h2>
                    <p class="text-slate-500 text-sm mt-1">Akses cepat berkas pedoman akademik dan administrasi resmi yang baru saja diunggah.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse($dokumen as $doc)
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition flex items-start space-x-3.5 group">
                        <div class="bg-rose-50 text-rose-600 p-2.5 rounded-lg group-hover:bg-rose-600 group-hover:text-white transition shadow-sm">
                            <i class="fa-solid fa-file-pdf text-lg"></i>
                        </div>
                        <div class="flex-grow min-w-0">
                            <h4 class="font-bold text-slate-900 text-sm mb-1 truncate group-hover:text-rose-600 transition" title="{{ $doc->judul_dokumen }}">{{ $doc->judul_dokumen }}</h4>
                            <span class="text-[10px] text-slate-400 block mb-2 font-medium">{{ $doc->created_at->format('d M Y') }} | {{ $doc->kategori ?? 'Umum' }}</span>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-xs font-bold text-rose-600 hover:text-rose-800 inline-flex items-center">
                                Buka Dokumen <i class="fa-regular fa-eye ml-1 text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-slate-400 text-sm">Belum ada dokumen yang dibagikan secara publik.</div>
                    @endforelse
                </div>

                <div class="mt-12 text-center">
                    <a href="{{ route('dokumen.publik') }}" class="inline-flex items-center justify-center px-8 py-3.5 font-bold text-white rounded-full shadow-lg bg-slate-900 hover:bg-brand-600 border border-slate-800 hover:border-brand-600 transition duration-300 group">
                        <span class="text-sm tracking-widest uppercase font-heading">Buka Laci Arsip Digital Lengkap</span>
                        <i class="fa-solid fa-arrow-right ml-3 transform group-hover:translate-x-1 transition"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    @include('partials.public_footer')

    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelector('#hero-slideshow')) {
          new Splide('#hero-slideshow', {
            type: 'fade', rewind: true, autoplay: true, interval: 6000, arrows: false, pagination: true, pauseOnHover: false
          }).mount();
        }

        const counters = document.querySelectorAll('[data-counter-target]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-counter-target'), 10);
                    if (target <= 0) return;
                    let current = 0;
                    const increment = target / 80;
                    const update = () => {
                        current += increment;
                        if (current < target) {
                            counter.innerText = Math.ceil(current).toLocaleString('id-ID');
                            requestAnimationFrame(update);
                        } else {
                            counter.innerText = target.toLocaleString('id-ID');
                        }
                    };
                    update();
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(c => observer.observe(c));
      });
    </script>
</body>
</html>