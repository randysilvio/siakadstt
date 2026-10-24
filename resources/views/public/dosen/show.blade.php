<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dosen->nama_lengkap }} - Profil Tenaga Pendidik & Riset</title>

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
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        <div class="h-48 w-full bg-gradient-to-r from-slate-900 via-brand-900 to-slate-950 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/30"></div>
        </div>

        <div class="container mx-auto px-4 pb-16 relative z-10 -mt-20 max-w-5xl">
            
            <div class="mb-5">
                <a href="{{ route('dosen.public.index') }}" class="inline-flex items-center text-xs font-bold text-white bg-slate-900/40 hover:bg-slate-900/70 px-4 py-2 rounded-full transition backdrop-blur-md">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Direktori Dosen
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 flex flex-col md:flex-row">
                
                {{-- Panel Identitas Kiri --}}
                <div class="w-full md:w-1/3 bg-slate-50 p-8 text-center border-b md:border-b-0 md:border-r border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="relative w-40 h-40 mx-auto mb-5">
                            @if($dosen->foto_profil)
                                <img src="{{ asset('storage/' . $dosen->foto_profil) }}" class="w-full h-full rounded-full object-cover border-4 border-white shadow-md" alt="{{ $dosen->nama_lengkap }}">
                            @else
                                <div class="w-full h-full rounded-full border-4 border-white shadow-md bg-slate-200 flex items-center justify-center text-5xl font-extrabold text-slate-400">
                                    {{ substr($dosen->nama_lengkap, 0, 1) }}
                                </div>
                            @endif
                            <span class="absolute bottom-2 right-2 bg-emerald-500 w-4 h-4 rounded-full border-2 border-white" title="Pengajar Aktif"></span>
                        </div>

                        <h1 class="text-lg font-bold text-slate-900 leading-tight mb-1">{{ $dosen->nama_lengkap }}</h1>
                        <span class="text-xs text-brand-600 font-bold block mb-1">{{ $dosen->jabatan_akademik ?? 'Dosen Pengajar' }}</span>
                        <span class="text-[10px] text-slate-500 font-medium block mb-6 uppercase tracking-wider">{{ $dosen->programStudi->nama_prodi ?? 'STT GPI Papua' }}</span>
                        
                        <div class="bg-white border border-gray-200 rounded-xl p-3 mb-6 text-center shadow-2xs">
                            <span class="text-[10px] text-slate-400 font-medium block uppercase tracking-wider">NIDN Terdaftar</span>
                            <span class="text-xs font-mono font-bold text-slate-700">{{ $dosen->nidn ?? 'BELUM DIVERIFIKASI' }}</span>
                        </div>

                        @if($dosen->email_institusi)
                            <a href="mailto:{{ $dosen->email_institusi }}" class="inline-flex items-center justify-center gap-2 w-full bg-slate-900 hover:bg-brand-600 text-white py-2.5 rounded-xl transition text-xs font-bold shadow-sm mb-4">
                                <i class="fa-regular fa-envelope text-sm"></i> Kirim Surel Resmi
                            </a>
                        @endif
                    </div>

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

                {{-- Kanvas Informasi Kanan (Portofolio Tridharma) --}}
                <div class="w-full md:w-2/3 p-8 md:p-10 flex flex-col justify-between">
                    <div>
                        <div class="mb-8">
                            <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest block mb-2">Profil Pengajar</span>
                            <p class="text-slate-600 text-xs md:text-sm leading-relaxed text-justify font-light whitespace-pre-wrap">{{ $dosen->deskripsi_diri ?? 'Informasi portofolio akademik dan deskripsi spesialisasi riset tenaga pendidik ini sedang dalam proses penyusunan.' }}</p>
                        </div>

                        @if($dosen->bidang_keahlian)
                            <div class="mb-8 border-t border-gray-100 pt-6">
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

                        {{-- Penelitian & Publikasi Ilmiah --}}
                        @if($dosen->penelitians && $dosen->penelitians->isNotEmpty())
                            <div class="mb-8 border-t border-gray-100 pt-6">
                                <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest block mb-3">
                                    <i class="fa-solid fa-book-open mr-1"></i> Publikasi Ilmiah & Penelitian Terpilih
                                </span>
                                <div class="space-y-3">
                                    @foreach($dosen->penelitians as $pub)
                                        <div class="p-3.5 rounded-xl bg-slate-50 border border-gray-100 hover:border-brand-200 transition">
                                            <div class="flex flex-wrap justify-between items-center gap-2 mb-1">
                                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 bg-brand-100 text-brand-700 rounded">{{ $pub->kategori }}</span>
                                                <span class="text-[11px] font-mono font-bold text-slate-400">{{ $pub->tahun }}</span>
                                            </div>
                                            <h4 class="text-xs md:text-sm font-bold text-slate-800 leading-snug">{{ $pub->judul }}</h4>
                                            <p class="text-[11px] text-slate-500 mt-1">
                                                {{ $pub->nama_jurnal_penerbit_lokasi ?? 'STT GPI Papua' }}
                                                @if($pub->nomor_identitas) | <span class="font-mono">{{ $pub->nomor_identitas }}</span> @endif
                                            </p>
                                            @if($pub->tautan_eksternal)
                                                <a href="{{ $pub->tautan_eksternal }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-[11px] font-bold text-brand-600 hover:text-brand-800 mt-1.5">
                                                    Buka Publikasi <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[9px]"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Pengabdian kepada Masyarakat (PkM) --}}
                        @if($dosen->pengabdians && $dosen->pengabdians->isNotEmpty())
                            <div class="mb-8 border-t border-gray-100 pt-6">
                                <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest block mb-3">
                                    <i class="fa-solid fa-hands-holding-circle mr-1"></i> Pengabdian Masyarakat & Pelayanan Gerejawi
                                </span>
                                <ul class="space-y-2">
                                    @foreach($dosen->pengabdians as $pkm)
                                        <li class="p-3 rounded-xl bg-slate-50 border border-gray-100 text-xs flex justify-between items-center gap-3">
                                            <div>
                                                <span class="font-bold text-slate-800 block">{{ $pkm->judul }}</span>
                                                <span class="text-[11px] text-slate-500">{{ $pkm->nama_jurnal_penerbit_lokasi ?? $pkm->kategori }} ({{ $pkm->peran }})</span>
                                            </div>
                                            <span class="text-[11px] font-mono font-bold text-brand-600 bg-white px-2.5 py-1 rounded border border-gray-200">{{ $pkm->tahun }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($dosen->mataKuliahs->isNotEmpty())
                            <div class="border-t border-gray-100 pt-6">
                                <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest block mb-3">Mata Kuliah Diampu</span>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach($dosen->mataKuliahs->unique('nama_mk') as $matkul)
                                        <li class="flex items-center text-slate-700 bg-slate-50 px-3 py-2 rounded-xl border border-gray-100 text-xs font-medium">
                                            <i class="fa-solid fa-check text-brand-500 mr-2 text-[10px]"></i>
                                            <span class="truncate" title="{{ $matkul->nama_mk }}">{{ $matkul->nama_mk }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="pt-6 mt-8 border-t border-gray-100 text-center md:text-left text-[11px] text-slate-400 font-light">
                        Sinkronisasi Basis Data Portofolio Tridharma SIAKAD STT GPI Papua.
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>