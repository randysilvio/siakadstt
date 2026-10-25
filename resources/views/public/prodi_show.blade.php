<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Program Studi {{ $programStudi->nama_prodi }} — {{ $cfg['singkatan_kampus'] }}</title>
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
        .smt-tab-active { background-color: var(--pub-primary); color: #ffffff; border-color: var(--pub-primary); }
        .smt-tab-inactive { background-color: #ffffff; color: #334155; border-color: #cbd5e1; }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow">
        {{-- Banner Header Program Studi --}}
        <section class="wm-banner text-white py-12">
            <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10 flex flex-col md:flex-row justify-between items-md-center gap-6">
                <div>
                    <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-white/75 mb-2">
                        <span>Program Studi ({{ $programStudi->jenjang ?? 'S1' }})</span>
                        <span>|</span>
                        <span>Kode: {{ $programStudi->kode_prodi ?? '-' }}</span>
                        @if($programStudi->fakultas)
                            <span>|</span>
                            <span>{{ $programStudi->fakultas->nama_fakultas }}</span>
                        @endif
                    </div>
                    <h1 class="font-serif text-2xl md:text-4xl font-bold uppercase">{{ $programStudi->nama_prodi }}</h1>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('pmb.register') }}" class="bg-white text-slate-900 hover:bg-slate-100 font-bold px-5 py-2.5 pub-radius text-xs uppercase tracking-wider transition">
                        Daftar di Prodi Ini &rarr;
                    </a>
                </div>
            </div>
        </section>

        <div class="container mx-auto px-4 lg:px-8 max-w-7xl py-10">
            {{-- Ringkasan Statistik & Pimpinan Prodi --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
                <div class="lg:col-span-8 wm-featured bg-white p-7 shadow-2xs border border-slate-200 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block mb-1">Profil Akademik</span>
                        <h2 class="font-serif text-xl font-bold text-slate-900 mb-3">Tentang Program Studi {{ $programStudi->nama_prodi }}</h2>
                        <p class="text-sm text-slate-600 leading-relaxed text-justify mb-6">
                            Program Studi {{ $programStudi->nama_prodi }} pada {{ $cfg['nama_kampus'] }} menyelenggarakan pendidikan tinggi jenjang {{ $programStudi->jenjang ?? 'S1' }} dengan kurikulum terstruktur yang memadukan penguasaan keilmuan akademik, penelitian kontekstual, dan pembentukan karakter pelayan serta pendidik yang berintegritas.
                        </p>
                    </div>
                    <div class="border-t border-slate-100 pt-4 flex flex-wrap justify-between items-center gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 font-mono uppercase block text-[10px]">Ketua Program Studi</span>
                            <strong class="text-slate-900 text-sm uppercase">{{ optional($programStudi->kaprodi)->nama_lengkap ?? '-' }}</strong>
                        </div>
                        @if($kurikulum)
                            <div class="text-right">
                                <span class="text-slate-400 font-mono uppercase block text-[10px]">Kurikulum Berlaku</span>
                                <strong class="text-slate-900 font-mono">{{ $kurikulum->nama_kurikulum }} ({{ $kurikulum->tahun }})</strong>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-4 grid grid-cols-2 gap-4">
                    <div class="wm-featured wm-featured-dark bg-pub-primary text-white p-5 flex flex-col justify-between">
                        <span class="text-[10px] font-mono uppercase text-white/75">Jenjang</span>
                        <div>
                            <div class="text-2xl font-serif font-bold">{{ $programStudi->jenjang ?? 'S1' }}</div>
                            <div class="text-[11px] text-white/85 uppercase mt-1">Program Akademik</div>
                        </div>
                    </div>
                    <div class="pub-radius bg-white p-5 border border-slate-200 border-t-4 border-t-pub-primary flex flex-col justify-between">
                        <span class="text-[10px] font-mono uppercase text-slate-400">Mahasiswa</span>
                        <div>
                            <div class="text-2xl font-serif font-bold text-slate-900">{{ number_format($jumlahMahasiswaAktif) }}</div>
                            <div class="text-[11px] text-slate-600 uppercase mt-1">Mahasiswa Aktif</div>
                        </div>
                    </div>
                    <div class="pub-radius bg-white p-5 border border-slate-200 border-t-4 border-t-pub-primary flex flex-col justify-between">
                        <span class="text-[10px] font-mono uppercase text-slate-400">Pengajar</span>
                        <div>
                            <div class="text-2xl font-serif font-bold text-slate-900">{{ number_format($programStudi->dosens->count()) }}</div>
                            <div class="text-[11px] text-slate-600 uppercase mt-1">Dosen Homebase</div>
                        </div>
                    </div>
                    <div class="pub-radius bg-white p-5 border border-slate-200 border-t-4 border-t-pub-primary flex flex-col justify-between">
                        <span class="text-[10px] font-mono uppercase text-slate-400">Lulusan</span>
                        <div>
                            <div class="text-2xl font-serif font-bold text-slate-900">{{ number_format($jumlahAlumni) }}</div>
                            <div class="text-[11px] text-slate-600 uppercase mt-1">Alumni</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Struktur Kurikulum & Sebaran Mata Kuliah Semester 1 - 8 --}}
            <div class="mb-12">
                <div class="mb-6 pb-2 border-b border-slate-200 flex flex-col sm:flex-row justify-between sm:items-end gap-2">
                    <div>
                        <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block">Struktur Akademik</span>
                        <h2 class="font-serif text-xl font-bold text-slate-900">Sebaran Mata Kuliah per Semester</h2>
                    </div>
                    <span class="text-xs font-mono text-slate-500">
                        Total Mata Kuliah: <strong>{{ $mataKuliahsPerSemester->flatten()->count() }} MK ({{ $mataKuliahsPerSemester->flatten()->sum('sks') }} SKS)</strong>
                    </span>
                </div>

                @if($mataKuliahsPerSemester->isEmpty())
                    <div class="bg-white pub-radius border border-slate-200 p-10 text-center text-xs text-slate-500 uppercase font-bold">
                        Data kurikulum untuk program studi ini sedang dalam pembaruan.
                    </div>
                @else
                    {{-- Tombol Tab Semester --}}
                    <div class="flex flex-wrap gap-2 mb-5">
                        @foreach($mataKuliahsPerSemester as $smt => $mkList)
                            <button type="button"
                                    onclick="bukaTabSemester(event, 'smt-pane-{{ $smt }}')"
                                    class="smt-tab-btn px-4 py-2 pub-radius text-xs font-mono font-bold uppercase border transition {{ $loop->first ? 'smt-tab-active' : 'smt-tab-inactive hover:bg-slate-100' }}">
                                Semester {{ $smt }} ({{ $mkList->sum('sks') }} SKS)
                            </button>
                        @endforeach
                    </div>

                    {{-- Konten Tabel Mata Kuliah per Semester --}}
                    @foreach($mataKuliahsPerSemester as $smt => $mkList)
                        <div id="smt-pane-{{ $smt }}" class="smt-pane {{ $loop->first ? 'block' : 'hidden' }} bg-white pub-radius border border-slate-200 shadow-2xs overflow-hidden">
                            <div class="bg-slate-50 px-6 py-3.5 border-b border-slate-200 flex justify-between items-center">
                                <span class="font-bold text-xs uppercase text-slate-800">Daftar Mata Kuliah Semester {{ $smt }}</span>
                                <span class="font-mono text-xs font-bold text-slate-700">Beban: {{ $mkList->sum('sks') }} SKS</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[560px]">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-[11px] font-mono uppercase text-slate-500 bg-white">
                                            <th class="px-6 py-3 w-14 text-center">No</th>
                                            <th class="px-6 py-3 w-32">Kode MK</th>
                                            <th class="px-6 py-3">Nama Mata Kuliah</th>
                                            <th class="px-6 py-3 w-56">Dosen Pengampu</th>
                                            <th class="px-6 py-3 w-24 text-center">Beban SKS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-xs">
                                        @foreach($mkList as $idx => $mk)
                                            <tr class="hover:bg-slate-50/80 transition">
                                                <td class="px-6 py-3.5 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                                <td class="px-6 py-3.5 font-mono font-bold text-slate-800">{{ $mk->kode_mk }}</td>
                                                <td class="px-6 py-3.5 font-bold text-slate-900 uppercase">{{ $mk->nama_mk }}</td>
                                                <td class="px-6 py-3.5 text-slate-600">{{ optional($mk->dosen)->nama_lengkap ?? '-' }}</td>
                                                <td class="px-6 py-3.5 text-center font-mono font-bold text-slate-900">{{ $mk->sks }} SKS</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Dosen Homebase Program Studi --}}
            @if($programStudi->dosens->isNotEmpty())
                <div>
                    <div class="mb-6 pb-2 border-b border-slate-200 flex justify-between items-end">
                        <div>
                            <span class="text-xs font-mono uppercase tracking-widest text-slate-500 block">Tenaga Pendidik</span>
                            <h2 class="font-serif text-xl font-bold text-slate-900">Dosen Program Studi {{ $programStudi->nama_prodi }}</h2>
                        </div>
                        <a href="{{ route('dosen.public.index', ['program_studi_id' => $programStudi->id]) }}" class="text-xs font-bold text-pub-primary hover:underline uppercase">Lihat Semua &rarr;</a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        @foreach($programStudi->dosens->take(8) as $dsn)
                            <div class="hover-lift pub-radius bg-white p-5 border border-slate-200 flex items-center gap-3.5">
                                @if($dsn->foto_profil)
                                    <img src="{{ asset('storage/' . $dsn->foto_profil) }}" class="w-12 h-12 rounded-full object-cover border border-slate-200 shrink-0" alt="{{ $dsn->nama_lengkap }}">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-500 shrink-0">
                                        {{ substr($dsn->nama_lengkap, 0, 1) }}
                                    </div>
                                @endif
                                <div class="overflow-hidden">
                                    <h4 class="font-bold text-xs text-slate-900 truncate">{{ $dsn->nama_lengkap }}</h4>
                                    <span class="text-[11px] text-slate-500 block truncate">{{ $dsn->jabatan_akademik ?? 'Dosen Tetap' }}</span>
                                    @if($dsn->nidn)
                                        <a href="{{ route('dosen.public.show', $dsn->nidn) }}" class="text-[10px] font-mono font-bold text-pub-primary hover:underline">Lihat Profil &rarr;</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>

    @include('partials.public_footer')

    <script>
        function bukaTabSemester(event, paneId) {
            document.querySelectorAll('.smt-pane').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('block');
            });
            document.querySelectorAll('.smt-tab-btn').forEach(btn => {
                btn.classList.remove('smt-tab-active');
                btn.classList.add('smt-tab-inactive');
            });
            const target = document.getElementById(paneId);
            if (target) {
                target.classList.remove('hidden');
                target.classList.add('block');
            }
            event.currentTarget.classList.remove('smt-tab-inactive');
            event.currentTarget.classList.add('smt-tab-active');
        }
    </script>
</body>
</html>