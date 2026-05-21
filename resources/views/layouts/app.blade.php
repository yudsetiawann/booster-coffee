<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="p-4 md:p-6 lg:p-8 max-w-screen-2xl mx-auto w-full transition-all duration-300">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
