<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Direktori Dosen — {{ \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA') }}</title>
    @include('partials._meta_sosial', ['judul' => 'Direktori Dosen — ' . \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI Papua')])
    <link rel="icon" href="{{ \App\Models\Pengaturan::logoUrl() }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|merriweather:700,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Merriweather', 'serif'] } } }
        }
    </script>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        <section class="wm-banner text-white py-12">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10">
                <span class="text-xs font-mono uppercase tracking-widest text-white/75 block mb-1">Sumber Daya Manusia</span>
                <h1 class="font-serif text-2xl md:text-4xl font-bold uppercase">Direktori Dosen & Peneliti</h1>
            </div>
        </section>

        <section class="py-10 container mx-auto px-4 lg:px-8 max-w-7xl">
            <form action="{{ route('dosen.public.index') }}" method="GET" class="bg-white p-5 pub-radius shadow-2xs border border-slate-200 mb-8 grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-6">
                    <input type="text" name="search" class="w-full px-3.5 py-2 pub-radius border border-slate-300 text-xs font-medium" placeholder="Cari nama dosen..." value="{{ request('search') }}">
                </div>
                <div class="md:col-span-4">
                    <select name="program_studi_id" class="w-full px-3 py-2 pub-radius border border-slate-300 text-xs font-semibold uppercase">
                        <option value="">-- Semua Program Studi --</option>
                        @foreach($program_studis as $prodi)
                            <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="bg-pub-primary text-white font-bold text-xs uppercase px-4 py-2 pub-radius flex-grow">Cari</button>
                    <a href="{{ route('dosen.public.index') }}" class="border border-slate-300 text-slate-700 font-bold text-xs uppercase px-3 py-2 pub-radius flex items-center justify-center">Reset</a>
                </div>
            </form>

            @if($dosens->isEmpty())
                <div class="text-center py-16 bg-white pub-radius border border-slate-200">
                    <h4 class="text-sm font-bold text-slate-700 uppercase">Data Dosen Tidak Ditemukan</h4>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($dosens as $dosen)
                        <div class="hover-lift pub-radius bg-white shadow-2xs border border-slate-200 border-t-4 border-t-pub-primary p-6 flex flex-col items-center text-center">
                            <div class="w-24 h-24 mx-auto mb-4">
                                @if($dosen->foto_profil)
                                    <img src="{{ asset('storage/' . $dosen->foto_profil) }}" class="w-full h-full rounded-full object-cover border border-slate-200" alt="{{ $dosen->nama_lengkap }}">
                                @else
                                    <div class="w-full h-full rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-2xl font-bold text-slate-500">
                                        {{ substr($dosen->nama_lengkap, 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <h3 class="text-sm font-bold text-slate-900 mb-1 leading-snug line-clamp-2">
                                {{ $dosen->nama_lengkap }}
                            </h3>
                            <span class="text-xs text-slate-600 font-medium block mb-1">{{ $dosen->jabatan_akademik ?? 'Dosen Pengajar' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono block mb-4 uppercase">{{ $dosen->programStudi->nama_prodi ?? '-' }}</span>

                            <div class="mt-auto w-full pt-3 border-t border-slate-100">
                                @if($dosen->nidn)
                                    <a href="{{ route('dosen.public.show', $dosen->nidn) }}" class="block w-full py-2 bg-slate-100 hover-bg-pub-primary text-slate-800 pub-radius text-xs font-bold uppercase tracking-wider transition">
                                        Profil & Publikasi
                                    </a>
                                @else
                                    <span class="block w-full py-2 bg-slate-50 text-slate-400 pub-radius text-xs font-semibold">
                                        Dosen Pengajar
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 flex justify-center">
                    {{ $dosens->links('pagination::siakad-publik') }}
                </div>
            @endif
        </section>
    </main>

    @include('partials.public_footer')

</body>
</html>