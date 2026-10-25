<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pengumuman->judul }} — {{ \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA') }}</title>
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
    <style>
        .prose p { margin-bottom: 1.4em; line-height: 1.8; color: #334155; }
        .prose h1, .prose h2, .prose h3 { color: #0f172a; font-weight: 700; margin-top: 1.5em; margin-bottom: 0.75em; }
        .prose ul { list-style-type: disc; padding-left: 1.5em; margin-bottom: 1.4em; color: #334155; }
        .prose ol { list-style-type: decimal; padding-left: 1.5em; margin-bottom: 1.4em; color: #334155; }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow py-10 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="mb-5">
                <a href="{{ route('berita.index') }}" class="inline-flex items-center text-xs font-bold uppercase tracking-wider text-slate-700 bg-white border border-slate-200 py-2 px-4 pub-radius hover:border-slate-900 transition">
                    &larr; Kembali ke Daftar Berita
                </a>
            </div>

            <article class="bg-white pub-radius border border-slate-200 shadow-2xs overflow-hidden">
                <div class="p-8 md:p-10 pb-6 border-b border-slate-100">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-3 text-xs font-mono text-slate-500">
                        <span class="bg-slate-100 text-slate-800 font-bold px-3 py-1 rounded uppercase">
                            {{ $pengumuman->kategori ?? 'PENGUMUMAN' }}
                        </span>
                        <span>{{ $pengumuman->created_at->translatedFormat('d F Y') }}</span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-serif font-bold text-slate-900 leading-tight">
                        {{ $pengumuman->judul }}
                    </h1>
                </div>

                @if($pengumuman->foto)
                    <div class="w-full max-h-[460px] overflow-hidden bg-slate-100 border-b border-slate-100">
                        <img src="{{ asset('storage/' . $pengumuman->foto) }}" class="w-full h-full object-cover" alt="{{ $pengumuman->judul }}">
                    </div>
                @endif

                <div class="p-8 md:p-10">
                    <div class="prose max-w-none text-sm md:text-base">
                        {!! $pengumuman->konten !!}
                    </div>
                </div>
            </article>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>