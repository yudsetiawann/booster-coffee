<?php

use Livewire\Volt\Component;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public string $filter = 'hari';
    public string $tanggal = '';

    public function mount(): void
    {
        $this->tanggal = now()->format('Y-m-d');
    }

    public function getDateRangeProperty(): array
    {
        return match ($this->filter) {
            'hari' => [now()->startOfDay(), now()->endOfDay()],
            'minggu' => [now()->startOfWeek(), now()->endOfWeek()],
            'bulan' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    public function with(): array
    {
        [$start, $end] = $this->dateRange;

        // Total pendapatan
        $totalPendapatan = Payment::whereBetween('created_at', [$start, $end])->sum('jumlah_bayar');

        // Total transaksi
        $totalTransaksi = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'selesai')
            ->count();

        // Menu terlaris
        $menuTerlaris = \App\Models\OrderItem::with('menu')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('menu_id, SUM(qty) as total_qty')
            ->groupBy('menu_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Performa kasir
        $pendapatanKasir = Order::with('kasir')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'selesai')
            ->selectRaw('kasir_id, COUNT(*) as total_order')
            ->groupBy('kasir_id')
            ->get();

        // Grafik per jam — menggunakan PHP groupBy agar kompatibel dengan SQLite & MySQL
        $grafikHarian = Payment::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->get(['created_at', 'jumlah_bayar'])
            ->groupBy(fn($p) => (int) $p->created_at->format('G')) // 0-23, no leading zero
            ->map(fn($group) => $group->sum('jumlah_bayar'));

        $grafikData = collect(range(0, 23))->map(
            fn($jam) => [
                'jam' => str_pad($jam, 2, '0', STR_PAD_LEFT) . ':00',
                'total' => $grafikHarian->get($jam, 0),
            ],
        );

        // Order terbaru
        $orderTerbaru = Order::with(['table', 'items'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Stok menipis — dipindah ke sini dari @php di template agar tidak jadi query liar
        $stokMenipis = Ingredient::whereColumn('stok_saat_ini', '<=', 'stok_minimum')->get();

        return compact('totalPendapatan', 'totalTransaksi', 'menuTerlaris', 'pendapatanKasir', 'grafikData', 'orderTerbaru', 'stokMenipis');
    }
}; ?>

<div class="space-y-6">

    {{-- Notifikasi Stok Menipis --}}
    @if ($stokMenipis->isNotEmpty())
        <div class="rounded-2xl border-l-4 border-red-500 bg-red-50 p-4">
            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-red-700">Peringatan: Stok Menipis</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($stokMenipis as $bahan)
                    <span
                        class="inline-block rounded-full border border-red-200 bg-white px-3 py-0.5 text-xs font-semibold text-red-700">
                        {{ $bahan->nama_bahan }}: {{ $bahan->stok_saat_ini }} {{ $bahan->satuan }}
                        (min: {{ $bahan->stok_minimum }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-stone-900">Laporan Penjualan</h1>
            <p class="text-sm text-stone-500 mt-1">Dashboard real-time Booster Coffee</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach (['hari' => 'Hari Ini', 'minggu' => 'Minggu Ini', 'bulan' => 'Bulan Ini'] as $key => $label)
                <button wire:click="$set('filter', '{{ $key }}')"
                    class="rounded-lg px-4 py-1.5 text-sm font-semibold transition
                    {{ $filter === $key
                        ? 'bg-amber-600 text-white'
                        : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-1 gap-px bg-stone-200 sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-stone-500">Total Pendapatan</p>
            <p class="mt-2 text-2xl font-black text-amber-700">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-stone-400">{{ ucfirst($filter) }} ini</p>
        </div>
        <div class="bg-white p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-stone-500">Total Transaksi</p>
            <p class="mt-2 text-2xl font-black text-stone-900">{{ $totalTransaksi }}</p>
            <p class="mt-1 text-xs text-stone-400">Order selesai</p>
        </div>
        <div class="bg-white p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-stone-500">Menu Terlaris</p>
            <p class="mt-2 text-lg font-black text-stone-900 truncate">
                {{ $menuTerlaris->first()?->menu?->nama_menu ?? '—' }}
            </p>
            <p class="mt-1 text-xs text-stone-400">{{ $menuTerlaris->first()?->total_qty ?? 0 }} porsi terjual</p>
        </div>
        <div class="bg-white p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-stone-500">Rata-rata per Transaksi</p>
            <p class="mt-2 text-2xl font-black text-amber-700">
                Rp {{ $totalTransaksi > 0 ? number_format($totalPendapatan / $totalTransaksi, 0, ',', '.') : '0' }}
            </p>
            <p class="mt-1 text-xs text-stone-400">Per order</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Grafik Penjualan Harian --}}
        <div class="xl:col-span-2 rounded-xl border border-amber-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-primary-dark">Grafik Penjualan Hari Ini (per Jam)</h2>
            <div class="relative h-48">
                <canvas id="grafikPenjualan"></canvas>
            </div>
        </div>

        {{-- Menu Terlaris --}}
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-black uppercase tracking-wider text-stone-900">Top 5 Menu Terlaris</h2>
            <div class="space-y-4">
                @forelse($menuTerlaris as $index => $item)
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-xs font-black text-white">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-stone-900 truncate">
                                {{ $item->menu?->nama_menu ?? '—' }}</p>
                            <div class="mt-1.5 h-1.5 w-full rounded-full bg-stone-100">
                                <div class="h-1.5 rounded-full bg-amber-500"
                                    style="width: {{ $menuTerlaris->first()->total_qty > 0 ? ($item->total_qty / $menuTerlaris->first()->total_qty) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        <span class="text-sm font-black text-amber-700 shrink-0">{{ $item->total_qty }}x</span>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-stone-400">Belum ada data.</p>
                @endforelse
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- Order Terbaru --}}
        <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-stone-100 px-5 py-4">
                <h2 class="text-xs font-black uppercase tracking-wider text-stone-900">Order Terbaru</h2>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse($orderTerbaru as $order)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-semibold text-stone-900">
                                #{{ $order->id }} &mdash; {{ $order->table?->nama_meja }}
                            </p>
                            <p class="text-xs text-stone-400">{{ $order->items->count() }} item &middot;
                                {{ $order->created_at->format('H:i') }}</p>
                        </div>
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-bold
                            {{ $order->status === 'selesai'
                                ? 'bg-emerald-100 text-emerald-700'
                                : ($order->status === 'diproses'
                                    ? 'bg-amber-100 text-amber-700'
                                    : 'bg-red-100 text-red-700') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-stone-400">Belum ada order.</p>
                @endforelse
            </div>
        </div>

        {{-- Performa Kasir --}}
        <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-stone-100 px-5 py-4">
                <h2 class="text-xs font-black uppercase tracking-wider text-stone-900">Performa Kasir</h2>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse($pendapatanKasir as $item)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-600 text-sm font-black text-white">
                                {{ strtoupper(substr($item->kasir?->name ?? '?', 0, 1)) }}
                            </div>
                            <p class="text-sm font-semibold text-stone-900">{{ $item->kasir?->name ?? 'Unknown' }}</p>
                        </div>
                        <span class="text-sm font-black text-amber-700">{{ $item->total_order }} order</span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-stone-400">Belum ada data kasir.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Chart.js Script --}}
    <script>
        function initChart() {
            if (typeof Chart === 'undefined') {
                setTimeout(initChart, 100);
                return;
            }

            const labels = @json($grafikData->pluck('jam'));
            const data = @json($grafikData->pluck('total'));

            const ctx = document.getElementById('grafikPenjualan');
            if (!ctx) return;

            if (ctx._chartInstance) {
                ctx._chartInstance.destroy();
            }

            ctx._chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: data,
                        backgroundColor: 'rgba(196, 154, 74, 0.7)',
                        borderColor: 'rgba(196, 154, 74, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                },
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 9
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }   
            });
        }

        document.addEventListener('livewire:navigated', initChart);
        document.addEventListener('livewire:initialized', initChart);
        window.addEventListener('load', initChart);
    </script>
</div>
