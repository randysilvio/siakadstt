<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil, Visi-Misi & Struktur Organisasi — {{ $cfg['singkatan_kampus'] }}</title>
    @include('partials._meta_sosial', ['judul' => 'Profil, Visi-Misi & Struktur Organisasi — ' . $cfg['singkatan_kampus']])
    <link rel="icon" href="{{ \App\Models\Pengaturan::logoUrl() }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|merriweather:700,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Merriweather', 'serif'] }
                }
            }
        }
    </script>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        <section class="wm-banner text-white py-12">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10">
                <span class="text-xs font-mono uppercase tracking-widest text-white/75 block mb-1">Profil & Tata Kelola Institusi</span>
                <h1 class="font-serif text-2xl md:text-4xl font-bold uppercase">{{ $cfg['nama_kampus'] }}</h1>
                <p class="text-xs md:text-sm text-white/80 mt-2 max-w-3xl">{{ $cfg['motto_kampus'] }}</p>
            </div>
        </section>

        <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-12 space-y-12">
            {{-- 1. SEJARAH & SAMBUTAN PIMPINAN --}}
            <section id="sejarah" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-7 wm-card bg-white p-8 shadow-2xs border border-slate-200">
                    <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block mb-1">Jati Diri & Sejarah</span>
                    <h2 class="font-serif text-xl font-bold text-slate-900 mb-4 uppercase">Sejarah Singkat {{ $cfg['singkatan_kampus'] }}</h2>
                    <div class="text-sm text-slate-600 leading-relaxed whitespace-pre-line text-justify">{{ $cfg['sejarah_kampus'] }}</div>
                </div>

                <div id="sambutan" class="lg:col-span-5 wm-card bg-white p-8 shadow-2xs border border-slate-200 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block mb-2">{{ $cfg['judul_sambutan'] }}</span>
                        <p class="text-sm text-slate-600 leading-relaxed italic mb-6">"{{ $cfg['isi_sambutan'] }}"</p>
                    </div>
                    <div class="flex items-center gap-4 border-t border-slate-100 pt-4">
                        @if(!empty($cfg['foto_pimpinan_path']))
                            <img src="{{ asset('storage/' . $cfg['foto_pimpinan_path']) }}" class="w-14 h-16 rounded-lg object-cover border border-slate-300">
                        @endif
                        <div>
                            <h4 class="font-bold text-sm text-slate-900 uppercase">{{ $cfg['nama_rektor'] }}</h4>
                            <span class="text-xs text-slate-600 font-semibold block">{{ $cfg['sebutan_pimpinan'] }} {{ $cfg['singkatan_kampus'] }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $cfg['label_id_rektor'] }}: {{ $cfg['nidn_rektor'] }}</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2. VISI & MISI --}}
            <section id="visi-misi" class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <div class="md:col-span-5 wm-card wm-card-dark bg-pub-primary text-white p-8 shadow-sm">
                    <span class="text-xs font-mono uppercase tracking-widest text-white/70 block mb-2">Arah Strategis</span>
                    <h3 class="font-serif text-xl font-bold uppercase mb-4">Visi Institusi</h3>
                    <p class="text-sm md:text-base leading-relaxed text-white/95">
                        "{{ $cfg['visi_kampus'] }}"
                    </p>
                </div>
                <div class="md:col-span-7 wm-card bg-white p-8 shadow-2xs border border-slate-200">
                    <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block mb-2">Penyelenggaraan Tridharma</span>
                    <h3 class="font-serif text-xl font-bold text-slate-900 uppercase mb-4">Misi Institusi</h3>
                    <ul class="space-y-3 text-sm text-slate-600">
                        @foreach(array_filter(explode("\n", $cfg['misi_kampus'] ?? '')) as $idx => $misi)
                            <li class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded bg-pub-primary text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">{{ $idx + 1 }}</span>
                                <span class="leading-relaxed">{{ trim($misi) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- 3. STRUKTUR ORGANISASI BADAN PENYELENGGARA (SINODE / YAYASAN) --}}
            <section id="struktur-yayasan" class="bg-white p-8 md:p-10 pub-radius shadow-2xs border border-slate-200">
                <div class="text-center max-w-3xl mx-auto mb-8 pb-4 border-b border-slate-100">
                    <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block mb-1">{{ $cfg['jenis_badan_penyelenggara'] }}</span>
                    <h2 class="font-serif text-xl md:text-2xl font-bold text-slate-900 uppercase">{{ $cfg['nama_badan_penyelenggara'] }}</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-5xl mx-auto">
                    <div class="wm-card p-6 bg-slate-50 border border-slate-200 border-t-4 border-t-pub-primary text-center">
                        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Ketua Umum / Ketua Pembina</span>
                        <h4 class="font-bold text-sm text-slate-900 mt-2 uppercase">{{ $cfg['ketua_yayasan'] ?: '-' }}</h4>
                    </div>
                    <div class="wm-card p-6 bg-slate-50 border border-slate-200 border-t-4 border-t-pub-primary text-center">
                        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Sekretaris Umum</span>
                        <h4 class="font-bold text-sm text-slate-900 mt-2 uppercase">{{ $cfg['sekretaris_yayasan'] ?: '-' }}</h4>
                    </div>
                    <div class="wm-card p-6 bg-slate-50 border border-slate-200 border-t-4 border-t-pub-primary text-center">
                        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Bendahara / Pengawas</span>
                        <h4 class="font-bold text-sm text-slate-900 mt-2 uppercase">{{ $cfg['bendahara_yayasan'] ?: '-' }}</h4>
                    </div>
                </div>
            </section>

            {{-- 4. STRUKTUR SENAT AKADEMIK, PIMPINAN EKSEKUTIF, LEMBAGA & KAPRODI --}}
            <section id="struktur-kampus" class="bg-white p-8 md:p-10 pub-radius shadow-2xs border border-slate-200">
                <div class="text-center max-w-3xl mx-auto mb-8 pb-4 border-b border-slate-100">
                    <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block mb-1">Tata Kelola Perguruan Tinggi</span>
                    <h2 class="font-serif text-xl md:text-2xl font-bold text-slate-900 uppercase">Struktur Organisasi {{ $cfg['singkatan_kampus'] }}</h2>
                </div>

                {{-- I. Senat Akademik --}}
                <div class="max-w-3xl mx-auto mb-10">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-center text-slate-500 mb-4">I. Senat Akademik Perguruan Tinggi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="wm-card p-5 bg-slate-50 border border-slate-200 border-t-4 border-t-pub-primary text-center">
                            <span class="text-[10px] font-mono uppercase text-slate-500 block">Ketua Senat Akademik</span>
                            <strong class="text-sm text-slate-900 uppercase mt-1 block">{{ $cfg['ketua_senat'] ?: '-' }}</strong>
                        </div>
                        <div class="wm-card p-5 bg-slate-50 border border-slate-200 border-t-4 border-t-pub-primary text-center">
                            <span class="text-[10px] font-mono uppercase text-slate-500 block">Sekretaris Senat Akademik</span>
                            <strong class="text-sm text-slate-900 uppercase mt-1 block">{{ $cfg['sekretaris_senat'] ?: '-' }}</strong>
                        </div>
                    </div>
                </div>

                {{-- II. Pimpinan Eksekutif --}}
                <div class="max-w-5xl mx-auto mb-10">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-center text-slate-500 mb-4">II. Pimpinan Eksekutif Institusi</h3>
                    <div class="max-w-md mx-auto mb-6 wm-card wm-card-dark p-6 bg-pub-primary text-white text-center shadow-sm">
                        <span class="text-[10px] font-mono uppercase tracking-widest text-white/75 block">{{ $cfg['sebutan_pimpinan'] }} {{ $cfg['singkatan_kampus'] }}</span>
                        <h4 class="font-bold text-base uppercase mt-1">{{ $cfg['nama_rektor'] }}</h4>
                        <span class="text-[11px] font-mono text-white/75">{{ $cfg['label_id_rektor'] }}: {{ $cfg['nidn_rektor'] }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="wm-card p-5 bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">{{ $cfg['wakil_pimpinan_1'] }}</span>
                            <strong class="text-sm text-slate-900 uppercase mt-1 block">{{ $cfg['nama_wakil_1'] ?: '-' }}</strong>
                        </div>
                        <div class="wm-card p-5 bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">{{ $cfg['wakil_pimpinan_2'] }}</span>
                            <strong class="text-sm text-slate-900 uppercase mt-1 block">{{ $cfg['nama_wakil_2'] ?: '-' }}</strong>
                        </div>
                        <div class="wm-card p-5 bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">{{ $cfg['wakil_pimpinan_3'] }}</span>
                            <strong class="text-sm text-slate-900 uppercase mt-1 block">{{ $cfg['nama_wakil_3'] ?: '-' }}</strong>
                        </div>
                    </div>
                </div>

                {{-- III. Pimpinan Program Studi & Unit Pelaksana --}}
                <div class="max-w-5xl mx-auto">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-center text-slate-500 mb-4">III. Pimpinan Program Studi & Unit Penunjang</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="wm-card p-4 bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-mono uppercase text-slate-500 block">Kepala UPM</span>
                            <strong class="text-xs text-slate-900 uppercase mt-1 block">{{ $cfg['nama_kepala_upm'] ?: '-' }}</strong>
                        </div>
                        <div class="wm-card p-4 bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-mono uppercase text-slate-500 block">Kepala LPPM</span>
                            <strong class="text-xs text-slate-900 uppercase mt-1 block">{{ $cfg['nama_kepala_lppm'] ?: '-' }}</strong>
                        </div>
                        <div class="wm-card p-4 bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-mono uppercase text-slate-500 block">Kepala BAAK</span>
                            <strong class="text-xs text-slate-900 uppercase mt-1 block">{{ $cfg['nama_kepala_baak'] ?: '-' }}</strong>
                        </div>
                        <div class="wm-card p-4 bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-mono uppercase text-slate-500 block">Kepala Perpustakaan</span>
                            <strong class="text-xs text-slate-900 uppercase mt-1 block">{{ $cfg['nama_kepala_perpus'] ?: '-' }}</strong>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($programStudis as $prodi)
                            <div class="wm-card p-5 bg-slate-50 border border-slate-200 border-l-4 border-l-pub-primary flex justify-between items-center">
                                <div>
                                    <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Ketua Program Studi {{ $prodi->nama_prodi }}</span>
                                    <strong class="text-sm text-slate-900 uppercase mt-0.5 block">{{ optional($prodi->kaprodi)->nama_lengkap ?? '-' }}</strong>
                                    <span class="text-[11px] font-mono text-slate-500">NIDN: {{ optional($prodi->kaprodi)->nidn ?? '-' }}</span>
                                </div>
                                <span class="text-xs font-mono font-bold bg-white px-3 py-1 rounded border border-slate-200 text-slate-800">{{ $prodi->mahasiswas_count }} Mhs</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(!empty($cfg['bagan_struktur_path']))
                    <div class="mt-10 pt-8 border-t border-slate-200 text-center">
                        <h4 class="text-xs font-mono font-bold uppercase tracking-widest text-slate-500 mb-4">Bagan Struktur Organisasi Resmi</h4>
                        <img src="{{ asset('storage/' . $cfg['bagan_struktur_path']) }}" alt="Bagan Struktur Organisasi" class="max-w-full mx-auto pub-radius border border-slate-200 shadow-2xs">
                    </div>
                @endif
            </section>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>