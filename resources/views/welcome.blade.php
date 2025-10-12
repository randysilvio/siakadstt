<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Administrasi Kampus - STT GPI Papua</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Figtree', sans-serif; background-color: #f8fafc; }
        .font-heading { font-family: 'Raleway', sans-serif; }
        .splide__slide img {
            width: 100%;
            height: 65vh;
            object-fit: cover;
        }
        .quick-link-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .quick-link-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="antialiased text-slate-800">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ route('welcome') }}" class="flex items-center space-x-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI Papua" class="h-12 w-12">
                <div class="hidden sm:block">
                    <span class="font-bold text-lg text-slate-800">STT GPI Papua</span>
                    <p class="text-xs text-gray-500">Sistem Informasi Akademik</p>
                </div>
            </a>
            <div class="flex items-center space-x-2 md:space-x-4">
                 <a href="#berita" class="text-gray-600 hover:text-teal-600 font-medium hidden md:block">Berita</a>
                 <a href="#prodi" class="text-gray-600 hover:text-teal-600 font-medium hidden md:block">Program Studi</a>
                 <a href="#dokumen" class="text-gray-600 hover:text-teal-600 font-medium hidden md:block">Dokumen</a>
                 <a href="{{ route('login') }}" class="bg-teal-500 hover:bg-teal-600 text-white font-bold py-2 px-6 rounded-full transition duration-300 text-sm">
                    LOGIN
                </a>
            </div>
        </nav>
    </header>

    <main>
        <section class="relative text-white">
            @if($slides->isNotEmpty())
                <div id="hero-slideshow" class="splide" aria-label="Galeri Kegiatan Kampus">
                    <div class="splide__track"><ul class="splide__list">
                        @foreach($slides as $slide)
                        <li class="splide__slide">
                            <img src="{{ asset('storage/' . $slide->gambar) }}" alt="{{ $slide->judul }}">
                            <div class="absolute inset-0 bg-slate-800/50"></div>
                            <div class="absolute inset-0 z-10 container mx-auto px-6 h-full flex flex-col items-start justify-center text-left">
                                @if($slide->judul)
                                <h1 class="text-4xl md:text-5xl font-heading font-extrabold max-w-2xl" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.7);">{{ $slide->judul }}</h1>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul></div>
                </div>
            @endif
        </section>

        <section id="akses-cepat" class="bg-white -mt-16 relative z-20 container mx-auto rounded-xl shadow-lg p-6">
             <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 text-center">
                <a href="{{ route('berita.index') }}" class="quick-link-card p-4 rounded-lg">
                    <div class="flex justify-center items-center h-16 w-16 bg-sky-100 text-sky-600 rounded-full mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3h2m-4 3h2m-4 3h2m-4 3h2" /></svg>
                    </div>
                    <p class="mt-3 font-semibold text-sm text-slate-700">Berita & Info</p>
                </a>
                <a href="{{ route('dosen.public.index') }}" class="quick-link-card p-4 rounded-lg">
                    <div class="flex justify-center items-center h-16 w-16 bg-teal-100 text-teal-600 rounded-full mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-1a6 6 0 00-1.781-4.121M12 10.875a4 4 0 100-5.292M12 10.875a4 4 0 110 5.292m0 0v2.25m0 0c-1.472 0-2.882.265-4.185.75M12 16.5c1.472 0 2.882.265 4.185.75m-8.37 0a4.5 4.5 0 01-4.185-.75M12 10.875a4 4 0 100-5.292M12 10.875c-1.472 0-2.882.265-4.185.75M12 10.875c1.472 0 2.882.265 4.185.75m-4.185 5.25a4.5 4.5 0 01-4.185-.75" /></svg>
                    </div>
                    <p class="mt-3 font-semibold text-sm text-slate-700">Direktori Dosen</p>
                </a>
                <a href="#dokumen" class="quick-link-card p-4 rounded-lg">
                    <div class="flex justify-center items-center h-16 w-16 bg-indigo-100 text-indigo-600 rounded-full mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <p class="mt-3 font-semibold text-sm text-slate-700">Unduh Dokumen</p>
                </a>
                <a href="#kontak" class="quick-link-card p-4 rounded-lg">
                    <div class="flex justify-center items-center h-16 w-16 bg-gray-100 text-gray-600 rounded-full mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2V7a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H17z" /></svg>
                    </div>
                    <p class="mt-3 font-semibold text-sm text-slate-700">Kontak Kami</p>
                </a>
                <a href="{{ route('login') }}" class="quick-link-card p-4 rounded-lg">
                    <div class="flex justify-center items-center h-16 w-16 bg-amber-100 text-amber-600 rounded-full mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                    </div>
                    <p class="mt-3 font-semibold text-sm text-slate-700">Login SIAKAD</p>
                </a>
            </div>
        </section>

        <section id="statistik" class="py-20">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold font-heading text-slate-800">Kampus dalam Angka</h2>
                    <p class="text-gray-600 mt-2">Data statistik terkini dari komunitas akademik kami.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-center max-w-4xl mx-auto">
                    <div class="bg-white border border-gray-200 p-8 rounded-xl shadow-sm">
                        <div class="text-teal-500 mb-4">
                            <svg class="h-12 w-12 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </div>
                        <h3 class="text-5xl font-bold text-slate-800" data-counter-target="{{ $totalMahasiswa ?? 0 }}">0</h3>
                        <p class="text-gray-600 mt-2 text-xl font-semibold">Mahasiswa Aktif</p>
                    </div>
                    <div class="bg-white border border-gray-200 p-8 rounded-xl shadow-sm">
                        <div class="text-sky-500 mb-4">
                           <svg class="h-12 w-12 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0l-2.072-1.037a3.75 3.75 0 01-1.08-5.320c.426-.85.99-1.643 1.66-2.385a3.75 3.75 0 015.321 1.08l2.072 1.037m0 0a48.29 48.29 0 005.372 0l2.072-1.037a3.75 3.75 0 015.321-1.08c.67.742 1.235 1.536 1.66 2.385a3.75 3.75 0 01-1.08 5.321l-2.072 1.037m0 0c-2.228.328-4.536.46-6.872.46s-4.644-.132-6.872-.46m13.745 0l-1.55-2.68a3.75 3.75 0 00-5.321-1.08l-1.55 2.68m0 0a49.331 49.331 0 00-2.343.834" /></svg>
                        </div>
                        <h3 class="text-5xl font-bold text-slate-800" data-counter-target="{{ $totalDosen ?? 0 }}">0</h3>
                        <p class="text-gray-600 mt-2 text-xl font-semibold">Dosen Pengajar</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="berita" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-left mb-12">
                    <h2 class="text-4xl font-bold font-heading text-slate-800">Berita & Informasi</h2>
                    <p class="text-gray-600 mt-2">Dapatkan kabar dan pengumuman terbaru dari kampus.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                    @if($beritaUtama)
                    <a href="{{ route('pengumuman.public.show', $beritaUtama) }}" class="group block">
                        <div class="overflow-hidden rounded-xl shadow-md">
                            <img src="{{ $beritaUtama->foto ? asset('storage/' . $beritaUtama->foto) : 'https://via.placeholder.com/800x500/64748b/ffffff?text=Berita+Utama' }}" alt="{{ $beritaUtama->judul }}" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500" style="aspect-ratio: 16/10;">
                        </div>
                        <p class="text-sm text-teal-600 font-semibold mt-4">{{ $beritaUtama->created_at->isoFormat('D MMMM YYYY') }}</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-2 group-hover:text-teal-700 transition-colors">{{ $beritaUtama->judul }}</h3>
                        <p class="text-gray-600 mt-2 leading-relaxed">{{ Str::limit(strip_tags($beritaUtama->konten), 120) }}</p>
                    </a>
                    @endif

                    <div class="space-y-6">
                        @forelse($beritaLainnya as $item)
                        <a href="{{ route('pengumuman.public.show', $item) }}" class="group flex items-center space-x-4">
                            <div class="w-24 h-24 lg:w-32 lg:h-32 flex-shrink-0 overflow-hidden rounded-lg shadow-sm">
                                <img src="{{ $item->foto ? asset('storage/' . $item->foto) : 'https://via.placeholder.com/200x200/94a3b8/ffffff?text=Info' }}" alt="{{ $item->judul }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            </div>
                            <div>
                                <p class="text-xs text-teal-600 font-semibold">{{ $item->created_at->isoFormat('D MMMM YYYY') }}</p>
                                <h4 class="font-bold text-slate-800 mt-1 group-hover:text-teal-700 transition-colors">{{ $item->judul }}</h4>
                            </div>
                        </a>
                        @empty
                        <p class="text-gray-500">Belum ada berita lainnya.</p>
                        @endforelse
                        <div class="pt-4">
                           <a href="{{ route('berita.index') }}" class="bg-teal-500 hover:bg-teal-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300">Lihat Semua Berita</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="prodi" class="py-20 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold font-heading text-slate-800">Program Studi</h2>
                    <p class="text-gray-600 mt-2">Temukan panggilan dan tujuan Anda bersama kami.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    @forelse ($programStudi as $prodi)
                    <div class="bg-white border border-gray-200 rounded-xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col text-center">
                        <h3 class="text-2xl font-bold text-slate-800">{{ $prodi->nama_prodi }}</h3>
                        <p class="text-gray-700 mt-4 flex-grow">{{ $prodi->deskripsi_singkat ?? 'Program studi unggulan yang dirancang untuk mempersiapkan pemimpin masa depan.' }}</p>
                        <a href="#" class="text-teal-600 font-semibold mt-6 inline-block hover:text-teal-700">Selengkapnya →</a>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 md:col-span-2">Informasi program studi akan segera tersedia.</p>
                    @endforelse
                </div>
            </div>
        </section>
        
        <section id="dokumen" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold font-heading text-slate-800">Unduh Dokumen</h2>
                    <p class="text-gray-600 mt-2">Dokumen dan formulir penting yang bisa Anda unduh.</p>
                </div>
                <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <ul class="space-y-4">
                        @forelse($dokumen as $doc)
                        <li class="p-4 flex items-center justify-between border-b last:border-b-0">
                            <div class="flex items-center space-x-4">
                               <svg class="h-6 w-6 text-teal-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                <h4 class="font-bold text-slate-800">{{ $doc->judul_dokumen }}</h4>
                            </div>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" download class="bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-full transition duration-300 text-sm">Unduh</a>
                        </li>
                        @empty
                        <li class="text-center text-gray-500 py-4">Belum ada dokumen yang tersedia.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </section>

    </main>

    <footer id="kontak" class="bg-slate-800 text-white">
        <div class="container mx-auto px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <div>
                    <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI Papua" class="h-16 w-16 mb-4">
                    <p class="text-gray-400 text-sm">Sekolah Tinggi Teologi Gereja Protestan Indonesia di Papua. Mempersiapkan pemimpin yang melayani dengan integritas dan kompetensi.</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-white mb-4">Kontak Kami</h3>
                    <ul class="text-gray-400 text-sm space-y-3">
                        <li class="flex items-start"><svg class="h-5 w-5 mr-3 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>Jl. Jenderal Sudirman, Fakfak, Papua Barat</li>
                        <li class="flex items-start"><svg class="h-5 w-5 mr-3 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /></svg>info@sttgpipapua.ac.id</li>
                        <li class="flex items-start"><svg class="h-5 w-5 mr-3 mt-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" /></svg>(0956) 123-456</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-white mb-4">Akademik</h3>
                    <ul class="text-gray-400 text-sm space-y-2">
                        <li><a href="#prodi" class="hover:text-teal-400 transition-colors">Program Studi</a></li>
                        <li><a href="#dokumen" class="hover:text-teal-400 transition-colors">Unduh Dokumen</a></li>
                    </ul>
                </div>
                 <div>
                    <h3 class="font-bold text-lg text-white mb-4">Mahasiswa</h3>
                    <ul class="text-gray-400 text-sm space-y-2">
                        <li><a href="{{ route('login') }}" class="hover:text-teal-400 transition-colors">Login SIAKAD</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Penerimaan Mahasiswa Baru</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition-colors">Layanan Mahasiswa</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="bg-slate-900 py-4">
            <div class="container mx-auto px-6 text-center text-sm text-gray-500">
                © {{ date('Y') }} STT GPI Papua. Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi Slideshow
        if(document.querySelector('#hero-slideshow')){
          new Splide('#hero-slideshow',{
            type: 'fade',
            rewind: true,
            perPage: 1,
            autoplay: true,
            interval: 5000,
            pauseOnHover: false,
            arrows: false,
            pagination: true,
          }).mount();
        }

        // Inisialisasi Counter Statistik
        const counters = document.querySelectorAll('[data-counter-target]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-counter-target'), 10);
                    const duration = 1500;
                    if (target === 0) {
                        counter.innerText = '0';
                        observer.unobserve(counter);
                        return;
                    }
                    const increment = target / (duration / 16);
                    let current = 0;
                    
                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            counter.innerText = Math.ceil(current).toLocaleString('id-ID');
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.innerText = target.toLocaleString('id-ID');
                        }
                    };
                    requestAnimationFrame(updateCounter);
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => observer.observe(counter));
      });
    </script>
</body>
</html>