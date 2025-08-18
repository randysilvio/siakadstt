<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Administrasi Kampus - STT GPI Papua</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700|raleway:700,800&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Figtree', sans-serif; }
        .font-heading { font-family: 'Raleway', sans-serif; }
    </style>
</head>
<body class="antialiased bg-gray-100">

    <header class="bg-white shadow-md sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="{{ route('welcome') }}" class="flex items-center space-x-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI Papua" class="h-10 w-10">
                <span class="font-bold text-lg text-purple-700 hidden sm:block">STT GPI Papua</span>
            </a>
            <div class="flex items-center space-x-4">
                 <a href="#prodi" class="text-gray-600 hover:text-purple-700 hidden md:block">Program Studi</a>
                 <a href="#statistik" class="text-gray-600 hover:text-purple-700 hidden md:block">Statistik</a>
                 <a href="#berita" class="text-gray-600 hover:text-purple-700 hidden md:block">Berita</a>
                 <a href="#dokumen" class="text-gray-600 hover:text-purple-700 hidden md:block">Dokumen</a>
                 <a href="{{ route('login') }}" class="bg-yellow-500 hover:bg-yellow-600 text-purple-700 font-bold py-2 px-6 rounded-full transition duration-300">
                    Login
                </a>
            </div>
        </nav>
    </header>

    <main>
        <section class="relative text-white">
            @if($slides->isNotEmpty())
                <div id="hero-slideshow" class="splide" aria-label="Galeri Kegiatan Kampus">
                    <div class="splide__track">
                        <ul class="splide__list">
                            @foreach($slides as $slide)
                            <li class="splide__slide">
                                <div class="relative w-full h-[70vh]">
                                    <img src="{{ asset('storage/' . $slide->gambar) }}" alt="{{ $slide->judul }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-purple-800/20"></div> {{-- ubah opacity. --}}
                                    <div class="absolute inset-0 z-10 container mx-auto px-6 h-full flex flex-col items-center justify-center text-center" style="padding-top: 25rem;">
                                        @if($slide->judul)
                                            <h1 class="text-3xl md:text-5xl font-heading font-extrabold tracking-wider leading-tight uppercase text-yellow-400">{{ $slide->judul }}</h1>
                                        @endif
                                        <h2 class="text-xl md:text-2xl font-semibold text-yellow-300 mt-2">STT GPI PAPUA</h2>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="relative h-[70vh]" style="background-image: url('{{ asset('images/latar-welcome-ungu-kuning.jpg') }}'); background-size: cover; background-position: center;">
                     <div class="absolute inset-0 bg-purple-800/70"></div>
                     <div class="relative z-10 container mx-auto px-6 h-full flex flex-col items-center justify-center text-center">
                        <h1 class="text-3xl md:text-5xl font-heading font-extrabold tracking-wider leading-tight uppercase text-yellow-400">Selamat Datang di SIAKAD</h1>
                        <h2 class="text-xl md:text-2xl font-semibold text-yellow-300 mt-2">STT GPI PAPUA</h2>
                     </div>
                </div>
            @endif
        </section>

        <section id="statistik" class="py-20 bg-purple-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold font-heading text-purple-800">Kampus dalam Angka</h2>
                    <p class="text-gray-600 mt-2">Data statistik terkini dari komunitas akademik kami.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-center max-w-4xl mx-auto">
                    <div class="bg-white p-8 rounded-lg shadow-lg transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-purple-600 mb-4">
                            <svg class="h-12 w-12 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </div>
                        <h3 class="text-5xl font-bold text-purple-800" data-counter-target="{{ $totalMahasiswa ?? 0 }}">0</h3>
                        <p class="text-gray-600 mt-2 text-xl font-semibold">Mahasiswa Aktif</p>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-lg transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-yellow-600 mb-4">
                            <svg class="h-12 w-12 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0l-2.072-1.037a3.75 3.75 0 01-1.08-5.320c.426-.85.99-1.643 1.66-2.385a3.75 3.75 0 015.321 1.08l2.072 1.037m0 0a48.29 48.29 0 005.372 0l2.072-1.037a3.75 3.75 0 015.321-1.08c.67.742 1.235 1.536 1.66 2.385a3.75 3.75 0 01-1.08 5.321l-2.072 1.037m0 0c-2.228.328-4.536.46-6.872.46s-4.644-.132-6.872-.46m13.745 0l-1.55-2.68a3.75 3.75 0 00-5.321-1.08l-1.55 2.68m0 0a49.331 49.331 0 00-2.343.834" /></svg>
                        </div>
                        <h3 class="text-5xl font-bold text-yellow-700" data-counter-target="{{ $totalDosen ?? 0 }}">0</h3>
                        <p class="text-gray-600 mt-2 text-xl font-semibold">Dosen Pengajar</p>
                    </div>
                </div>
            </div>
        </section>
        
        <section id="prodi" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold font-heading text-purple-800">Program Studi</h2>
                    <p class="text-gray-600 mt-2">Pilih program studi yang sesuai dengan panggilan pelayanan Anda.</p>
                </div>
                <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                    @forelse ($programStudi->take(2) as $prodi)
                        <div class="bg-white border border-purple-200 rounded-lg p-8 shadow-lg hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col text-center">
                            <h3 class="text-2xl font-bold text-purple-800">{{ $prodi->nama_prodi }}</h3>
                            <p class="text-gray-700 mt-4 flex-grow"><span class="font-semibold">{{ $prodi->deskripsi_singkat ?? 'Program studi ini dirancang untuk memperlengkapi mahasiswa dengan pengetahuan teologi dan keterampilan praktis.' }}</span></p>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 md:col-span-2">Informasi program studi akan segera tersedia.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="berita" class="py-20 bg-purple-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold font-heading text-purple-800">Berita & Pengumuman Terbaru</h2>
                    <p class="text-gray-600 mt-2">Dapatkan informasi terkini seputar aktivitas kampus.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse ($berita as $item)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col hover:shadow-xl transition-shadow duration-300 border border-gray-200">
                            <div class="p-6 flex-grow">
                                <p class="text-sm text-purple-500 font-semibold">{{ $item->created_at->isoFormat('D MMMM YYYY') }}</p>
                                <h3 class="text-xl font-bold text-purple-900 mt-2">{{ $item->judul }}</h3>
                                <p class="text-gray-700 mt-3 text-sm leading-relaxed">{{ Str::limit(strip_tags($item->konten), 100) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 md:col-span-3">Belum ada berita atau pengumuman terbaru.</p>
                    @endforelse
                </div>
                <div class="text-center mt-12">
                    <a href="{{ route('berita.index') }}" class="bg-yellow-500 hover:bg-yellow-600 text-purple-700 font-bold py-3 px-8 rounded-full transition duration-300">Lihat Semua Berita</a>
                </div>
            </div>
        </section>
        
        <section id="dokumen" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold font-heading text-purple-800">Unduh Dokumen</h2>
                    <p class="text-gray-600 mt-2">Dokumen dan formulir penting yang bisa Anda unduh.</p>
                </div>
                <div class="max-w-4xl mx-auto bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <ul class="space-y-4">
                        @forelse($dokumen as $doc)
                        <li class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-purple-800">{{ $doc->judul_dokumen }}</h4>
                            </div>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" download class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-full transition duration-300 text-sm">Unduh</a>
                        </li>
                        @empty
                        <li class="text-center text-gray-500 py-4">Belum ada dokumen yang tersedia.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <footer id="kontak" class="bg-purple-800 text-white">
        <div class="container mx-auto px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h3 class="font-bold text-lg text-yellow-400 mb-4">STT GPI Papua</h3>
                    <p class="text-gray-300 text-sm">Sekolah Tinggi Teologi Gereja Protestan Indonesia di Papua.</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-yellow-400 mb-4">Kontak Kami</h3>
                    <p class="text-gray-300 text-sm mb-1">Jl. Jenderal Sudirman, Fakfak, Papua Barat</p>
                    <p class="text-gray-300 text-sm mb-1">Email: info@sttgpipapua.ac.id</p>
                    <p class="text-gray-300 text-sm">Telp: (0956) 123-456</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-yellow-400 mb-4">Tautan Cepat</h3>
                    <ul class="text-gray-300 text-sm space-y-2">
                        <li><a href="{{ route('login') }}" class="hover:text-yellow-400">Login Mahasiswa</a></li>
                        <li><a href="#" class="hover:text-yellow-400">Kalender Akademik</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-yellow-400 mb-4">Lokasi Kampus</h3>
                    <iframe class="w-full h-32 rounded" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.849939340998!2d132.28794947474365!3d-2.927344096281343!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6862150a89494759%3A0x54c1576bab9f2127!2sFakfak!5e0!3m2!1sid!2sid!4v1708989788933!5m2!1sid!2sid0" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <div class="bg-purple-900 py-4 text-center text-sm text-gray-400">
            © {{ date('Y') }} STT GPI Papua. Hak Cipta Dilindungi.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi Slideshow jika ada
        if (document.querySelector('#hero-slideshow')) {
          new Splide('#hero-slideshow', {
            type       : 'fade',
            rewind     : true,
            perPage    : 1,
            autoplay   : true,
            interval   : 5000,
            pauseOnHover: false,
            arrows     : false,
            pagination : false,
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