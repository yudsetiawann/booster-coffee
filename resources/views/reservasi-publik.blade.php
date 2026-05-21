<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Meja — Booster Coffee</title>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-surface">

    {{-- Header --}}
    <header class="bg-primary-dark px-6 py-4">
        <div class="mx-auto max-w-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/logo.png') }}" alt="Booster Coffee" class="h-12 w-12 rounded-full object-cover" />
                <div>
                    <p class="text-sm font-bold text-cream">Booster Coffee</p>
                    <p class="text-xs text-amber-400">Reservasi Meja Online</p>
                </div>
            </div>
        </div>
    </header>

    {{-- Konten --}}
    <main class="mx-auto max-w-2xl px-6 py-8">
        @livewire('reservasi.form-publik')
    </main>

    {{-- Footer --}}
    <footer class="mt-8 border-t border-zinc-200 px-6 py-4 text-center text-xs text-zinc-400">
        Jl. Srengseng Raya No.85 Kembangan, Jakarta Barat 11630 · 0851-8312-3932
    </footer>

    @livewireScripts
    @vite(['resources/js/app.js'])
</body>

</html>
