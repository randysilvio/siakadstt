<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Administrasi Kampus - STT GPI Papua</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
    </style>
</head>
<body class="antialiased" style="background-image: url('{{ asset('images/latar-welcome.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed;">

    <div class="absolute inset-0 bg-black/50"></div>

    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center text-center p-6">
        
        <div class="mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI Papua" class="h-24 w-24 mx-auto md:h-32 md:w-32">
        </div>

        <div class="mb-8">
            <h1 class="text-2xl md:text-4xl font-bold text-white tracking-wider">
                SISTEM ADMINISTRASI KAMPUS
            </h1>
            <h2 class="text-xl md:text-2xl font-semibold text-blue-300">
                STT GPI PAPUA FAKFAK
            </h2>
        </div>

        <div class="w-full max-w-sm">
            <div class="space-y-4">
                <a href="{{ route('login') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-lg transition duration-300 ease-in-out transform hover:scale-105">
                    Login
                </a>
            </div>
        </div>

        <div class="absolute bottom-6">
            <p class="text-sm text-gray-300">
                &copy; {{ date('Y') }} STT GPI Papua. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>