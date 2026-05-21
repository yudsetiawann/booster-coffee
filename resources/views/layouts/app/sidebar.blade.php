<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">

<head>
    @include('partials.head')
</head>

<body
    class="min-h-screen bg-zinc-50/50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 selection:bg-zinc-900 selection:text-white dark:selection:bg-white dark:selection:text-zinc-900">

    <flux:sidebar sticky collapsible
        class="border-e border-amber-200/60 bg-amber-50 dark:bg-zinc-900 dark:border-zinc-800 shadow-xl lg:shadow-none">

        {{-- ✅ PERBAIKAN: flex justify-between + items-center + gap dihapus agar tidak overflow --}}
        <flux:sidebar.header class="px-4 pt-6 pb-4">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                {{-- Logo: selalu tampil --}}
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-100 bg-white shadow-sm">
                    <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name', 'Booster Coffee') }}"
                        class="h-8 w-8 rounded-lg object-cover">
                </div>

                {{-- Nama app: sembunyi saat collapsed --}}
                <div class="in-data-flux-sidebar-collapsed-desktop:hidden min-w-0 flex flex-col justify-center">
                    <span class="truncate text-sm font-bold tracking-tight text-amber-900 dark:text-white">
                        {{ config('app.name', 'Booster Coffee') }}
                    </span>
                    <span class="truncate text-xs font-medium text-amber-600/80 dark:text-zinc-400">
                        Point of Sale
                    </span>
                </div>
            </div>

            {{-- ✅ PERBAIKAN: Gunakan flux:sidebar.collapse bawaan untuk desktop juga,
                 bukan dispatchEvent manual yang bisa broken --}}
            <flux:sidebar.collapse
                class="shrink-0 text-amber-700 hover:text-amber-900 dark:text-zinc-400 dark:hover:text-white" />
        </flux:sidebar.header>

        <flux:sidebar.nav class="mt-4 space-y-0.5 px-2">

            {{-- Label grup Operasional --}}
            <div class="in-data-flux-sidebar-collapsed-desktop:hidden px-3 pb-2 pt-1">
                <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                    Operasional
                </span>
            </div>

            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate class="rounded-lg transition-colors">
                {{ __('Dashboard') }}
            </flux:sidebar.item>

            @hasrole('kasir|admin')
                <flux:sidebar.item icon="table-cells" :href="route('meja.index')" :current="request()->routeIs('meja.*')"
                    wire:navigate class="rounded-lg transition-colors">
                    {{ __('Manajemen Meja') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="clipboard-document-list" :href="route('order.index')"
                    :current="request()->routeIs('order.*')" wire:navigate class="rounded-lg transition-colors">
                    {{ __('Pesanan') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="banknotes" :href="route('pembayaran.index')"
                    :current="request()->routeIs('pembayaran.*')" wire:navigate class="rounded-lg transition-colors">
                    {{ __('Pembayaran') }}
                </flux:sidebar.item>
            @endhasrole

            @hasrole('dapur|admin')
                <flux:sidebar.item icon="computer-desktop" :href="route('kds.index')" :current="request()->routeIs('kds.*')"
                    wire:navigate class="rounded-lg transition-colors">
                    {{ __('Layar Dapur (KDS)') }}
                </flux:sidebar.item>
            @endhasrole

            @hasrole('admin')
                {{-- Divider antar grup --}}
                <div
                    class="in-data-flux-sidebar-collapsed-desktop:hidden mx-3 my-2 h-px bg-zinc-200/80 dark:bg-zinc-700/50">
                </div>

                {{-- Label grup Manajemen --}}
                <div class="in-data-flux-sidebar-collapsed-desktop:hidden px-3 pb-2 pt-1">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                        Manajemen
                    </span>
                </div>

                <flux:sidebar.item icon="book-open" :href="route('menu.index')" :current="request()->routeIs('menu.*')"
                    wire:navigate class="rounded-lg transition-colors">
                    {{ __('Menu') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="tag" :href="route('promo.index')" :current="request()->routeIs('promo.*')"
                    wire:navigate class="rounded-lg transition-colors">
                    {{ __('Promo & Diskon') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="calendar-days" :href="route('reservasi.index')"
                    :current="request()->routeIs('reservasi.*')" wire:navigate class="rounded-lg transition-colors">
                    {{ __('Reservasi') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="beaker" :href="route('stok.index')" :current="request()->routeIs('stok.*')"
                    wire:navigate class="rounded-lg transition-colors">
                    {{ __('Stok Bahan Baku') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-bar" :href="route('laporan.index')"
                    :current="request()->routeIs('laporan.*')" wire:navigate class="rounded-lg transition-colors">
                    {{ __('Laporan') }}
                </flux:sidebar.item>
            @endhasrole

        </flux:sidebar.nav>

        <flux:spacer />

        {{-- ✅ PERBAIKAN: User menu hanya untuk desktop, mobile sudah ada di header --}}
        <div class="hidden px-2 pb-4 lg:block">
            <x-desktop-user-menu class="rounded-xl border border-zinc-200 shadow-sm dark:border-zinc-800"
                :name="auth()->user()->name" />
        </div>

    </flux:sidebar>

    {{-- Mobile Header --}}
    <flux:header
        class="sticky top-0 z-50 border-b border-amber-200/60 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/80 lg:hidden">
        {{-- ✅ PERBAIKAN: 3-column grid agar logo benar-benar center --}}
        <div class="grid w-full grid-cols-3 items-center">

            {{-- Kiri: tombol hamburger --}}
            <div class="flex items-center">
                <flux:sidebar.toggle class="text-amber-700 dark:text-zinc-300" icon="bars-2" inset="left" />
            </div>

            {{-- Tengah: logo --}}
            <div class="flex items-center justify-center">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-amber-100 bg-white shadow-sm">
                    <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name', 'Booster Coffee') }}"
                        class="h-7 w-7 rounded-md object-cover">
                </div>
            </div>

            {{-- Kanan: profil --}}
            <div class="flex items-center justify-end">
                <flux:dropdown position="bottom" align="end">
                    <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down"
                        class="text-zinc-700 dark:text-zinc-300" />
                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-2">
                                <div class="flex items-center gap-3 px-1 py-1.5">
                                    <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()"
                                        class="shadow-sm" />
                                    <div class="grid min-w-0 flex-1">
                                        <flux:heading class="truncate font-semibold text-zinc-900 dark:text-white">
                                            {{ auth()->user()->name }}
                                        </flux:heading>
                                        <flux:text class="truncate text-zinc-500">
                                            {{ auth()->user()->email }}
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30">
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>

        </div>
    </flux:header>

    {{ $slot }}

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
