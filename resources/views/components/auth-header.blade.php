@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-1">
    <h1 class="text-xl font-black tracking-tight text-stone-900">{{ $title }}</h1>
    <p class="text-sm text-stone-500">{{ $description }}</p>
</div>
