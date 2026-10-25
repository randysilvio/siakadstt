<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dokumen Publik — {{ \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA') }}</title>
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
        .tab-active { background-color: var(--pub-primary); color: #ffffff; border-color: var(--pub-primary); }
        .tab-inactive { background-color: #ffffff; color: #334155; border-color: #cbd5e1; }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        <section class="wm-banner text-white py-12">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10">
                <span class="text-xs font-mono uppercase tracking-widest text-white/75 block mb-1">Arsip & Pedoman Resmi</span>
                <h1 class="font-serif text-2xl md:text-4xl font-bold uppercase">Dokumen Publik</h1>
            </div>
        </section>

        <section class="py-12 container mx-auto px-4 lg:px-8 max-w-5xl">
            @if($kategoris->isEmpty())
                <div class="text-center py-16 bg-white pub-radius border border-slate-200">
                    <h4 class="text-sm font-bold text-slate-700 uppercase">Belum Ada Dokumen Publik</h4>
                </div>
            @else
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($kategoris as $index => $kat)
                        <button onclick="openTab(event, 'tab-{{ Str::slug($kat) }}')" class="tab-btn px-5 py-2 pub-radius text-xs font-bold uppercase tracking-wider transition border {{ $index == 0 ? 'tab-active' : 'tab-inactive hover:bg-slate-100' }}">
                            {{ $kat }}
                        </button>
                    @endforeach
                </div>

                <div class="wm-card bg-white border border-slate-200 shadow-2xs overflow-hidden">
                    @foreach($kategoris as $index => $kat)
                        <div id="tab-{{ Str::slug($kat) }}" class="tab-content {{ $index == 0 ? 'block' : 'hidden' }}">
                            <div class="bg-slate-50 border-b border-slate-200 px-6 py-3.5 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800 uppercase text-xs tracking-wider">Kategori: {{ $kat }}</h3>
                                <span class="text-xs font-mono font-bold bg-white border border-slate-200 px-2.5 py-0.5 rounded text-slate-700">{{ $dokumenPerKategori[$kat]->count() }} Berkas</span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[600px]">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-[11px] font-mono uppercase text-slate-500 bg-white">
                                            <th class="px-6 py-3.5 font-bold">Nama Dokumen</th>
                                            <th class="px-6 py-3.5 font-bold text-center w-32">Tanggal</th>
                                            <th class="px-6 py-3.5 font-bold text-right w-36">Unduhan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-xs">
                                        @foreach($dokumenPerKategori[$kat] as $doc)
                                            <tr class="hover:bg-slate-50/80 transition">
                                                <td class="px-6 py-4">
                                                    <span class="font-bold text-slate-900 block">{{ $doc->judul_dokumen }}</span>
                                                    @if($doc->deskripsi)
                                                        <span class="text-slate-500 mt-0.5 block">{{ $doc->deskripsi }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-slate-500 text-center font-mono">
                                                    {{ $doc->created_at->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="inline-block px-4 py-1.5 bg-pub-primary text-white pub-radius text-xs font-bold uppercase tracking-wider">
                                                        Unduh
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
    
    @include('partials.public_footer')
    
    <script>
        function openTab(event, tabId) {
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('block');
            });
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('tab-active');
                el.classList.add('tab-inactive');
            });
            document.getElementById(tabId).classList.remove('hidden');
            document.getElementById(tabId).classList.add('block');
            event.currentTarget.classList.remove('tab-inactive');
            event.currentTarget.classList.add('tab-active');
        }
    </script>
</body>
</html>