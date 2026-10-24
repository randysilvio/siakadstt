<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Keaslian Dokumen Resmi - STT GPI Papua</title>

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|raleway:700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#f0fdfa', 100: '#ccfbf1', 400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 900: '#134e4a' },
                    },
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                        heading: ['Raleway', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .glass-effect { background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
</head>
<body class="antialiased text-slate-800 flex flex-col min-h-screen bg-slate-50">

    @include('partials.public_navbar')

    <main class="flex-grow py-14 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                @if($isValid && $dataDokumen)
                    <div class="bg-emerald-700 text-white p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-shield-check fa-circle-check text-3xl text-white"></i>
                        </div>
                        <span class="text-xs font-mono uppercase tracking-widest bg-emerald-900/50 px-3 py-1 rounded-full">Terverifikasi Sah & Asli</span>
                        <h1 class="text-2xl font-heading font-extrabold mt-3 uppercase">{{ $dataDokumen['jenis_dokumen'] }}</h1>
                        <p class="text-xs text-emerald-100 mt-1">Pangkalan Data Resmi SIAKAD STT GPI Papua</p>
                    </div>

                    <div class="p-8 space-y-4 text-sm">
                        <div class="flex justify-between py-2.5 border-b border-gray-100">
                            <span class="text-slate-400 font-semibold uppercase text-xs">Nomor Registrasi</span>
                            <span class="font-mono font-bold text-slate-800">{{ $dataDokumen['nomor_dokumen'] }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-gray-100">
                            <span class="text-slate-400 font-semibold uppercase text-xs">Nama Pemilik Dokumen</span>
                            <span class="font-bold text-slate-900 uppercase">{{ $dataDokumen['nama_pemilik'] }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-gray-100">
                            <span class="text-slate-400 font-semibold uppercase text-xs">NIM / Identitas</span>
                            <span class="font-mono font-bold text-brand-700">{{ $dataDokumen['nim_identitas'] }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-gray-100">
                            <span class="text-slate-400 font-semibold uppercase text-xs">Program Studi</span>
                            <span class="font-semibold text-slate-800 uppercase">{{ $dataDokumen['program_studi'] }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-gray-100">
                            <span class="text-slate-400 font-semibold uppercase text-xs">Tanggal Verifikasi</span>
                            <span class="font-mono text-slate-700">{{ $dataDokumen['tanggal_terbit'] }}</span>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-gray-200">
                            <span class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Keterangan / Detail:</span>
                            <p class="text-xs font-semibold text-slate-700">{{ $dataDokumen['keterangan'] }}</p>
                        </div>
                        <div class="text-center pt-2">
                            <span class="text-[11px] font-mono text-slate-400">SIGNATURE HASH: {{ $dataDokumen['kode_hash'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="bg-rose-700 text-white p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-triangle-exclamation text-3xl text-white"></i>
                        </div>
                        <span class="text-xs font-mono uppercase tracking-widest bg-rose-900/50 px-3 py-1 rounded-full">Peringatan Keamanan</span>
                        <h1 class="text-2xl font-heading font-extrabold mt-3 uppercase">Dokumen Tidak Ditemukan / Tidak Valid</h1>
                    </div>
                    <div class="p-8 text-center">
                        <p class="text-sm text-slate-600 mb-6">
                            Kode token verifikasi <strong class="font-mono text-rose-600">{{ $token }}</strong> tidak terdaftar atau tidak cocok dengan tanda tangan kriptografi SIAKAD STT GPI Papua.
                        </p>
                        <a href="{{ url('/') }}" class="inline-block bg-slate-900 text-white text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-full hover:bg-brand-600 transition">
                            Kembali ke Beranda
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </main>

    @include('partials.public_footer')

</body>
</html>