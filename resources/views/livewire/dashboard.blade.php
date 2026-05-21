<?php

use Livewire\Volt\Component;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\Ingredient;

new class extends Component {
    public string $filter = 'hari';

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

        $totalPendapatan = Payment::whereBetween('created_at', [$start, $end])->sum('jumlah_bayar');
        $totalTransaksi = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'selesai')
            ->count();
        $stokMenipis = Ingredient::whereColumn('stok_saat_ini', '<=', 'stok_minimum')->get();
        $orderTerbaru = Order::with(['table', 'items'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $mejas = Table::orderBy('zona')->orderBy('posisi_x')->get();
        $orderAktif = Order::with(['table', 'items.menu'])
            ->whereIn('status', ['pending', 'diproses'])
            ->orderBy('created_at')
            ->get();

        $antrianDapur = Order::with(['table', 'items.menu'])
            ->whereIn('status', ['pending', 'diproses'])
            ->orderBy('created_at')
            ->limit(6)
            ->get();

        return compact('totalPendapatan', 'totalTransaksi', 'stokMenipis', 'orderTerbaru', 'mejas', 'orderAktif', 'antrianDapur');
    }
}; ?>

<div class="space-y-8">

    {{-- ── DASHBOARD ADMIN ─────────────────────────────────────────────── --}}
    @hasrole('admin')

        @if ($stokMenipis->isNotEmpty())
            <div class="rounded-2xl border border-red-200 bg-red-50/50 p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm font-bold uppercase tracking-wide text-red-700">Peringatan: Stok Menipis</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($stokMenipis as $bahan)
                        <span wire:key="stok-{{ $bahan->id }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            {{ $bahan->nama_bahan }}: {{ $bahan->stok_saat_ini }} {{ $bahan->satuan }}
                            <span class="text-red-400 font-medium">(min: {{ $bahan->stok_minimum }})</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">Ikhtisar Bisnis</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Ringkasan operasional harian Booster Coffee.</p>
            </div>
            <div class="flex shrink-0 gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                @foreach (['hari' => 'Hari Ini', 'minggu' => 'Minggu Ini', 'bulan' => 'Bulan Ini'] as $key => $label)
                    <button type="button" wire:key="filter-{{ $key }}"
                        wire:click="$set('filter', '{{ $key }}')"
                        class="rounded-lg px-4 py-1.5 text-sm font-semibold transition-all
                        {{ $filter === $key
                            ? 'bg-white text-primary shadow-sm dark:bg-zinc-900 dark:text-white'
                            : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Pendapatan</p>
                <p class="mt-3 text-3xl font-black text-primary dark:text-white tracking-tight">Rp
                    {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                <p
                    class="mt-1 text-xs font-medium text-emerald-600 bg-emerald-50 inline-block px-2 py-0.5 rounded-md dark:bg-emerald-950/30 dark:text-emerald-400">
                    {{ ucfirst($filter) }} ini</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Transaksi</p>
                <p class="mt-3 text-3xl font-black text-primary dark:text-white tracking-tight">{{ $totalTransaksi }}</p>
                <p class="mt-1 text-xs font-medium text-zinc-500">Order diselesaikan</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Meja Aktif</p>
                <p class="mt-3 text-3xl font-black text-primary dark:text-white tracking-tight">
                    {{ $mejas->whereIn('status', ['terisi', 'pesanan_masuk'])->count() }} <span
                        class="text-lg font-medium text-zinc-400">/ {{ $mejas->count() }}</span>
                </p>
                <p class="mt-1 text-xs font-medium text-zinc-500">Kapasitas terpakai</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Antrian Dapur</p>
                <p class="mt-3 text-3xl font-black text-amber-600 tracking-tight">{{ $antrianDapur->count() }}</p>
                <p class="mt-1 text-xs font-medium text-zinc-500">Order menunggu diproses</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            {{-- Panel Order Terbaru --}}
            <div
                class="rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden dark:bg-zinc-900 dark:border-zinc-800">
                <div class="border-b border-zinc-100 bg-zinc-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950/50">
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Order Terbaru</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($orderTerbaru as $order)
                        <div wire:key="admin-order-{{ $order->id }}"
                            class="flex items-center justify-between px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <div>
                                <p class="text-sm font-bold text-zinc-900 dark:text-white">
                                    <span class="text-zinc-400">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    &bull; {{ $order->table?->nama_meja }}
                                </p>
                                <p class="text-xs font-medium text-zinc-500 mt-0.5">
                                    {{ $order->items->count() }} item &mdash; {{ $order->created_at->format('H:i') }}
                                </p>
                            </div>
                            <span
                                class="rounded-lg px-2.5 py-1 text-xs font-bold tracking-wide
                                {{ $order->status === 'selesai'
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400'
                                    : ($order->status === 'diproses'
                                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400'
                                        : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center">
                            <p class="text-sm font-medium text-zinc-500">Belum ada order hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Panel Status Meja --}}
            <div
                class="rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden dark:bg-zinc-900 dark:border-zinc-800">
                <div class="border-b border-zinc-100 bg-zinc-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950/50">
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Status Meja Terkini</h2>
                </div>
                <div class="flex flex-wrap gap-2.5 p-6">
                    @foreach ($mejas as $meja)
                        <div wire:key="admin-meja-{{ $meja->id }}"
                            class="flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-bold transition-all
                            {{ $meja->status === 'tersedia'
                                ? 'border-zinc-200 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'
                                : ($meja->status === 'terisi'
                                    ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400'
                                    : ($meja->status === 'pesanan_masuk'
                                        ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400'
                                        : 'border-zinc-200 bg-zinc-100 text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900')) }}">
                            <span
                                class="h-2 w-2 rounded-full
                                {{ $meja->status === 'tersedia' ? 'bg-emerald-500' : ($meja->status === 'terisi' ? 'bg-red-500' : 'bg-amber-500') }}">
                            </span>
                            {{ $meja->nama_meja }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    @endhasrole

    {{-- ── DASHBOARD KASIR ─────────────────────────────────────────────── --}}
    @hasrole('kasir')
        {{-- Logika & Tampilan khusus kasir yang sudah disederhanakan mengikuti gaya yang sama. --}}
        <div>
            <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">Shift Kasir Aktif</h1>
            <p class="text-sm text-zinc-500 mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:gap-6 xl:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Tersedia</p>
                <p class="mt-2 text-3xl font-black text-emerald-600">{{ $mejas->where('status', 'tersedia')->count() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Terisi</p>
                <p class="mt-2 text-3xl font-black text-red-600">{{ $mejas->where('status', 'terisi')->count() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Pesanan Masuk</p>
                <p class="mt-2 text-3xl font-black text-amber-600">{{ $mejas->where('status', 'pesanan_masuk')->count() }}
                </p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Pembersihan</p>
                <p class="mt-2 text-3xl font-black text-zinc-400">
                    {{ $mejas->where('status', 'perlu_dibersihkan')->count() }}</p>
            </div>
        </div>

        {{-- Denah Cepat --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-zinc-100 bg-zinc-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-zinc-900">Jalan Pintas Manajemen Meja</h2>
            </div>
            <div class="flex flex-wrap gap-2.5 p-6">
                @foreach ($mejas as $meja)
                    <a href="{{ route('meja.index') }}" wire:navigate wire:key="shortcut-meja-{{ $meja->id }}"
                        class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition-all hover:scale-105 hover:shadow-md
                        {{ $meja->status === 'tersedia'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : ($meja->status === 'terisi'
                                ? 'border-red-200 bg-red-50 text-red-700'
                                : ($meja->status === 'pesanan_masuk'
                                    ? 'border-amber-200 bg-amber-50 text-amber-700'
                                    : 'border-zinc-200 bg-zinc-100 text-zinc-500')) }}">
                        {{ $meja->nama_meja }}
                    </a>
                @endforeach
            </div>
        </div>
    @endhasrole

    {{-- ── DASHBOARD DAPUR ─────────────────────────────────────────────── --}}
    @hasrole('dapur')
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">Stasiun Dapur</h1>
                <p class="text-sm text-zinc-500 mt-1">Status terkini &mdash; {{ now()->format('H:i') }}</p>
            </div>
            <a href="{{ route('kds.index') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-zinc-800 transition-all dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                Buka Layar KDS
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
            <div
                class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/30">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-500">Antrian Menunggu
                </p>
                <p class="mt-2 text-4xl font-black text-amber-600 dark:text-amber-400">{{ $antrianDapur->count() }}</p>
            </div>
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/30">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-500">Sedang Dimasak
                </p>
                <p class="mt-2 text-4xl font-black text-emerald-600 dark:text-emerald-400">
                    {{ $antrianDapur->where('status', 'diproses')->count() }}</p>
            </div>
        </div>
    @endhasrole
</div>
