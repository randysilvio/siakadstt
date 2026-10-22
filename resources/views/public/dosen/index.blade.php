<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Direktori Dosen - STT GPI Papua</title>

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
        select { -webkit-appearance: none; -moz-appearance: none; appearance: none; }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        {{-- ================= HERO DIREKTORI ================= --}}
        <section class="bg-slate-950 py-16 relative text-center border-b border-slate-800 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-brand-900/10 via-transparent to-transparent"></div>
            <div class="container mx-auto px-6 relative z-10 max-w-2xl">
                <span class="text-xs font-bold text-brand-400 uppercase tracking-widest block mb-2">Tenaga Pendidik Ahli</span>
                <h1 class="text-3xl md:text-5xl font-heading font-extrabold text-white tracking-tight mb-3">Direktori Dosen</h1>
                <p class="text-slate-400 text-xs md:text-sm font-light">Mengenal lebih dekat profil akademis, keahlian riset, dan publikasi para dosen tetap STT GPI Papua.</p>
            </div>
        </section>

        {{-- ================= AREA PENCARIAN & KARTU ================= --}}
        <section class="py-12 container mx-auto px-6 max-w-6xl -mt-8 relative z-20">
            
            <div class="max-w-3xl mx-auto mb-12">
                <div class="bg-white p-1.5 md:p-2 rounded-3xl md:rounded-full shadow-lg border border-gray-100">
                    <form action="{{ route('dosen.public.index') }}" method="GET" class="flex flex-col md:flex-row items-center w-full">
                        
                        <div class="flex items-center w-full md:w-1/2 md:border-r border-gray-200 py-1">
                            <span class="pl-4 md:pl-5 text-gray-400"><i class="fa-solid fa-search text-sm"></i></span>
                            <input type="text" name="search" class="w-full px-3 py-2 text-xs md:text-sm outline-none text-slate-700 placeholder-gray-400 font-medium bg-transparent" placeholder="Pencarian nama dosen..." value="{{ request('search') }}">
                        </div>
                        
                        <div class="flex items-center w-full md:w-1/3 py-1 border-t md:border-t-0 border-gray-100">
                            <span class="pl-4 text-gray-400"><i class="fa-solid fa-graduation-cap text-sm"></i></span>
                            <select name="program_studi_id" class="w-full px-3 py-2 text-xs md:text-sm outline-none text-slate-700 font-medium bg-transparent cursor-pointer">
                                <option value="">Semua Program Studi</option>
                                @foreach($program_studis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-auto md:ml-auto mt-2 md:mt-0 p-1 md:p-0">
                            <button type="submit" class="w-full md:w-auto bg-brand-600 hover:bg-brand-700 text-white px-8 py-3 md:py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition shadow-sm">
                                Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($dosens->isEmpty())
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 max-w-md mx-auto shadow-sm">
                    <i class="fa-solid fa-user-slash text-4xl text-slate-300 block mb-3"></i>
                    <h4 class="text-sm font-bold text-slate-700">Dosen Tidak Ditemukan</h4>
                    <p class="text-xs text-slate-400 mt-1">Pencarian untuk kriteria yang dimasukkan tidak memiliki padanan master data.</p>
                    <a href="{{ route('dosen.public.index') }}" class="inline-block mt-4 text-xs font-bold text-brand-600 hover:underline">Reset Pencarian</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($dosens as $dosen)
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 p-6 flex flex-col items-center text-center group relative overflow-hidden">
                            
                            <div class="relative w-28 h-28 mx-auto mb-4">
                                <div class="absolute inset-0 bg-brand-100 rounded-full animate-pulse opacity-75 group-hover:hidden"></div>
                                @if($dosen->foto_profil)
                                    <img src="{{ asset('storage/' . $dosen->foto_profil) }}" class="w-full h-full rounded-full object-cover border-4 border-white shadow-sm relative z-10 group-hover:scale-105 transition duration-500" alt="{{ $dosen->nama_lengkap }}">
                                @else
                                    <div class="w-full h-full rounded-full border-4 border-white shadow-sm relative z-10 group-hover:scale-105 transition duration-500 bg-slate-200 flex items-center justify-center text-3xl font-extrabold text-slate-400">
                                        {{ substr($dosen->nama_lengkap, 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-slate-900 mb-1 group-hover:text-brand-600 transition leading-snug truncate w-full" title="{{ $dosen->nama_lengkap }}">
                                {{ $dosen->nama_lengkap }}
                            </h3>
                            <span class="text-xs text-brand-600 font-semibold block mb-1">{{ $dosen->jabatan_akademik ?? 'Dosen Pengajar' }}</span>
                            <span class="text-[10px] text-slate-400 font-medium block mb-3 uppercase tracking-wider">{{ $dosen->programStudi->nama_prodi ?? 'STT GPI Papua' }}</span>
                            
                            <div class="w-8 h-0.5 bg-gray-100 rounded-full mb-4 group-hover:bg-brand-300 transition"></div>

                            <div class="mt-auto w-full pt-2">
                                @if($dosen->nidn)
                                    <a href="{{ route('dosen.public.show', $dosen->nidn) }}" class="block w-full py-2 bg-slate-50 hover:bg-brand-600 text-slate-700 hover:text-white rounded-xl text-xs font-bold tracking-wide transition duration-200">
                                        Lihat Profil Lengkap
                                    </a>
                                @else
                                    <span class="block w-full py-2 bg-gray-50 text-slate-400 rounded-xl text-xs font-semibold">
                                        Profil Terdaftar
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 flex justify-center">
                    {{ $dosens->links('pagination::tailwind') }} 
                </div>
            @endif
        </section>
    </main>

    @include('partials.public_footer')

</body>
</html>