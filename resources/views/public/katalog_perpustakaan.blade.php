<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Perpustakaan Digital (OPAC) — {{ $cfg['singkatan_kampus'] }}</title>
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
                    <span class="text-xs font-mono uppercase tracking-widest text-amber-300 block mb-1">Online Public Access Catalog (OPAC)</span>
                    <h1 class="font-heading text-2xl md:text-4xl font-black uppercase">Katalog Koleksi Perpustakaan</h1>
                </div>
                <div class="flex gap-3">
                    <div class="bg-white/10 px-4 py-2.5 rounded-xl text-center">
                        <span class="text-xl font-black block">{{ number_format($totalJudul) }}</span>
                        <span class="text-[10px] uppercase text-amber-300 font-bold">Judul Buku</span>
                    </div>
                    <div class="bg-white/10 px-4 py-2.5 rounded-xl text-center">
                        <span class="text-xl font-black block">{{ number_format($totalEksemplar) }}</span>
                        <span class="text-[10px] uppercase text-emerald-300 font-bold">Total Eksemplar</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-10">
            <form action="{{ route('publik.perpustakaan') }}" method="GET" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-8 grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-7">
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold" placeholder="Ketik Judul Buku, Pengarang, Penerbit, ISBN, atau Lokasi Rak...">
                </div>
                <div class="md:col-span-3">
                    <select name="ketersediaan" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-bold uppercase">
                        <option value="">-- Semua Koleksi --</option>
                        <option value="tersedia" {{ request('ketersediaan') == 'tersedia' ? 'selected' : '' }}>Tersedia di Rak Saat Ini</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="bg-pub-primary text-white font-bold text-xs uppercase px-4 py-2.5 rounded-xl flex-grow">Cari Buku</button>
                    <a href="{{ route('publik.perpustakaan') }}" class="bg-slate-100 text-slate-700 font-bold text-xs uppercase px-3 py-2.5 rounded-xl flex items-center justify-center"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($koleksis as $buku)
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-[10px] font-mono font-bold px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded">
                                    RAK: {{ $buku->lokasi_rak ?? '-' }}
                                </span>
                                @if($buku->jumlah_tersedia > 0)
                                    <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded">
                                        Tersedia ({{ $buku->jumlah_tersedia }}/{{ $buku->jumlah_stok }})
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 bg-rose-100 text-rose-800 rounded">
                                        Sedang Dipinjam
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-heading font-extrabold text-base text-slate-900 mb-1 uppercase">{{ $buku->judul }}</h3>
                            <p class="text-xs text-teal-700 font-semibold mb-2">{{ $buku->pengarang }}</p>
                            <p class="text-[11px] text-slate-500">Penerbit: {{ $buku->penerbit ?? '-' }} ({{ $buku->tahun_terbit ?? '-' }})</p>
                        </div>
                        <div class="border-t mt-4 pt-3 flex justify-between items-center text-[11px] font-mono text-slate-400">
                            <span>ISBN: {{ $buku->isbn ?? '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 bg-white rounded-2xl p-12 text-center text-slate-400 text-sm">
                        Koleksi buku yang dicari belum ditemukan.
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $koleksis->links() }}</div>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>