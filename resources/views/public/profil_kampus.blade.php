<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil, Visi-Misi & Struktur Organisasi — {{ $cfg['singkatan_kampus'] }}</title>
    <link rel="icon" href="{{ \App\Models\Pengaturan::logoUrl() }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Figtree', 'sans-serif'], heading: ['Raleway', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        .glass-effect { background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        {{-- Banner Header --}}
        <section class="bg-pub-primary text-white py-14">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
                <span class="text-xs font-mono uppercase tracking-widest text-amber-300 block mb-2">Tentang Institusi & Tata Kelola</span>
                <h1 class="font-heading text-3xl md:text-4xl font-black uppercase">{{ $cfg['nama_kampus'] }}</h1>
                <p class="text-sm text-white/80 mt-2 max-w-3xl">{{ $cfg['motto_kampus'] }}</p>
            </div>
        </section>

        <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-12 space-y-16">
            {{-- 1. SEJARAH & SAMBUTAN PIMPINAN --}}
            <section id="sejarah" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-7 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-teal-600 block mb-2">Jati Diri & Sejarah</span>
                    <h2 class="font-heading text-2xl font-extrabold text-slate-900 mb-4 uppercase">Sejarah Singkat {{ $cfg['singkatan_kampus'] }}</h2>
                    <div class="text-sm text-slate-600 leading-relaxed whitespace-pre-line text-justify">{{ $cfg['sejarah_kampus'] }}</div>
                </div>

                <div id="sambutan" class="lg:col-span-5 bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-widest text-teal-600 block mb-2">{{ $cfg['judul_sambutan'] }}</span>
                        <p class="text-sm text-slate-600 leading-relaxed italic mb-6">"{{ $cfg['isi_sambutan'] }}"</p>
                    </div>
                    <div class="flex items-center gap-4 border-t pt-4">
                        @if(!empty($cfg['foto_pimpinan_path']))
                            <img src="{{ asset('storage/' . $cfg['foto_pimpinan_path']) }}" class="w-16 h-16 rounded-full object-cover border-2 border-teal-600">
                        @endif
                        <div>
                            <h4 class="font-bold text-sm text-slate-900 uppercase">{{ $cfg['nama_rektor'] }}</h4>
                            <span class="text-xs text-teal-600 font-semibold block">{{ $cfg['sebutan_pimpinan'] }} {{ $cfg['singkatan_kampus'] }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $cfg['label_id_rektor'] }}: {{ $cfg['nidn_rektor'] }}</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2. VISI & MISI --}}
            <section id="visi-misi" class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <div class="md:col-span-5 bg-pub-primary text-white p-8 rounded-2xl shadow-md">
                    <span class="text-xs font-mono uppercase tracking-widest text-amber-300 block mb-2">Arah Strategis</span>
                    <h3 class="font-heading text-2xl font-black uppercase mb-4">Visi Institusi</h3>
                    <p class="text-sm md:text-base leading-relaxed text-white/90 font-medium">
                        "{{ $cfg['visi_kampus'] }}"
                    </p>
                </div>
                <div class="md:col-span-7 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-teal-600 block mb-2">Langkah Penyelenggaraan Tridharma</span>
                    <h3 class="font-heading text-2xl font-black text-slate-900 uppercase mb-4">Misi Institusi</h3>
                    <ul class="space-y-3 text-sm text-slate-600">
                        @foreach(array_filter(explode("\n", $cfg['misi_kampus'] ?? '')) as $idx => $misi)
                            <li class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full bg-teal-100 text-teal-800 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">{{ $idx + 1 }}</span>
                                <span class="leading-relaxed">{{ trim($misi) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- 3. STRUKTUR ORGANISASI BADAN PENYELENGGARA (SINODE / YAYASAN) --}}
            <section id="struktur-yayasan" class="bg-white p-8 md:p-10 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-center max-w-3xl mx-auto mb-10">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-teal-600 block mb-2">{{ $cfg['jenis_badan_penyelenggara'] }}</span>
                    <h2 class="font-heading text-2xl md:text-3xl font-black text-slate-900 uppercase">{{ $cfg['nama_badan_penyelenggara'] }}</h2>
                    <p class="text-xs text-slate-500 mt-1">Badan Hukum Penyelenggara & Pembina Utama Perguruan Tinggi</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <div class="p-6 rounded-2xl bg-slate-50 border border-gray-200 text-center">
                        <span class="text-[10px] font-mono font-bold uppercase px-3 py-1 bg-amber-100 text-amber-800 rounded-full">Ketua Umum / Ketua Pembina</span>
                        <h4 class="font-heading font-extrabold text-base text-slate-900 mt-3 uppercase">{{ $cfg['ketua_yayasan'] ?: '-' }}</h4>
                    </div>
                    <div class="p-6 rounded-2xl bg-slate-50 border border-gray-200 text-center">
                        <span class="text-[10px] font-mono font-bold uppercase px-3 py-1 bg-teal-100 text-teal-800 rounded-full">Sekretaris Umum</span>
                        <h4 class="font-heading font-extrabold text-base text-slate-900 mt-3 uppercase">{{ $cfg['sekretaris_yayasan'] ?: '-' }}</h4>
                    </div>
                    <div class="p-6 rounded-2xl bg-slate-50 border border-gray-200 text-center">
                        <span class="text-[10px] font-mono font-bold uppercase px-3 py-1 bg-blue-100 text-blue-800 rounded-full">Bendahara / Pengawas</span>
                        <h4 class="font-heading font-extrabold text-base text-slate-900 mt-3 uppercase">{{ $cfg['bendahara_yayasan'] ?: '-' }}</h4>
                    </div>
                </div>
            </section>

            {{-- 4. STRUKTUR SENAT AKADEMIK, PIMPINAN EKSEKUTIF, LEMBAGA & KAPRODI --}}
            <section id="struktur-kampus" class="bg-white p-8 md:p-10 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-center max-w-3xl mx-auto mb-10">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-teal-600 block mb-2">Tata Kelola Perguruan Tinggi</span>
                    <h2 class="font-heading text-2xl md:text-3xl font-black text-slate-900 uppercase">Struktur Organisasi {{ $cfg['singkatan_kampus'] }}</h2>
                </div>

                {{-- Lapis Senat Akademik --}}
                <div class="max-w-3xl mx-auto mb-10">
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-center text-slate-400 mb-4">I. Senat Akademik Perguruan Tinggi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-5 rounded-xl bg-slate-900 text-white text-center">
                            <span class="text-[10px] font-mono uppercase text-amber-300 block">Ketua Senat Akademik</span>
                            <strong class="text-sm uppercase mt-1 block">{{ $cfg['ketua_senat'] ?: '-' }}</strong>
                        </div>
                        <div class="p-5 rounded-xl bg-slate-900 text-white text-center">
                            <span class="text-[10px] font-mono uppercase text-amber-300 block">Sekretaris Senat Akademik</span>
                            <strong class="text-sm uppercase mt-1 block">{{ $cfg['sekretaris_senat'] ?: '-' }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Lapis Pimpinan Eksekutif (Ketua/Rektor & Para Wakil) --}}
                <div class="max-w-5xl mx-auto mb-10">
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-center text-slate-400 mb-4">II. Pimpinan Eksekutif Institusi</h3>
                    <div class="max-w-md mx-auto mb-6 p-6 rounded-2xl bg-pub-primary text-white text-center shadow-lg">
                        <span class="text-[10px] font-mono uppercase tracking-widest text-amber-300 block">{{ $cfg['sebutan_pimpinan'] }} {{ $cfg['singkatan_kampus'] }}</span>
                        <h4 class="font-heading font-black text-lg uppercase mt-1">{{ $cfg['nama_rektor'] }}</h4>
                        <span class="text-[11px] font-mono text-white/75">{{ $cfg['label_id_rektor'] }}: {{ $cfg['nidn_rektor'] }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="p-5 rounded-xl bg-slate-50 border border-gray-200 text-center">
                            <span class="text-[10px] font-bold uppercase text-teal-700 block">{{ $cfg['wakil_pimpinan_1'] }}</span>
                            <strong class="text-sm text-slate-900 uppercase mt-1 block">{{ $cfg['nama_wakil_1'] ?: '-' }}</strong>
                        </div>
                        <div class="p-5 rounded-xl bg-slate-50 border border-gray-200 text-center">
                            <span class="text-[10px] font-bold uppercase text-teal-700 block">{{ $cfg['wakil_pimpinan_2'] }}</span>
                            <strong class="text-sm text-slate-900 uppercase mt-1 block">{{ $cfg['nama_wakil_2'] ?: '-' }}</strong>
                        </div>
                        <div class="p-5 rounded-xl bg-slate-50 border border-gray-200 text-center">
                            <span class="text-[10px] font-bold uppercase text-teal-700 block">{{ $cfg['wakil_pimpinan_3'] }}</span>
                            <strong class="text-sm text-slate-900 uppercase mt-1 block">{{ $cfg['nama_wakil_3'] ?: '-' }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Lapis Unit Pelaksana & Ketua Program Studi (Real-Time dari Database) --}}
                <div class="max-w-5xl mx-auto">
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-center text-slate-400 mb-4">III. Pimpinan Program Studi & Lembaga</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="p-4 rounded-xl border border-gray-200 text-center">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Kepala UPM (Penjaminan Mutu)</span>
                            <strong class="text-xs text-slate-800 uppercase mt-1 block">{{ $cfg['nama_kepala_upm'] ?: '-' }}</strong>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 text-center">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Kepala LPPM (Riset & PkM)</span>
                            <strong class="text-xs text-slate-800 uppercase mt-1 block">{{ $cfg['nama_kepala_lppm'] ?: '-' }}</strong>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 text-center">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Kepala BAAK</span>
                            <strong class="text-xs text-slate-800 uppercase mt-1 block">{{ $cfg['nama_kepala_baak'] ?: '-' }}</strong>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 text-center">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Kepala UPT Perpustakaan</span>
                            <strong class="text-xs text-slate-800 uppercase mt-1 block">{{ $cfg['nama_kepala_perpus'] ?: '-' }}</strong>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($programStudis as $prodi)
                            <div class="p-5 rounded-xl bg-teal-50/60 border border-teal-200 flex justify-between items-center">
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase text-teal-700 block">Ketua Program Studi {{ $prodi->nama_prodi }}</span>
                                    <strong class="text-sm text-slate-900 uppercase mt-0.5 block">{{ optional($prodi->kaprodi)->nama_lengkap ?? 'Belum Diplot' }}</strong>
                                    <span class="text-[11px] font-mono text-slate-500">NIDN: {{ optional($prodi->kaprodi)->nidn ?? '-' }}</span>
                                </div>
                                <span class="text-xs font-mono font-bold bg-white px-3 py-1 rounded-lg border text-teal-800">{{ $prodi->mahasiswas_count }} Mhs Aktif</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(!empty($cfg['bagan_struktur_path']))
                    <div class="mt-10 pt-8 border-t text-center">
                        <h4 class="text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-4">Bagan Struktur Organisasi Resmi</h4>
                        <img src="{{ asset('storage/' . $cfg['bagan_struktur_path']) }}" alt="Bagan Struktur Organisasi" class="max-w-full mx-auto rounded-xl border shadow-sm">
                    </div>
                @endif
            </section>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>