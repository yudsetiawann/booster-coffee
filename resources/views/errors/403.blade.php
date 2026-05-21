<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak | Booster Coffee</title>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-stone-50 flex items-center justify-center">

    <div class="text-center px-6">

        {{-- Logo --}}
        <div class="flex justify-center mb-8">
            <img src="{{ asset('img/logo.png') }}" alt="Booster Coffee" class="h-16 w-auto">
        </div>

        {{-- Kode Error --}}
        <h1 class="text-9xl font-black tracking-tight text-amber-600 mb-2">403</h1>

        {{-- Pesan --}}
        <h2 class="text-2xl font-black tracking-tight text-stone-900 mb-3">Akses Ditolak</h2>
        <p class="text-sm text-stone-500 mb-8 max-w-sm mx-auto">
            Maaf, kamu tidak memiliki izin untuk mengakses halaman ini.
            Silakan hubungi Admin jika kamu merasa ini adalah kesalahan.
        </p>

        {{-- Tombol --}}
        <div class="flex justify-center gap-3">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-amber-700 transition-colors">
                Kembali ke Dashboard
            </a>
        </div>

    </div>

</body>

</html>
