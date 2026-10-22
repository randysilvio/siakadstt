<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Portal Autentikasi - SIAKAD STT GPI Papua</title>
        
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col justify-between items-center py-8 px-4">

            {{-- Navigasi Atas Kembali ke Beranda Publik --}}
            <div class="w-full sm:max-w-md flex justify-between items-center mb-2">
                <a href="{{ url('/') }}" class="inline-flex items-center text-xs font-bold uppercase tracking-wider text-gray-600 hover:text-teal-800 transition">
                    &larr; Beranda Utama
                </a>
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">STT GPI PAPUA</span>
            </div>

            {{-- Kartu Utama Siku Enterprise --}}
            <div class="w-full sm:max-w-md px-8 py-6 bg-white shadow-sm border border-gray-200 border-t-4 border-t-black sm:rounded-none overflow-hidden my-auto">
                {{ $slot }}
            </div>

            {{-- Footer Terpusat Seluruh Halaman Guest/Auth --}}
            <div class="w-full sm:max-w-md text-center mt-6 pt-4 border-t border-gray-200">
                <p class="text-[11px] text-gray-400 uppercase tracking-tight">
                    &copy; {{ date('Y') }} <strong>STT GPI Papua</strong> | Biro Administrasi Akademik & Kemahasiswaan
                </p>
            </div>
        </div>
    </body>
</html>