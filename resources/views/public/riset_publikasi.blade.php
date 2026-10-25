<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riset, Publikasi Ilmiah & Pengabdian — {{ $cfg['singkatan_kampus'] }}</title>
    <link rel="icon" href="{{ \App\Models\Pengaturan::logoUrl() }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>.glass-effect { background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); }</style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        <section class="bg-pub-primary text-white py-12">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl flex flex-col md:flex-row justify-between items-md-center gap-4">
                <div>
                    <span class="text-xs font-mono uppercase tracking-widest text-amber-300 block mb-1">Repositori Tridharma Perguruan Tinggi</span>
                    <h1 class="font-heading text-2xl md:text-4xl font-black uppercase">Publikasi Ilmiah & Pengabdian Masyarakat</h1>
                </div>
                <div class="flex gap-3">
                    <div class="bg-white/10 px-4 py-2.5 rounded-xl text-center">
                        <span class="text-xl font-black block">{{ $totalPenelitian }}</span>
                        <span class="text-[10px] uppercase text-amber-300 font-bold">Publikasi Riset</span>
                    </div>
                    <div class="bg-white/10 px-4 py-2.5 rounded-xl text-center">
                        <span class="text-xl font-black block">{{ $totalPengabdian }}</span>
                        <span class="text-[10px] uppercase text-emerald-300 font-bold">Kegiatan PkM</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-10">
            {{-- Smart Filter --}}
            <form action="{{ route('publik.riset') }}" method="GET" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-8 grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold" placeholder="Cari judul artikel jurnal, buku, lokasi PkM, atau nama dosen...">
                </div>
                <div class="md:col-span-3">
                    <select name="unsur" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-bold uppercase">
                        <option value="">-- Semua Unsur Tridharma --</option>
                        <option value="penelitian" {{ request('unsur') == 'penelitian' ? 'selected' : '' }}>Penelitian & Publikasi Ilmiah</option>
                        <option value="pengabdian" {{ request('unsur') == 'pengabdian' ? 'selected' : '' }}>Pengabdian Masyarakat (PkM)</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <select name="tahun" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-mono font-bold">
                        <option value="">-- Semua Tahun --</option>
                        @foreach($tahunList as $th)
                            <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="bg-pub-primary text-white font-bold text-xs uppercase px-4 py-2.5 rounded-xl flex-grow">Filter</button>
                    <a href="{{ route('publik.riset') }}" class="bg-slate-100 text-slate-700 font-bold text-xs uppercase px-3 py-2.5 rounded-xl flex items-center justify-center"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @forelse($karyaTridharmas as $item)
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center gap-2 mb-2">
                                <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded {{ $item->unsur == 'penelitian' ? 'bg-teal-50 text-teal-700' : 'bg-amber-50 text-amber-800' }}">
                                    {{ strtoupper($item->kategori) }}
                                </span>
                                <span class="text-xs font-mono font-bold text-slate-400">{{ $item->tahun }} ({{ $item->semester }})</span>
                            </div>
                            <h3 class="font-heading font-bold text-base text-slate-900 mb-2">{{ $item->judul }}</h3>
                            <p class="text-xs text-slate-500 mb-3">
                                {{ $item->nama_jurnal_penerbit_lokasi ?? '-' }}
                                @if($item->nomor_identitas) | <span class="font-mono">{{ $item->nomor_identitas }}</span> @endif
                            </p>
                        </div>
                        <div class="border-t pt-3 flex justify-between items-center text-xs">
                            <span class="font-bold text-slate-700">
                                <i class="fa-solid fa-user-graduate text-teal-600 mr-1"></i> {{ $item->dosen->nama_lengkap ?? '-' }}
                            </span>
                            @if($item->tautan_eksternal)
                                <a href="{{ $item->tautan_eksternal }}" target="_blank" class="text-teal-600 font-extrabold uppercase text-[11px]">Buka Tautan &rarr;</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white rounded-2xl p-12 text-center text-slate-400 text-sm">
                        Belum ada data publikasi atau pengabdian masyarakat yang cocok dengan filter pencarian.
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $karyaTridharmas->links() }}</div>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>