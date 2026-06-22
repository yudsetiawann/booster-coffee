<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Booster Coffee') }} &mdash; Point of Sale</title>

    <link rel="icon" href="/img/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="/img/logo.png">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-espresso antialiased">

    {{-- NAVBAR --}}
    <header class="border-b border-zinc-200">
        <div class="mx-auto max-w-7xl px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center">
                <img src="{{ asset('img/logo.png') }}"
                     alt="{{ config('app.name', 'Booster Coffee') }}"
                     class="h-10 w-auto">
            </a>

            @if (Route::has('login'))
                <nav class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold bg-espresso text-cream hover:bg-primary-dark transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center px-5 py-2 text-sm font-medium text-espresso border border-zinc-300 hover:border-espresso transition-colors">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center px-5 py-2 text-sm font-semibold bg-espresso text-cream hover:bg-primary-dark transition-colors">
                                Daftar
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
    </header>

    {{-- HERO --}}
    <section class="border-b border-zinc-200 bg-surface">
        <div class="mx-auto max-w-7xl px-6 py-20 md:py-28">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-widest text-primary mb-5">
                    Point of Sale System
                </p>
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-espresso leading-tight mb-6">
                    Operasional Kafe,<br>Satu Platform.
                </h1>
                <p class="text-base md:text-lg text-text-muted leading-relaxed mb-10 max-w-xl">
                    Sistem POS terintegrasi untuk Booster Coffee. Kelola pesanan, pembayaran, stok,
                    dan laporan dari satu dasbor yang cepat dan andal.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-7 py-3 text-sm font-semibold bg-espresso text-cream hover:bg-primary transition-colors">
                        Masuk ke Sistem
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="border-b border-zinc-200">
        <div class="mx-auto max-w-7xl px-6 py-16">
            <p class="text-xs font-bold uppercase tracking-widest text-primary mb-10">Fitur Utama</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-px bg-zinc-200">

                <div class="bg-white p-8">
                    <div class="w-10 h-10 border border-zinc-200 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-espresso mb-2">Manajemen Meja</h3>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Pantau status seluruh meja secara real-time. Alokasikan, buka, dan tutup meja
                        dari satu tampilan denah interaktif.
                    </p>
                </div>

                <div class="bg-white p-8">
                    <div class="w-10 h-10 border border-zinc-200 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-espresso mb-2">Input Pesanan</h3>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Proses pesanan cepat dan akurat langsung dari layar kasir. Dukung modifikasi
                        item, catatan khusus, dan promo terapan.
                    </p>
                </div>

                <div class="bg-white p-8">
                    <div class="w-10 h-10 border border-zinc-200 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-espresso mb-2">Layar Dapur (KDS)</h3>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Kitchen Display System terintegrasi memastikan tim dapur menerima pesanan
                        secara langsung tanpa nota kertas.
                    </p>
                </div>

                <div class="bg-white p-8">
                    <div class="w-10 h-10 border border-zinc-200 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-espresso mb-2">Pembayaran</h3>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Proses pembayaran dengan kalkulasi kembalian otomatis dan riwayat transaksi
                        yang lengkap dan terstruktur.
                    </p>
                </div>

                <div class="bg-white p-8">
                    <div class="w-10 h-10 border border-zinc-200 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-espresso mb-2">Manajemen Stok</h3>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Pantau stok bahan baku secara otomatis. Terima peringatan saat stok mendekati
                        batas minimum yang ditentukan.
                    </p>
                </div>

                <div class="bg-white p-8">
                    <div class="w-10 h-10 border border-zinc-200 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-espresso mb-2">Laporan &amp; Analitik</h3>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Laporan penjualan harian, mingguan, dan bulanan. Pantau pendapatan dan tren
                        transaksi untuk keputusan bisnis yang lebih tepat.
                    </p>
                </div>

            </div>
        </div>
    </section>

    {{-- ROLES --}}
    <section class="border-b border-zinc-200 bg-surface">
        <div class="mx-auto max-w-7xl px-6 py-16">
            <p class="text-xs font-bold uppercase tracking-widest text-primary mb-10">Akses Berbasis Peran</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-espresso border-t-2 border-espresso pt-4 mb-3">
                        Admin
                    </div>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Akses penuh ke semua fitur: manajemen menu, promo, reservasi, stok, laporan,
                        dan pengaturan sistem.
                    </p>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-espresso border-t-2 border-primary pt-4 mb-3">
                        Kasir
                    </div>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Akses ke manajemen meja, input pesanan, dan proses pembayaran untuk operasional
                        lantai sehari-hari.
                    </p>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-espresso border-t-2 border-accent pt-4 mb-3">
                        Dapur
                    </div>
                    <p class="text-sm text-text-muted leading-relaxed">
                        Akses ke Kitchen Display System untuk menerima dan memproses antrian pesanan
                        secara real-time.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="border-t border-zinc-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <img src="{{ asset('img/logo.png') }}"
                 alt="{{ config('app.name', 'Booster Coffee') }}"
                 class="h-7 w-auto">
            <p class="text-xs text-zinc-400">
                &copy; {{ date('Y') }} {{ config('app.name', 'Booster Coffee') }}. All rights reserved.
            </p>
        </div>
    </footer>

</body>
</html>
