<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased dark">

<head>
    @include('partials.head')
</head>

<body
    class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 selection:bg-zinc-900 selection:text-white dark:selection:bg-white dark:selection:text-zinc-900">

    <!-- Sticky Header with Blur -->
    <flux:header container
        class="sticky top-0 z-50 backdrop-blur-md bg-white/80 dark:bg-zinc-900/80 border-b border-zinc-200/80 dark:border-zinc-800">
        <flux:sidebar.toggle
            class="lg:hidden mr-2 text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors"
            icon="bars-2" inset="left" />

        <x-app-logo href="{{ route('dashboard') }}" wire:navigate class="transition-opacity hover:opacity-80" />

        <flux:navbar class="-mb-px max-lg:hidden ml-6 gap-2">
            <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate class="rounded-lg transition-colors">
                {{ __('Dashboard') }}
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <flux:navbar class="me-1.5 space-x-1 rtl:space-x-reverse py-0!">
            <flux:tooltip :content="__('Search')" position="bottom">
                <flux:navbar.item
                    class="!h-10 w-10 justify-center rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors [&>div>svg]:size-5"
                    icon="magnifying-glass" href="#" :label="__('Search')" />
            </flux:tooltip>
            <flux:tooltip :content="__('Repository')" position="bottom">
                <flux:navbar.item
                    class="h-10 w-10 justify-center rounded-full max-lg:hidden hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors [&>div>svg]:size-5"
                    icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank"
                    :label="__('Repository')" />
            </flux:tooltip>
            <flux:tooltip :content="__('Documentation')" position="bottom">
                <flux:navbar.item
                    class="h-10 w-10 justify-center rounded-full max-lg:hidden hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors [&>div>svg]:size-5"
                    icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank"
                    :label="__('Documentation')" />
            </flux:tooltip>
        </flux:navbar>

        <x-desktop-user-menu class="ml-2" />
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar collapsible="mobile" sticky
        class="lg:hidden border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 shadow-xl">
        <flux:sidebar.header class="pt-6 pb-4 px-4 border-b border-zinc-100 dark:border-zinc-800/50">
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2 text-zinc-500 hover:text-zinc-900" />
        </flux:sidebar.header>

        <flux:sidebar.nav class="px-2 mt-4">
            <flux:sidebar.group :heading="__('Platform')">
                <flux:sidebar.item icon="layout-grid" :href="route('dashboard')"
                    :current="request()->requestIs('dashboard')" wire:navigate class="rounded-lg">
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav class="px-2 pb-4">
            <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank" class="rounded-lg">
                {{ __('Repository') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank" class="rounded-lg">
                {{ __('Documentation') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>
    </flux:sidebar>

    <main class="p-4 md:p-6 lg:p-8 max-w-screen-2xl mx-auto w-full transition-all duration-300">
        {{ $slot }}
    </main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
