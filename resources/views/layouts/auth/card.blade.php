<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    @include('partials.head')
</head>

<body class="h-full bg-stone-50 font-sans text-stone-900 antialiased">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            {{-- Logo --}}
            <div class="flex justify-center mb-6">
                <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" class="h-16 w-auto">
            </div>
            <h2 class="text-center text-2xl font-black tracking-tight text-stone-900">
                {{ $title ?? '' }}
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white px-8 py-10 shadow-lg border border-stone-100 rounded-3xl">
                {{ $slot }}
            </div>
        </div>
    </div>

    @persist('toast')
        <flux:toast.group />
    @endpersist
    @fluxScripts
</body>

</html>
