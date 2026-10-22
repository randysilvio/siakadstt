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
        .glass-effect { background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .tab-active { background-color: #0f766e; color: white; border-color: #0f766e; }
        .tab-inactive { background-color: white; color: #475569; border-color: #e2e8f0; }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        {{-- ================= SPANDUK HALAMAN SERAGAM ================= --}}
        <section class="bg-slate-950 py-16 relative overflow-hidden border-b border-slate-800 text-center">
            <div class="absolute inset-0 bg-gradient-to-r from-brand-900/20 via-transparent to-brand-900/20"></div>
            <div class="container mx-auto px-6 relative z-10 max-w-3xl">
                <span class="text-xs font-bold text-brand-400 uppercase tracking-widest block mb-2">Pusat Layanan Informasi Publik</span>
                <h1 class="text-3xl md:text-5xl font-heading font-extrabold text-white tracking-tight mb-4">Laci Arsip Digital</h1>
                <p class="text-slate-400 text-sm md:text-base font-light">Akses sentral untuk seluruh dokumen resmi, pedoman akademik, modul perkuliahan, dan formulir administratif STT GPI Papua.</p>
            </div>
        </section>

        <section class="py-14 container mx-auto px-6 max-w-5xl">
            @if($kategoris->isEmpty())
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <i class="fa-regular fa-folder-open text-5xl text-slate-300 mb-4 block"></i>
                    <h4 class="text-base font-bold text-slate-700">Belum Ada Dokumen Publik</h4>
                    <p class="text-xs text-slate-500 mt-1">Saat ini belum ada arsip dokumen yang diunggah ke repositori publik.</p>
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

                {{-- KONTEN TAB --}}
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
                                                <td class="px-6 py-4 text-xs text-slate-500 text-center font-medium font-mono">
                                                    {{ $doc->created_at->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-slate-700 hover:bg-brand-600 hover:text-white hover:border-brand-600 rounded-lg text-xs font-bold transition shadow-sm">
                                                        <i class="fa-solid fa-download mr-2"></i> Buka / Unduh
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
        </section>
    </main>
    
    @include('partials.public_footer')
    
    <script>
        function openTab(event, tabId) {
            let contents = document.querySelectorAll('.tab-content');
            contents.forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('block');
            });
            
            let buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(el => {
                el.classList.remove('tab-active');
                el.classList.add('tab-inactive', 'hover:bg-slate-50');
            });
            
            document.getElementById(tabId).classList.remove('hidden');
            document.getElementById(tabId).classList.add('block');
            
            event.currentTarget.classList.remove('tab-inactive', 'hover:bg-slate-50');
            event.currentTarget.classList.add('tab-active');
        }
    </script>
</body>
</html>