<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $cfg = \App\Models\Pengaturan::allCached();
    @endphp
    <title>Verifikasi Keaslian Dokumen — {{ $cfg['singkatan_kampus'] }}</title>

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

    <main class="flex-grow py-14 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white pub-radius shadow-sm border border-slate-200 overflow-hidden">
                @if($isValid && $dataDokumen)
                    <div class="wm-banner text-white p-8 text-center">
                        <div class="relative z-10">
                            <span class="text-xs font-mono uppercase tracking-widest bg-white/15 border border-white/25 px-3 py-1 rounded">Terverifikasi Sah & Asli</span>
                            <h1 class="text-xl md:text-2xl font-serif font-bold mt-3 uppercase">{{ $dataDokumen['jenis_dokumen'] }}</h1>
                            <p class="text-xs text-white/80 mt-1">Pangkalan Data Resmi SIAKAD {{ $cfg['singkatan_kampus'] }}</p>
                        </div>
                    </div>

                    <div class="p-8 space-y-4 text-sm">
                        <div class="flex justify-between py-2.5 border-b border-slate-100">
                            <span class="text-slate-500 font-semibold uppercase text-xs">Nomor Dokumen</span>
                            <span class="font-mono font-bold text-slate-900">{{ $dataDokumen['nomor_dokumen'] }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-slate-100">
                            <span class="text-slate-500 font-semibold uppercase text-xs">Nama Pemilik</span>
                            <span class="font-bold text-slate-900 uppercase">{{ $dataDokumen['nama_pemilik'] }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-slate-100">
                            <span class="text-slate-500 font-semibold uppercase text-xs">NIM / Identitas</span>
                            <span class="font-mono font-bold text-slate-900">{{ $dataDokumen['nim_identitas'] }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-slate-100">
                            <span class="text-slate-500 font-semibold uppercase text-xs">Program Studi</span>
                            <span class="font-semibold text-slate-800 uppercase">{{ $dataDokumen['program_studi'] }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-slate-100">
                            <span class="text-slate-500 font-semibold uppercase text-xs">Tanggal Verifikasi</span>
                            <span class="font-mono text-slate-700">{{ $dataDokumen['tanggal_terbit'] }}</span>
                        </div>
                        <div class="bg-slate-50 p-4 pub-radius border border-slate-200">
                            <span class="text-[11px] font-mono font-bold text-slate-500 uppercase block mb-1">Keterangan Validasi:</span>
                            <p class="text-xs font-semibold text-slate-800">{{ $dataDokumen['keterangan'] }}</p>
                        </div>
                        <div class="text-center pt-2">
                            <span class="text-[11px] font-mono text-slate-400">SIGNATURE HASH: {{ $dataDokumen['kode_hash'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="bg-red-800 text-white p-8 text-center">
                        <span class="text-xs font-mono uppercase tracking-widest bg-black/25 px-3 py-1 rounded">Peringatan Verifikasi</span>
                        <h1 class="text-xl md:text-2xl font-serif font-bold mt-3 uppercase">Dokumen Tidak Ditemukan</h1>
                    </div>
                    <div class="p-8 text-center">
                        <p class="text-sm text-slate-600 mb-6">
                            Kode token verifikasi <strong class="font-mono text-red-700">{{ $token }}</strong> tidak terdaftar pada pangkalan data SIAKAD {{ $cfg['singkatan_kampus'] }}.
                        </p>
                        <a href="{{ url('/') }}" class="inline-block bg-pub-primary text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 pub-radius transition">
                            Kembali ke Halaman Utama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>