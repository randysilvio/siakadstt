<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita & Pengumuman — {{ \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA') }}</title>
    @include('partials._meta_sosial', ['judul' => 'Berita & Pengumuman — ' . \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI Papua'), 'deskripsi' => 'Kabar terbaru, pengumuman akademik, dan kegiatan ' . \App\Models\Pengaturan::get('nama_kampus', 'STT GPI Papua') . '.', 'gambar' => \App\Support\GambarBagikan::situs('Berita & Pengumuman')])
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
                <span class="text-xs font-mono uppercase tracking-widest text-white/75 block mb-1">Informasi & Publikasi</span>
                <h1 class="font-serif text-2xl md:text-4xl font-bold uppercase">Berita & Pengumuman</h1>
            </div>
        </section>

        <section class="py-12 container mx-auto px-4 lg:px-8 max-w-7xl">
            @if($semuaBerita->isEmpty())
                <div class="text-center py-16 bg-white pub-radius border border-slate-200 max-w-xl mx-auto">
                    <h4 class="text-sm font-bold text-slate-700 uppercase">Belum Ada Berita Publik</h4>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($semuaBerita as $berita)
                        <article class="wm-card bg-white shadow-2xs hover:shadow-md transition border border-slate-200 overflow-hidden flex flex-col h-full">
                            <div class="h-48 overflow-hidden relative bg-slate-100 border-b border-slate-100">
                                <img src="{{ $berita->foto ? asset('storage/' . $berita->foto) : asset('images/latar-welcome.jpg') }}" class="w-full h-full object-cover" alt="{{ $berita->judul }}">
                                <span class="absolute top-3 left-3 bg-pub-primary text-white text-[10px] font-mono font-bold px-2.5 py-1 rounded uppercase">
                                    {{ $berita->kategori ?? 'UMUM' }}
                                </span>
                            </div>

                            <div class="p-6 flex-grow flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] text-slate-500 font-mono block mb-1.5">
                                        {{ \Carbon\Carbon::parse($berita->created_at)->translatedFormat('d F Y') }}
                                    </span>
                                    <h2 class="font-serif text-base font-bold text-slate-900 mb-2 leading-snug line-clamp-2">
                                        <a href="{{ route('pengumuman.public.show', $berita) }}" class="hover:underline">
                                            {{ $berita->judul }}
                                        </a>
                                    </h2>
                                    <p class="text-slate-600 text-xs leading-relaxed line-clamp-3 mb-4">
                                        {{ Str::limit(strip_tags($berita->konten), 120) }}
                                    </p>
                                </div>

                                <div class="pt-3 border-t border-slate-100 mt-auto">
                                    <a href="{{ route('pengumuman.public.show', $berita) }}" class="text-xs font-bold text-pub-primary hover:underline uppercase">
                                        Baca Selengkapnya &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            
                <div class="mt-10 flex justify-center">
                    {{ $semuaBerita->links('pagination::siakad-publik') }}
                </div>
            @endif
        </section>
    </main>

    @include('partials.public_footer')

</body>
</html>