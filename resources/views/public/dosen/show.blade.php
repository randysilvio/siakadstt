<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $dosen->nama_lengkap }} — Profil Dosen</title>
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

    <main class="flex-grow py-10">
        <div class="container mx-auto px-4 lg:px-8 max-w-5xl">
            <div class="mb-5">
                <a href="{{ route('dosen.public.index') }}" class="inline-flex items-center text-xs font-bold text-slate-700 bg-white border border-slate-200 px-4 py-2 pub-radius hover:border-slate-900 transition">
                    &larr; Kembali ke Direktori Dosen
                </a>
            </div>

            <div class="bg-white pub-radius shadow-2xs overflow-hidden border border-slate-200 flex flex-col md:flex-row">
                <div class="w-full md:w-1/3 bg-slate-50 p-8 text-center border-b md:border-b-0 md:border-r border-slate-200 flex flex-col justify-between">
                    <div>
                        <div class="w-36 h-36 mx-auto mb-5">
                            @if($dosen->foto_profil)
                                <img src="{{ asset('storage/' . $dosen->foto_profil) }}" class="w-full h-full rounded-full object-cover border-2 border-white shadow-sm" alt="{{ $dosen->nama_lengkap }}">
                            @else
                                <div class="w-full h-full rounded-full bg-slate-200 flex items-center justify-center text-4xl font-bold text-slate-500">
                                    {{ substr($dosen->nama_lengkap, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <h1 class="text-base font-bold text-slate-900 leading-snug mb-1">{{ $dosen->nama_lengkap }}</h1>
                        <span class="text-xs text-slate-600 font-semibold block mb-1">{{ $dosen->jabatan_akademik ?? 'Dosen Pengajar' }}</span>
                        <span class="text-[11px] text-slate-500 font-mono block mb-5 uppercase">{{ $dosen->programStudi->nama_prodi ?? '-' }}</span>
                        
                        <div class="bg-white border border-slate-200 pub-radius p-3 mb-5 text-center">
                            <span class="text-[10px] text-slate-400 font-mono block uppercase">NIDN / NUPTK</span>
                            <span class="text-xs font-mono font-bold text-slate-800">{{ $dosen->nidn ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        @if($dosen->link_google_scholar)
                            <a href="{{ $dosen->link_google_scholar }}" target="_blank" class="bg-white border border-slate-200 text-slate-700 hover:border-slate-900 py-2 pub-radius text-xs font-bold transition">
                                Google Scholar
                            </a>
                        @endif
                        @if($dosen->link_sinta)
                            <a href="{{ $dosen->link_sinta }}" target="_blank" class="bg-white border border-slate-200 text-slate-700 hover:border-slate-900 py-2 pub-radius text-xs font-bold transition">
                                Profil SINTA
                            </a>
                        @endif
                    </div>
                </div>

                <div class="w-full md:w-2/3 p-8 flex flex-col justify-between">
                    <div>
                        <div class="mb-6">
                            <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider block mb-2">Profil Akademik</span>
                            <p class="text-slate-600 text-xs md:text-sm leading-relaxed text-justify whitespace-pre-wrap">{{ $dosen->deskripsi_diri ?? 'Dosen tetap pada ' . (\App\Models\Pengaturan::get('nama_kampus', 'STT GPI Papua')) . '.' }}</p>
                        </div>

                        @if($dosen->bidang_keahlian)
                            <div class="mb-6 border-t border-slate-100 pt-5">
                                <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider block mb-3">Bidang Keahlian</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(explode(',', $dosen->bidang_keahlian) as $keahlian)
                                        <span class="bg-slate-100 text-slate-800 text-xs px-3 py-1 rounded border border-slate-200 font-medium">
                                            {{ trim($keahlian) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($dosen->penelitians && $dosen->penelitians->isNotEmpty())
                            <div class="mb-6 border-t border-slate-100 pt-5">
                                <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider block mb-3">Penelitian & Publikasi Ilmiah</span>
                                <div class="space-y-3">
                                    @foreach($dosen->penelitians as $pub)
                                        <div class="p-3.5 pub-radius bg-slate-50 border border-slate-200">
                                            <div class="flex justify-between items-center gap-2 mb-1 text-[10px] font-mono text-slate-500">
                                                <span class="font-bold text-slate-700 uppercase">{{ $pub->kategori }}</span>
                                                <span>{{ $pub->tahun }}</span>
                                            </div>
                                            <h4 class="text-xs md:text-sm font-bold text-slate-900">{{ $pub->judul }}</h4>
                                            <p class="text-[11px] text-slate-500 mt-1">{{ $pub->nama_jurnal_penerbit_lokasi ?? '-' }}</p>
                                            @if($pub->tautan_eksternal)
                                                <a href="{{ $pub->tautan_eksternal }}" target="_blank" class="inline-block text-[11px] font-bold text-pub-primary hover:underline mt-1">
                                                    Buka Publikasi &rarr;
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($dosen->pengabdians && $dosen->pengabdians->isNotEmpty())
                            <div class="mb-6 border-t border-slate-100 pt-5">
                                <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider block mb-3">Pengabdian kepada Masyarakat</span>
                                <ul class="space-y-2">
                                    @foreach($dosen->pengabdians as $pkm)
                                        <li class="p-3 pub-radius bg-slate-50 border border-slate-200 text-xs flex justify-between items-center gap-3">
                                            <div>
                                                <span class="font-bold text-slate-900 block">{{ $pkm->judul }}</span>
                                                <span class="text-[11px] text-slate-500">{{ $pkm->nama_jurnal_penerbit_lokasi ?? $pkm->kategori }}</span>
                                            </div>
                                            <span class="text-[11px] font-mono font-bold text-slate-700 bg-white px-2.5 py-1 rounded border border-slate-200">{{ $pkm->tahun }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($dosen->mataKuliahs->isNotEmpty())
                            <div class="border-t border-slate-100 pt-5">
                                <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider block mb-3">Mata Kuliah yang Diampu</span>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($dosen->mataKuliahs->unique('nama_mk') as $matkul)
                                        <li class="text-slate-800 bg-slate-50 px-3 py-2 pub-radius border border-slate-200 text-xs font-medium truncate">
                                            {{ $matkul->nama_mk }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>