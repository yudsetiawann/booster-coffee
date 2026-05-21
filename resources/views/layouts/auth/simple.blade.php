<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-stone-50 font-sans antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center p-6 md:p-10">

            <div class="w-full max-w-sm">
                <a href="{{ route('home') }}" class="flex flex-col items-center mb-8" wire:navigate>
                    <img src="{{ asset('img/logo.png') }}"
                         alt="{{ config('app.name', 'Booster Coffee') }}"
                         class="h-14 w-auto">
                </a>

                @isset($title)
                    <h2 class="mb-6 text-center text-2xl font-black tracking-tight text-stone-900">{{ $title }}</h2>
                @endisset

                <div class="rounded-3xl border border-stone-100 bg-white px-8 py-10 shadow-lg">
                    {{ $slot }}
                </div>
            </div>

        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
