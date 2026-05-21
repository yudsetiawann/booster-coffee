<?php

use Livewire\Volt\Component;
use App\Models\Table;

new class extends Component {
    public $mejas;
    public $selectedMeja = null;
    public $showModal = false;

    public function mount(): void
    {
        $this->loadMejas();
    }

    public function loadMejas(): void
    {
        $this->mejas = Table::orderBy('zona')->orderBy('posisi_x')->get();
    }

    public function pilihMeja(int $id): void
    {
        $this->selectedMeja = Table::findOrFail($id);
        $this->showModal = true;
    }

    public function updateStatus(int $id, string $status): void
    {
        $meja = Table::findOrFail($id);
        $meja->update(['status' => $status]);
        $this->loadMejas();
        $this->showModal = false;
        $this->selectedMeja = null;
    }

    public function tutupModal(): void
    {
        $this->showModal = false;
        $this->selectedMeja = null;
    }

    // DISESUAIKAN DENGAN TEMA MODERN COFFEE
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'tersedia' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-400',
            'terisi' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400',
            'pesanan_masuk' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400',
            'perlu_dibersihkan' => 'border-stone-200 bg-stone-100 text-stone-600 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-400',
            default => 'border-stone-200 bg-white text-stone-800 dark:border-stone-800 dark:bg-stone-900 dark:text-stone-300',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'tersedia' => 'Tersedia',
            'terisi' => 'Terisi',
            'pesanan_masuk' => 'Pesanan Masuk',
            'perlu_dibersihkan' => 'Perlu Dibersihkan',
            default => '-',
        };
    }

    public function updatePosisi(int $id, int $posisi_x, int $posisi_y): void
    {
        Table::findOrFail($id)->update([
            'posisi_x' => $posisi_x,
            'posisi_y' => $posisi_y,
        ]);
        $this->loadMejas();
    }

    public function getZonaLabel(string $zona): string
    {
        return match ($zona) {
            'rooftop_a' => 'Rooftop A',
            'rooftop_b' => 'Rooftop B',
            'indoor' => 'Indoor',
            'bangku' => 'Bangku',
            'lesehan' => 'Lesehan',
            default => $zona,
        };
    }
}; ?>

<div class="space-y-6">

    {{-- Header & Legend --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-stone-900 dark:text-stone-100">Manajemen Meja</h1>
            <p class="text-sm text-stone-500 mt-1">Booster Coffee — Total Kapasitas 140 Pax</p>
        </div>

        <div
            class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-2 border border-stone-200 shadow-sm dark:bg-stone-900 dark:border-stone-800">
            <div class="flex items-center gap-1.5 px-2">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                <span
                    class="text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">Tersedia</span>
            </div>
            <div class="hidden sm:block w-px h-4 bg-stone-200 dark:bg-stone-700"></div>
            <div class="flex items-center gap-1.5 px-2">
                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                <span
                    class="text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">Terisi</span>
            </div>
            <div class="hidden sm:block w-px h-4 bg-stone-200 dark:bg-stone-700"></div>
            <div class="flex items-center gap-1.5 px-2">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">Pesanan
                    Masuk</span>
            </div>
            <div class="hidden sm:block w-px h-4 bg-stone-200 dark:bg-stone-700"></div>
            <div class="flex items-center gap-1.5 px-2">
                <span class="h-2.5 w-2.5 rounded-full bg-stone-400"></span>
                <span
                    class="text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">Pembersihan</span>
            </div>
        </div>
    </div>

    {{-- Denah per Zona --}}
    @php
        $zonas = ['rooftop_b', 'indoor', 'bangku', 'lesehan', 'rooftop_a'];
    @endphp

    <div class="grid grid-cols-1 gap-6">
        @foreach ($zonas as $zona)
            @php
                $mejasZona = $mejas->where('zona', $zona)->values();
            @endphp

            @if ($mejasZona->count() > 0)
                <div
                    class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm dark:bg-stone-900 dark:border-stone-800">
                    <div class="mb-4 flex items-center gap-3 border-b border-stone-100 pb-3 dark:border-stone-800">
                        <h2 class="text-sm font-black uppercase tracking-widest text-amber-700 dark:text-amber-500">
                            {{ $this->getZonaLabel($zona) }}
                        </h2>
                        <span
                            class="rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-bold text-stone-500 dark:bg-stone-800">
                            {{ $mejasZona->count() }} Meja
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-3"
                        data-zona="{{ $zona }}">
                        @foreach ($mejasZona as $meja)
                            <button type="button" wire:key="btn-meja-{{ $meja->id }}"
                                wire:click="pilihMeja({{ $meja->id }})" data-mejaid="{{ $meja->id }}"
                                class="group relative flex min-h-22.5 flex-col items-center justify-center rounded-xl border-2 p-3 text-center transition-all hover:scale-[1.02] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 {{ $this->getStatusColor($meja->status) }}">

                                <span
                                    class="text-lg font-black tracking-tight leading-none mb-1">{{ $meja->nama_meja }}</span>
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider opacity-60 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    {{ $meja->kapasitas }} Pax
                                </span>

                                <div
                                    class="absolute inset-x-0 bottom-0 translate-y-1/2 opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100">
                                    <span
                                        class="inline-block rounded-full bg-stone-900 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm dark:bg-white dark:text-stone-900">
                                        Update Status
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Modal Update Status --}}
    @if ($showModal && $selectedMeja)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl dark:bg-stone-900">
                <div class="mb-6 text-center">
                    <div
                        class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-900/30">
                        <svg class="h-7 w-7 text-amber-600 dark:text-amber-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-stone-900 dark:text-white">Ubah Status Meja</h3>
                    <p class="mt-1 text-sm font-medium text-stone-500">
                        {{ $selectedMeja->nama_meja }} &mdash; Kapasitas {{ $selectedMeja->kapasitas }} pax
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-6">
                    <button type="button" wire:click="updateStatus({{ $selectedMeja->id }}, 'tersedia')"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-emerald-50 px-4 py-4 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 transition-colors focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-950/30 dark:border-emerald-900/50 dark:text-emerald-400">
                        <span class="h-3 w-3 rounded-full bg-emerald-500 mb-1"></span>
                        <span class="text-sm font-bold">Tersedia</span>
                    </button>

                    <button type="button" wire:click="updateStatus({{ $selectedMeja->id }}, 'pesanan_masuk')"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-amber-50 px-4 py-4 border border-amber-200 text-amber-700 hover:bg-amber-100 transition-colors focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:bg-amber-950/30 dark:border-amber-900/50 dark:text-amber-400">
                        <span class="h-3 w-3 rounded-full bg-amber-500 mb-1"></span>
                        <span class="text-sm font-bold">Pesanan Masuk</span>
                    </button>

                    <button type="button" wire:click="updateStatus({{ $selectedMeja->id }}, 'terisi')"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-red-50 px-4 py-4 border border-red-200 text-red-700 hover:bg-red-100 transition-colors focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-950/30 dark:border-red-900/50 dark:text-red-400">
                        <span class="h-3 w-3 rounded-full bg-red-500 mb-1"></span>
                        <span class="text-sm font-bold">Terisi</span>
                    </button>

                    <button type="button" wire:click="updateStatus({{ $selectedMeja->id }}, 'perlu_dibersihkan')"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-stone-100 px-4 py-4 border border-stone-200 text-stone-600 hover:bg-stone-200 transition-colors focus:ring-2 focus:ring-stone-500 focus:ring-offset-2 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-400">
                        <span class="h-3 w-3 rounded-full bg-stone-500 mb-1"></span>
                        <span class="text-sm font-bold text-center leading-tight">Perlu Dibersihkan</span>
                    </button>
                </div>

                <button type="button" wire:click="tutupModal"
                    class="w-full rounded-xl bg-stone-900 px-4 py-3 text-sm font-bold text-white hover:bg-stone-800 transition-all dark:bg-white dark:text-stone-900 dark:hover:bg-stone-200">
                    Batal & Tutup
                </button>
            </div>
        </div>
    @endif

    {{-- Drag and Drop Script --}}
    <script>
        function initSortable() {
            if (typeof Sortable === 'undefined') {
                setTimeout(initSortable, 100);
                return;
            }

            document.querySelectorAll('[data-zona]').forEach(container => {
                if (container._sortable) {
                    container._sortable.destroy();
                }
                container._sortable = new Sortable(container, {
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: function(evt) {
                        const mejaId = evt.item.dataset.mejaid;
                        const newIndex = evt.newIndex;
                        @this.call('updatePosisi', parseInt(mejaId), newIndex, 0);
                    }
                });
            });
        }

        document.addEventListener('livewire:navigated', initSortable);
        document.addEventListener('livewire:initialized', initSortable);
        window.addEventListener('load', initSortable);
    </script>
</div>
