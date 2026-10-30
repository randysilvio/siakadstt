<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pengumuman->judul }} — {{ \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA') }}</title>
    <link rel="icon" href="{{ \App\Models\Pengaturan::logoUrl() }}" type="image/png">
    @include('partials._meta_sosial', [
        'judul' => $pengumuman->judul,
        'deskripsi' => $pengumuman->konten,
        'gambar' => \App\Support\GambarBagikan::untukPengumuman($pengumuman),
        'tautan' => route('pengumuman.public.show', $pengumuman),
        'jenis' => 'article',
        'terbit' => $pengumuman->created_at,
        'diubah' => $pengumuman->updated_at,
        'bagian' => $pengumuman->kategori,
    ])
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
                        {!! \App\Support\HtmlSanitizer::clean($pengumuman->konten) !!}
                    </div>
                </div>
            </article>

            {{-- Bagikan untuk promosi di media sosial --}}
            @php
                $tautanBagikan = \App\Support\GambarBagikan::absolut(route('pengumuman.public.show', $pengumuman));
                $teksBagikan = $pengumuman->judul . ' — ' . \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI Papua');
            @endphp
            <div class="mt-5 bg-white pub-radius border border-slate-200 p-5 flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 mr-2">Bagikan</span>
                <a href="https://wa.me/?text={{ rawurlencode($teksBagikan . "\n" . $tautanBagikan) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-[#25D366] hover:opacity-90 px-4 py-2 pub-radius"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($tautanBagikan) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-[#1877F2] hover:opacity-90 px-4 py-2 pub-radius"><i class="fab fa-facebook-f"></i> Facebook</a>
                <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($teksBagikan) }}&url={{ rawurlencode($tautanBagikan) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-slate-900 hover:opacity-90 px-4 py-2 pub-radius"><span class="font-black">𝕏</span> X / Twitter</a>
                <a href="https://t.me/share/url?url={{ rawurlencode($tautanBagikan) }}&text={{ rawurlencode($teksBagikan) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-[#229ED9] hover:opacity-90 px-4 py-2 pub-radius"><i class="fab fa-telegram-plane"></i> Telegram</a>
                <button type="button" id="salinTautan" data-tautan="{{ $tautanBagikan }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800 bg-white border border-slate-300 hover:border-slate-900 px-4 py-2 pub-radius"><i class="fas fa-link"></i> <span>Salin tautan</span></button>
                <button type="button" id="bagikanLain" class="hidden items-center gap-2 text-sm font-semibold text-slate-800 bg-white border border-slate-300 hover:border-slate-900 px-4 py-2 pub-radius"><i class="fas fa-share-alt"></i> Lainnya (Instagram, dll.)</button>
            </div>

            @if($beritaTerkait->count())
                <div class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-3">Berita lainnya</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($beritaTerkait as $b)
                            <a href="{{ route('pengumuman.public.show', $b) }}" class="flex gap-3 bg-white pub-radius border border-slate-200 p-3 hover:border-slate-900 transition">
                                @if($b->foto)
                                    <img src="{{ asset('storage/' . $b->foto) }}" alt="" class="w-20 h-20 object-cover pub-radius flex-shrink-0" loading="lazy">
                                @endif
                                <div class="min-w-0">
                                    <div class="text-[11px] font-mono text-slate-500">{{ $b->created_at?->translatedFormat('d M Y') }}</div>
                                    <div class="text-sm font-bold text-slate-900 leading-snug line-clamp-3">{{ $b->judul }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>
    <script>
        (function () {
            var salin = document.getElementById('salinTautan');
            salin.addEventListener('click', function () {
                var t = salin.dataset.tautan, label = salin.querySelector('span');
                var selesai = function () { label.textContent = 'Tersalin!'; setTimeout(function () { label.textContent = 'Salin tautan'; }, 1800); };
                if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(t).then(selesai); return; }
                var ta = document.createElement('textarea'); ta.value = t; document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); selesai(); } catch (e) {} document.body.removeChild(ta);
            });
            // Menu bagikan bawaan HP (Instagram, Line, dll.)
            var lain = document.getElementById('bagikanLain');
            if (navigator.share) {
                lain.classList.remove('hidden'); lain.classList.add('inline-flex');
                lain.addEventListener('click', function () {
                    navigator.share({ title: @json($pengumuman->judul), text: @json($teksBagikan), url: @json($tautanBagikan) }).catch(function () {});
                });
            }
        })();
    </script>

    @include('partials.public_footer')

</body>
</html>