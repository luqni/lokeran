<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Halaman Tidak Ditemukan (404) - Loker Merah Putih</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col items-center justify-center p-4">
            <div class="max-w-md w-full text-center space-y-6">
                <!-- Illustration -->
                <div class="relative w-72 h-72 mx-auto sm:w-80 sm:h-80 transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('images/404.png') }}" alt="Lowongan Kosong 404" class="w-full h-full object-contain rounded-2xl shadow-xl shadow-red-500/10 border border-gray-100 bg-white">
                    <div class="absolute -bottom-2 -right-2 bg-red-600 text-white font-extrabold text-xs px-3 py-1 rounded-full shadow-lg">
                        Error 404
                    </div>
                </div>

                <!-- Text Content -->
                <div class="space-y-2">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Waduh! Lowongan Kosong
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed max-w-sm mx-auto">
                        Halaman yang Anda cari tidak ditemukan atau lowongan kerja ini sudah tidak tersedia lagi.
                    </p>
                </div>

                <!-- Call to Action -->
                <div class="pt-2">
                    <a href="/" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-600/30 hover:bg-red-700 active:scale-95 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
