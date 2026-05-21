<!DOCTYPE html>
<html lang="id">

<head>
    @include('partials.head')
    <title>Layar Dapur — Booster Coffee</title>
</head>

<body class="min-h-screen bg-espresso text-cream">

    <!-- Header KDS -->
    <header class="flex items-center justify-between border-b border-amber-800 bg-primary-dark px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary">
                <span class="text-lg font-bold text-white">B</span>
            </div>
            <div>
                <div class="text-lg font-bold text-cream">Booster Coffee</div>
                <div class="text-xs text-amber-400">Kitchen Display System</div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="text-xs text-amber-400">Waktu Sekarang</div>
                <div class="text-sm font-bold text-cream" id="kds-clock">--:--:--</div>
            </div>
        </div>
    </header>

    <!-- Konten KDS -->
    <main class="p-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('kds-clock').textContent = now.toLocaleTimeString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    @fluxScripts
</body>

</html>
