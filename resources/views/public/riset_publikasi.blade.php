<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penelitian & Pengabdian Masyarakat — {{ $cfg['singkatan_kampus'] }}</title>
    @include('partials._meta_sosial', ['judul' => 'Penelitian & Pengabdian Masyarakat — ' . $cfg['singkatan_kampus']])
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
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl flex flex-col md:flex-row justify-between items-md-center gap-4 relative z-10">
                <div>
                    <span class="text-xs font-mono uppercase tracking-widest text-white/75 block mb-1">Tridharma Perguruan Tinggi</span>
                    <h1 class="font-serif text-2xl md:text-4xl font-bold uppercase">Penelitian & Pengabdian Masyarakat</h1>
                </div>
                <div class="flex gap-3">
                    <div class="bg-white/10 border border-white/15 px-4 py-2.5 pub-radius text-center">
                        <span class="text-xl font-bold font-mono block">{{ $totalPenelitian }}</span>
                        <span class="text-[10px] uppercase text-white/80 font-mono">Penelitian</span>
                    </div>
                    <div class="bg-white/10 border border-white/15 px-4 py-2.5 pub-radius text-center">
                        <span class="text-xl font-bold font-mono block">{{ $totalPengabdian }}</span>
                        <span class="text-[10px] uppercase text-white/80 font-mono">Pengabdian</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-10">
            <form action="{{ route('publik.riset') }}" method="GET" class="bg-white p-5 pub-radius shadow-2xs border border-slate-200 mb-8 grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3.5 py-2 pub-radius border border-slate-300 text-xs font-medium" placeholder="Cari judul publikasi, jurnal, lokasi PkM, atau nama dosen...">
                </div>
                <div class="md:col-span-3">
                    <select name="unsur" class="w-full px-3 py-2 pub-radius border border-slate-300 text-xs font-semibold uppercase">
                        <option value="">-- Semua Bidang --</option>
                        <option value="penelitian" {{ request('unsur') == 'penelitian' ? 'selected' : '' }}>Penelitian & Publikasi</option>
                        <option value="pengabdian" {{ request('unsur') == 'pengabdian' ? 'selected' : '' }}>Pengabdian Masyarakat</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <select name="tahun" class="w-full px-3 py-2 pub-radius border border-slate-300 text-xs font-mono font-bold">
                        <option value="">-- Semua Tahun --</option>
                        @foreach($tahunList as $th)
                            <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="bg-pub-primary text-white font-bold text-xs uppercase px-4 py-2 pub-radius flex-grow">Filter</button>
                    <a href="{{ route('publik.riset') }}" class="border border-slate-300 text-slate-700 font-bold text-xs uppercase px-3 py-2 pub-radius flex items-center justify-center">Reset</a>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @forelse($karyaTridharmas as $item)
                    <div class="wm-card bg-white p-6 shadow-2xs border border-slate-200 border-t-4 border-t-pub-primary flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center gap-2 mb-2 text-[11px] font-mono text-slate-500">
                                <span class="uppercase font-bold text-slate-800">{{ $item->kategori }}</span>
                                <span>{{ $item->tahun }} ({{ $item->semester }})</span>
                            </div>
                            <h3 class="font-serif font-bold text-base text-slate-900 mb-2">{{ $item->judul }}</h3>
                            <p class="text-xs text-slate-600 mb-3">
                                {{ $item->nama_jurnal_penerbit_lokasi ?? '-' }}
                                @if($item->nomor_identitas) | <span class="font-mono">{{ $item->nomor_identitas }}</span> @endif
                            </p>
                        </div>
                        <div class="border-t border-slate-100 pt-3 flex justify-between items-center text-xs">
                            <span class="font-bold text-slate-800">{{ $item->dosen->nama_lengkap ?? '-' }}</span>
                            @if($item->tautan_eksternal)
                                <a href="{{ $item->tautan_eksternal }}" target="_blank" class="text-pub-primary font-bold uppercase text-[11px] hover:underline">Tautan Publikasi &rarr;</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white pub-radius p-12 text-center text-slate-500 text-sm border border-slate-200">
                        Belum ada data publikasi atau pengabdian masyarakat.
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $karyaTridharmas->links('pagination::siakad-publik') }}</div>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>