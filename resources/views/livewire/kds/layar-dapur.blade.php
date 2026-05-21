<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Poll;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

new class extends Component {
    #[Poll(2000)]
    public function with(): array
    {
        $orders = Order::with(['table', 'items.menu'])
            ->whereIn('status', ['pending', 'diproses'])
            ->orderBy('created_at', 'asc')
            ->get();

        return compact('orders');
    }

    public function prosesItem(int $itemId): void
    {
        // Ambil item sekaligus dengan order_id-nya — tidak perlu query terpisah
        $item = OrderItem::findOrFail($itemId);
        $item->update(['status' => 'diproses']);

        Log::info('KDS: item mulai diproses', [
            'item_id' => $itemId,
            'order_id' => $item->order_id,
            'dapur_id' => auth()->id(),
        ]);

        $this->updateOrderStatus($item->order_id);
    }

    public function selesaikanItem(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update(['status' => 'selesai']);

        Log::info('KDS: item selesai', [
            'item_id' => $itemId,
            'order_id' => $item->order_id,
            'dapur_id' => auth()->id(),
        ]);

        $this->updateOrderStatus($item->order_id);
    }

    public function selesaikanOrder(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::with('items')->findOrFail($orderId);
            $order->items()->update(['status' => 'selesai']);
            $order->update(['status' => 'selesai']);
            $order->table()->update(['status' => 'perlu_dibersihkan']);

            Log::info('KDS: order selesai semua', [
                'order_id' => $orderId,
                'dapur_id' => auth()->id(),
            ]);
        });
    }

    private function updateOrderStatus(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::with('items')->findOrFail($orderId);
            $allSelesai = $order->items->every(fn($item) => $item->status === 'selesai');
            $anyDiproses = $order->items->contains(fn($item) => $item->status === 'diproses');

            if ($allSelesai) {
                $order->update(['status' => 'selesai']);
                $order->table()->update(['status' => 'perlu_dibersihkan']);
            } elseif ($anyDiproses) {
                $order->update(['status' => 'diproses']);
                $order->table()->update(['status' => 'terisi']);
            }
        });
    }
}; ?>

<div class="min-h-screen bg-zinc-950 p-4 md:p-6 lg:p-8 font-sans selection:bg-emerald-500 selection:text-white">

    {{-- Header --}}
    <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-white">Kitchen Display</h1>
            <div class="mt-1 flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <p class="text-sm font-medium text-zinc-400">Live Sync (2s)</p>
            </div>
        </div>
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 px-5 py-3 shadow-sm">
            <p class="text-sm font-bold text-zinc-300 tracking-wide uppercase">{{ now()->translatedFormat('l, d M') }}
            </p>
            <p class="text-xl font-black text-white tabular-nums">{{ now()->format('H:i:s') }}</p>
        </div>
    </div>

    @if ($orders->isEmpty())
        <div
            class="flex h-[60vh] flex-col items-center justify-center rounded-3xl border-2 border-dashed border-zinc-800 bg-zinc-900/50 p-8 text-center">
            <div class="mb-4 rounded-full bg-zinc-800 p-4 text-zinc-500">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-1">Dapur Bersih!</h3>
            <p class="text-zinc-400">Semua pesanan telah diselesaikan.</p>
        </div>
    @else
        {{-- Kanban Grid --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 items-start">
            @foreach ($orders as $order)
                <div
                    class="relative flex flex-col rounded-2xl bg-zinc-900 border-2 overflow-hidden shadow-xl
                    {{ $order->status === 'diproses' ? 'border-amber-500/50' : 'border-zinc-800' }}">

                    {{-- Header Tiket --}}
                    <div class="px-5 py-4 {{ $order->status === 'diproses' ? 'bg-amber-500/10' : 'bg-zinc-800/50' }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-2xl font-black text-white leading-none mb-1">
                                    {{ $order->table->nama_meja }}</h2>
                                <p class="text-xs font-medium text-zinc-400">
                                    #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="inline-block rounded-md px-2 py-1 text-xs font-bold tracking-wider uppercase
                                    {{ $order->status === 'diproses' ? 'bg-amber-500 text-amber-950' : 'bg-zinc-700 text-zinc-200' }}">
                                    {{ $order->status === 'diproses' ? 'Memasak' : 'Baru' }}
                                </span>
                                <p class="mt-1.5 text-sm font-bold text-zinc-300 tabular-nums">
                                    {{ $order->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Item (Body Tiket) --}}
                    <div class="flex-1 p-3 space-y-2">
                        @foreach ($order->items as $item)
                            <div
                                class="flex items-start justify-between gap-3 rounded-xl p-3 transition-colors
                                {{ $item->status === 'selesai' ? 'bg-emerald-950/30 opacity-50' : ($item->status === 'diproses' ? 'bg-amber-950/30 border border-amber-900/50' : 'bg-zinc-800/50') }}">

                                <div class="flex-1">
                                    <p
                                        class="text-base font-bold leading-tight {{ $item->status === 'selesai' ? 'text-emerald-500 line-through' : 'text-white' }}">
                                        <span
                                            class="mr-1 text-lg font-black {{ $item->status === 'selesai' ? 'text-emerald-700' : 'text-amber-500' }}">{{ $item->qty }}x</span>
                                        {{ $item->menu->nama_menu }}
                                    </p>
                                    @if ($item->catatan)
                                        <p
                                            class="mt-1.5 inline-flex items-center gap-1 rounded bg-red-950/50 px-2 py-1 text-xs font-bold text-red-400">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ $item->catatan }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Aksi per Item --}}
                                <div class="shrink-0 pt-0.5">
                                    @if ($item->status === 'pending')
                                        <button wire:click="prosesItem({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-bold text-amber-950 shadow-sm hover:bg-amber-400 active:scale-95 transition-all">
                                            <span wire:loading.remove
                                                wire:target="prosesItem({{ $item->id }})">Mulai</span>
                                            <span wire:loading wire:target="prosesItem({{ $item->id }})">...</span>
                                        </button>
                                    @elseif($item->status === 'diproses')
                                        <button wire:click="selesaikanItem({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="rounded-lg bg-emerald-500 px-3 py-2 text-sm font-bold text-emerald-950 shadow-sm hover:bg-emerald-400 active:scale-95 transition-all">
                                            <span wire:loading.remove
                                                wire:target="selesaikanItem({{ $item->id }})">Ceklis</span>
                                            <span wire:loading
                                                wire:target="selesaikanItem({{ $item->id }})">...</span>
                                        </button>
                                    @else
                                        <svg class="h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if ($order->catatan_umum)
                            <div class="mt-2 rounded-xl border border-red-900/30 bg-red-950/20 p-3">
                                <p class="text-xs font-bold uppercase tracking-wider text-red-500 mb-1">Catatan Struk:
                                </p>
                                <p class="text-sm font-medium text-red-200">{{ $order->catatan_umum }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer Tiket: Tombol Raksasa Selesai Semua --}}
                    <div class="p-3 bg-zinc-900">
                        <button wire:click="selesaikanOrder({{ $order->id }})" wire:loading.attr="disabled"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-emerald-500/20 bg-emerald-500/10 px-4 py-3.5 text-base font-black text-emerald-500 hover:bg-emerald-500 hover:text-emerald-950 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-zinc-900 active:scale-[0.98] transition-all disabled:opacity-50">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span wire:loading.remove wire:target="selesaikanOrder({{ $order->id }})">TUTUP
                                TIKET</span>
                            <span wire:loading wire:target="selesaikanOrder({{ $order->id }})">MEMPROSES...</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
