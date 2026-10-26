<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $gCfg     = \App\Models\Pengaturan::allCached();
        $gTheme   = \App\Models\Pengaturan::themeColors();
        $gLogo    = \App\Models\Pengaturan::logoUrl();
        $gFavicon = !empty($gCfg['favicon_path']) ? asset('storage/' . $gCfg['favicon_path']) : $gLogo;
        $gBrand   = $gCfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
    @endphp

    <title>Portal Autentikasi — SIAKAD {{ $gBrand }}</title>
    
    <link rel="icon" href="{{ $gFavicon }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        :root {
            --auth-primary: {{ $gTheme['primary'] }};
            --auth-accent: {{ $gTheme['accent'] }};
            --auth-radius: {{ $gTheme['radius'] ?? '10px' }};
        }
        .auth-card {
            border-radius: var(--auth-radius);
            border-top: 4px solid var(--auth-primary);
        }
        .btn-auth-primary {
            background-color: var(--auth-primary) !important;
            color: #ffffff !important;
            border-radius: calc(var(--auth-radius) * 0.75);
        }
        .btn-auth-primary:hover {
            background-color: var(--auth-accent) !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body class="font-sans text-slate-900 antialiased bg-slate-50">
    <div class="min-h-screen flex flex-col justify-between items-center py-8 px-4">

        {{-- Navigasi Atas Kembali ke Beranda Publik --}}
        <div class="w-full sm:max-w-md flex justify-between items-center mb-3">
            <a href="{{ url('/') }}" class="inline-flex items-center text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-slate-950 transition">
                &larr; Beranda Utama
            </a>
            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400">{{ $gBrand }}</span>
        </div>

        {{-- Kartu Utama Autentikasi --}}
        <div class="w-full sm:max-w-md px-6 sm:px-8 py-6 bg-white shadow-sm border border-slate-200 auth-card overflow-hidden my-auto">
            {{ $slot }}
        </div>

        {{-- Footer Terpusat Seluruh Halaman Guest/Auth --}}
        <div class="w-full sm:max-w-md text-center mt-6 pt-4 border-t border-slate-200">
            <p class="text-[11px] text-slate-400 uppercase tracking-tight">
                &copy; {{ date('Y') }} <strong>{{ $gBrand }}</strong> — {{ $gCfg['nama_kampus'] ?? 'Sistem Informasi Akademik' }}
            </p>
        </div>
    </div>
</body>
</html>