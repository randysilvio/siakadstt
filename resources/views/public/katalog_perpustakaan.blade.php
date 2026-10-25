<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Perpustakaan (OPAC) — {{ $cfg['singkatan_kampus'] }}</title>
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
                    <span class="text-xs font-mono uppercase tracking-widest text-white/75 block mb-1">Online Public Access Catalog (OPAC)</span>
                    <h1 class="font-serif text-2xl md:text-4xl font-bold uppercase">Katalog Koleksi Perpustakaan</h1>
                </div>
                <div class="flex gap-3">
                    <div class="bg-white/10 border border-white/15 px-4 py-2.5 pub-radius text-center">
                        <span class="text-xl font-bold font-mono block">{{ number_format($totalJudul) }}</span>
                        <span class="text-[10px] uppercase text-white/80 font-mono">Judul Buku</span>
                    </div>
                    <div class="bg-white/10 border border-white/15 px-4 py-2.5 pub-radius text-center">
                        <span class="text-xl font-bold font-mono block">{{ number_format($totalEksemplar) }}</span>
                        <span class="text-[10px] uppercase text-white/80 font-mono">Eksemplar</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-10">
            <form action="{{ route('publik.perpustakaan') }}" method="GET" class="bg-white p-5 pub-radius shadow-2xs border border-slate-200 mb-8 grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-7">
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3.5 py-2 pub-radius border border-slate-300 text-xs font-medium" placeholder="Cari Judul Buku, Pengarang, Penerbit, ISBN, atau Nomor Rak...">
                </div>
                <div class="md:col-span-3">
                    <select name="ketersediaan" class="w-full px-3 py-2 pub-radius border border-slate-300 text-xs font-semibold uppercase">
                        <option value="">-- Semua Koleksi --</option>
                        <option value="tersedia" {{ request('ketersediaan') == 'tersedia' ? 'selected' : '' }}>Tersedia di Rak</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="bg-pub-primary text-white font-bold text-xs uppercase px-4 py-2 pub-radius flex-grow">Cari</button>
                    <a href="{{ route('publik.perpustakaan') }}" class="border border-slate-300 text-slate-700 font-bold text-xs uppercase px-3 py-2 pub-radius flex items-center justify-center">Reset</a>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($koleksis as $buku)
                    <div class="wm-card bg-white p-6 shadow-2xs border border-slate-200 border-t-4 border-t-pub-primary flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-100 text-[10px] font-mono">
                                <span class="text-slate-600 font-bold">RAK: {{ $buku->lokasi_rak ?? '-' }}</span>
                                <span class="px-2 py-0.5 rounded font-bold uppercase {{ $buku->jumlah_tersedia > 0 ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $buku->jumlah_tersedia > 0 ? 'Tersedia (' . $buku->jumlah_tersedia . '/' . $buku->jumlah_stok . ')' : 'Dipinjam' }}
                                </span>
                            </div>
                            <h3 class="font-serif font-bold text-base text-slate-900 mb-1 uppercase">{{ $buku->judul }}</h3>
                            <p class="text-xs text-slate-700 font-semibold mb-2">{{ $buku->pengarang }}</p>
                            <p class="text-[11px] text-slate-500">Penerbit: {{ $buku->penerbit ?? '-' }} ({{ $buku->tahun_terbit ?? '-' }})</p>
                        </div>
                        <div class="border-t border-slate-100 mt-4 pt-3 flex justify-between items-center text-[11px] font-mono text-slate-500">
                            <span>ISBN: {{ $buku->isbn ?? '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 bg-white pub-radius p-12 text-center text-slate-500 text-sm border border-slate-200">
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