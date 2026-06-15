<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pusat Unduhan & Arsip Digital - STT GPI Papua</title>

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#f0fdfa', 100: '#ccfbf1', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 900: '#134e4a' },
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
        .tab-active { background-color: #0f766e; color: white; border-color: #0f766e; }
        .tab-inactive { background-color: white; color: #475569; border-color: #e2e8f0; }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    {{-- NAVBAR HEADER --}}
    <header class="sticky top-0 z-50 transition-all duration-300 glass-effect border-b border-gray-100 shadow-sm">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center max-w-6xl">
            <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-11 w-11 transition-transform duration-500 group-hover:rotate-6">
                <div>
                    <span class="font-heading font-extrabold text-xl text-slate-900 tracking-tight block leading-none">STT GPI PAPUA</span>
                    <span class="text-[10px] text-brand-600 font-bold uppercase tracking-widest block mt-1">Sistem Informasi Akademik</span>
                </div>
            </a>
            <div class="hidden md:flex items-center space-x-8">
                 <a href="{{ url('/#berita') }}" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Berita</a>
                 <a href="{{ url('/#prodi') }}" class="text-sm font-semibold text-slate-600 hover:text-brand-600 transition">Program Studi</a>
                 <a href="{{ route('dokumen.publik') }}" class="text-sm font-bold text-brand-600 border-b-2 border-brand-600 pb-1">Dokumen</a>
                 <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 font-bold text-white rounded-full shadow-md bg-slate-900 hover:bg-brand-600 border border-slate-800 transition duration-300">
                    <span class="text-xs tracking-widest uppercase font-heading text-white">Portal Login</span>
                </a>
            </div>
        </nav>
    </header>

    <main class="flex-grow pt-10 pb-20">
        <div class="container mx-auto px-6 max-w-5xl">
            
            {{-- JUDUL HALAMAN --}}
            <div class="text-center mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-600 block mb-2"><i class="fa-solid fa-server mr-2"></i>Pusat Layanan Informasi Publik</span>
                <h1 class="text-4xl md:text-5xl font-heading font-extrabold text-slate-900 tracking-tight">Laci Arsip Digital</h1>
                <p class="text-slate-500 mt-4 max-w-2xl mx-auto text-sm md:text-base">Akses sentral untuk seluruh dokumen resmi, pedoman akademik, modul, dan formulir administratif STT GPI Papua.</p>
            </div>

            @if($kategoris->isEmpty())
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4 block"></i>
                    <p class="text-slate-500">Belum ada arsip dokumen yang tersedia.</p>
                </div>
            @else
                {{-- NAVIGASI TAB KATEGORI --}}
                <div class="flex flex-wrap justify-center gap-3 mb-8">
                    @foreach($kategoris as $index => $kat)
                        <button onclick="openTab(event, 'tab-{{ Str::slug($kat) }}')" class="tab-btn px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition duration-300 border shadow-sm {{ $index == 0 ? 'tab-active' : 'tab-inactive hover:bg-slate-50' }}">
                            <i class="fa-solid fa-folder mr-2"></i> {{ $kat }}
                        </button>
                    @endforeach
                </div>

                {{-- KONTEN TAB (Tabel Digital) --}}
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    @foreach($kategoris as $index => $kat)
                        <div id="tab-{{ Str::slug($kat) }}" class="tab-content {{ $index == 0 ? 'block' : 'hidden' }}">
                            
                            <div class="bg-slate-50 border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800 uppercase text-sm tracking-wider">Direktori: {{ $kat }}</h3>
                                <span class="text-xs font-bold bg-white border px-3 py-1 rounded-full text-slate-500">{{ $dokumenPerKategori[$kat]->count() }} Berkas</span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[600px]">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-xs uppercase tracking-widest text-slate-400 bg-white">
                                            <th class="px-6 py-4 font-bold">Informasi Berkas</th>
                                            <th class="px-6 py-4 font-bold text-center w-32">Tgl Rilis</th>
                                            <th class="px-6 py-4 font-bold text-right w-40">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach($dokumenPerKategori[$kat] as $doc)
                                            <tr class="hover:bg-slate-50 transition duration-150 group">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-start">
                                                        <i class="fa-solid fa-file-pdf text-rose-500 text-2xl mr-4 mt-0.5"></i>
                                                        <div>
                                                            <span class="font-bold text-slate-800 text-sm block group-hover:text-brand-600 transition">{{ $doc->judul_dokumen }}</span>
                                                            <span class="text-xs text-slate-400 mt-1 block">{{ $doc->deskripsi ?? 'Tidak ada deskripsi tambahan.' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-xs text-slate-500 text-center font-medium font-monospace">
                                                    {{ $doc->created_at->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    {{-- Tombol Pratinjau (Preview) --}}
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-slate-700 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 rounded-lg text-xs font-bold transition shadow-sm">
                                                        <i class="fa-regular fa-eye mr-2"></i> Buka
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
    
    {{-- FOOTER KAMPUS --}}
    <footer class="bg-slate-950 text-white pt-12 pb-6 border-t-2 border-brand-600">
        <div class="container mx-auto px-6 max-w-6xl text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-10 mx-auto mb-4 opacity-80">
            <p class="text-slate-400 text-xs font-light">
                &copy; {{ date('Y') }} STT GPI Papua. Seluruh Hak Cipta Dilindungi.<br>
                Sistem Informasi Akademik & Portal Publik.
            </p>
        </div>
    </footer>
    
    <script>
        // Logika Perpindahan Tab Laci Arsip
        function openTab(event, tabId) {
            // Sembunyikan semua isi tab
            let contents = document.querySelectorAll('.tab-content');
            contents.forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('block');
            });
            
            // Ubah gaya semua tombol menjadi tidak aktif
            let buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(el => {
                el.classList.remove('tab-active');
                el.classList.add('tab-inactive', 'hover:bg-slate-50');
            });
            
            // Tampilkan isi tab yang dipilih
            document.getElementById(tabId).classList.remove('hidden');
            document.getElementById(tabId).classList.add('block');
            
            // Ubah gaya tombol yang diklik menjadi aktif
            event.currentTarget.classList.remove('tab-inactive', 'hover:bg-slate-50');
            event.currentTarget.classList.add('tab-active');
        }
    </script>
</body>
</html>