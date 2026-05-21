<?php

use Livewire\Volt\Component;
use App\Models\Table;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public ?int $selectedMejaId = null;
    public array $keranjang = [];
    public string $catatanUmum = '';
    public string $search = '';
    public string $filterKategori = '';
    public bool $showBerhasil = false;

    public array $kategoriList = [
        'kopi' => 'Kopi',
        'non_kopi' => 'Non Kopi',
        'main_course' => 'Main Course',
        'snack' => 'Snack',
        'pasta' => 'Pasta',
        'dessert' => 'Dessert',
        'tea' => 'Tea',
        'others' => 'Others',
    ];

    public function pilihMeja(int $id): void
    {
        $this->selectedMejaId = $id;
        $this->keranjang = [];
        $this->catatanUmum = '';
    }

    public function tambahKeKeranjang(int $menuId): void
    {
        if (isset($this->keranjang[$menuId])) {
            $this->keranjang[$menuId]['qty']++;
        } else {
            $menu = Menu::findOrFail($menuId);
            $this->keranjang[$menuId] = [
                'menu_id' => $menu->id,
                'nama' => $menu->nama_menu,
                'harga' => $menu->harga,
                'qty' => 1,
                'catatan' => '',
            ];
        }
    }

    public function kurangiQty(int $menuId): void
    {
        if (isset($this->keranjang[$menuId])) {
            if ($this->keranjang[$menuId]['qty'] <= 1) {
                unset($this->keranjang[$menuId]);
            } else {
                $this->keranjang[$menuId]['qty']--;
            }
        }
    }

    public function hapusDariKeranjang(int $menuId): void
    {
        unset($this->keranjang[$menuId]);
    }

    public function getTotalProperty(): int
    {
        return collect($this->keranjang)->sum(fn($item) => $item['harga'] * $item['qty']);
    }

    public function simpanPesanan(): void
    {
        if (!$this->selectedMejaId || empty($this->keranjang)) {
            return;
        }

        // Pre-load semua resep sekaligus untuk seluruh menu di keranjang — cegah N+1
        $menuIds = array_column($this->keranjang, 'menu_id');
        $resepMap = Recipe::with('ingredient')->whereIn('menu_id', $menuIds)->get()->groupBy('menu_id');

        DB::transaction(function () use ($resepMap) {
            $order = Order::create([
                'table_id' => $this->selectedMejaId,
                'kasir_id' => auth()->id(),
                'status' => 'menunggu_pembayaran',
                'catatan_umum' => $this->catatanUmum,
            ]);

            foreach ($this->keranjang as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $item['menu_id'],
                    'qty' => $item['qty'],
                    'harga_saat_pesan' => $item['harga'],
                    'catatan' => $item['catatan'],
                    'status' => 'menunggu_pembayaran',
                ]);

                // Kurangi stok bahan baku — semua resep sudah ada di $resepMap (no N+1)
                $reseps = $resepMap->get($item['menu_id'], collect());
                foreach ($reseps as $resep) {
                    $kebutuhan = $resep->jumlah_pakai * $item['qty'];

                    // Cegah stok negatif
                    if ($resep->ingredient->stok_saat_ini < $kebutuhan) {
                        Log::warning('Stok bahan tidak mencukupi saat order dibuat', [
                            'ingredient_id' => $resep->ingredient_id,
                            'nama_bahan' => $resep->ingredient->nama_bahan,
                            'stok_tersedia' => $resep->ingredient->stok_saat_ini,
                            'kebutuhan' => $kebutuhan,
                            'kasir_id' => auth()->id(),
                        ]);
                    }

                    $resep->ingredient->decrement('stok_saat_ini', $kebutuhan);
                }
            }

            Table::findOrFail($this->selectedMejaId)->update(['status' => 'pesanan_masuk']);

            Log::info('Pesanan baru dibuat', [
                'order_id' => $order->id,
                'kasir_id' => auth()->id(),
                'table_id' => $this->selectedMejaId,
                'items_count' => count($this->keranjang),
                'total' => $this->total,
            ]);
        });

        $this->keranjang = [];
        $this->catatanUmum = '';
        $this->selectedMejaId = null;
        $this->showBerhasil = true;
    }

    public function with(): array
    {
        $mejas = Table::orderBy('zona')->orderBy('posisi_x')->get();

        $menus = Menu::query()->where('tersedia', true)->when($this->search, fn($q) => $q->where('nama_menu', 'like', "%{$this->search}%"))->when($this->filterKategori, fn($q) => $q->where('kategori', $this->filterKategori))->orderBy('kategori')->orderBy('nama_menu')->get();

        return compact('mejas', 'menus');
    }
}; ?>

<div class="relative">
    {{-- Notifikasi Berhasil (Floating Alert) --}}
    @if ($showBerhasil)
        <div
            class="fixed top-6 right-6 z-50 flex items-center gap-3 rounded-xl bg-green-500 px-5 py-4 text-white shadow-xl animate-in fade-in slide-in-from-top-4">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <div class="flex-1">
                <h4 class="text-sm font-bold">Pesanan Terkirim!</h4>
                <p class="text-xs text-green-100">Pesanan disimpan. Lanjut ke pembayaran.</p>
            </div>
            <button wire:click="$set('showBerhasil', false)" class="ml-4 text-white hover:text-green-200">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6 items-start">

        {{-- Kolom Kiri: Area Produk (70%) --}}
        <div class="flex-1 w-full min-w-0 space-y-6">

            {{-- 1. Pemilihan Meja --}}
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-sm">
                <h2 class="text-base font-bold text-zinc-900 dark:text-white mb-4">Pilih Meja / Pelanggan</h2>
                <div class="flex gap-4 overflow-x-auto pb-2 snap-x hide-scrollbar">
                    @foreach ($mejas->groupBy('zona') as $zona => $mejasZona)
                        <div class="shrink-0 snap-start">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-widest text-zinc-400">
                                {{ match ($zona) {
                                    'rooftop_a' => 'Rooftop A',
                                    'rooftop_b' => 'Rooftop B',
                                    'indoor' => 'Indoor',
                                    'bangku' => 'Bangku',
                                    'lesehan' => 'Lesehan',
                                    default => $zona,
                                } }}
                            </p>
                            <div class="flex gap-2">
                                @foreach ($mejasZona as $meja)
                                    <button type="button" wire:key="meja-btn-{{ $meja->id }}"
                                        wire:click="pilihMeja({{ $meja->id }})"
                                        @disabled($meja->status !== 'tersedia')
                                        class="min-w-16 rounded-xl border px-3 py-2.5 text-sm font-semibold transition-all duration-200
                                        {{ $selectedMejaId === $meja->id
                                            ? 'bg-zinc-900 border-zinc-900 text-white shadow-md dark:bg-white dark:text-zinc-900 dark:border-white'
                                            : ($meja->status === 'tersedia'
                                                ? 'bg-white border-zinc-200 text-zinc-700 hover:border-zinc-400 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300'
                                                : 'bg-red-50 border-red-200 text-red-600 opacity-60 cursor-not-allowed dark:bg-red-950/30 dark:border-red-900/50') }}">
                                        {{ $meja->nama_meja }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 2. Pencarian & Filter Kategori --}}
            <div class="flex flex-col sm:flex-row gap-4 items-center">
                <div class="relative w-full sm:w-64 shrink-0">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-zinc-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live="search" type="text" placeholder="Cari menu..."
                        class="w-full rounded-xl border border-zinc-200 bg-white pl-10 pr-4 py-2.5 text-sm text-zinc-900 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 dark:bg-zinc-900 dark:border-zinc-800 dark:text-white dark:focus:border-white dark:focus:ring-white transition-colors" />
                </div>

                {{-- Horizontal Pills untuk Kategori --}}
                <div class="flex w-full overflow-x-auto gap-2 pb-1 hide-scrollbar">
                    <button wire:click="$set('filterKategori', '')"
                        class="whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-medium transition-colors
                        {{ $filterKategori === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                        Semua
                    </button>
                    @foreach ($this->kategoriList as $key => $label)
                        <button wire:click="$set('filterKategori', '{{ $key }}')"
                            class="whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-medium transition-colors
                            {{ $filterKategori === $key ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- 3. Grid Menu Bergaya Kartu --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 pb-10">
                @forelse($menus as $menu)
                    <button type="button" wire:key="menu-btn-{{ $menu->id }}"
                        wire:click="tambahKeKeranjang({{ $menu->id }})"
                        @if (!$selectedMejaId) disabled @endif
                        class="group relative flex flex-col items-start justify-between rounded-2xl border border-zinc-200 bg-white p-4 text-left shadow-sm transition-all duration-200
                        {{ $selectedMejaId ? 'hover:border-zinc-900 hover:shadow-md dark:hover:border-white' : 'opacity-60 cursor-not-allowed' }} dark:bg-zinc-900 dark:border-zinc-800">

                        {{-- Jika ada gambar produk nanti, tempatkan div bg-cover di sini --}}
                        <div
                            class="mb-4 h-12 w-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 group-hover:bg-zinc-900 group-hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white leading-tight mb-1">
                                {{ $menu->nama_menu }}</h3>
                            <p class="text-xs font-medium text-zinc-500">Rp
                                {{ number_format($menu->harga, 0, ',', '.') }}</p>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <p class="text-sm text-zinc-500">Tidak ada menu yang sesuai.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Kolom Kanan: Panel Keranjang Sticky (30%) --}}
        <div class="w-full lg:w-95 shrink-0 sticky top-24">
            <div
                class="flex flex-col h-[calc(100vh-8rem)] rounded-3xl border border-zinc-200 bg-white shadow-xl dark:bg-zinc-900 dark:border-zinc-800 overflow-hidden">

                {{-- Header Keranjang --}}
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950/50">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Pesanan Saat Ini</h2>
                    @if ($selectedMejaId)
                        <div
                            class="mt-2 inline-flex items-center gap-1.5 rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                            {{ $mejas->firstWhere('id', $selectedMejaId)?->nama_meja }}
                        </div>
                    @else
                        <p class="mt-1 text-xs text-red-500 font-medium">Belum ada meja dipilih</p>
                    @endif
                </div>

                {{-- List Item (Scrollable) --}}
                <div class="flex-1 overflow-y-auto p-2 hide-scrollbar">
                    @if (empty($keranjang))
                        <div
                            class="flex h-full flex-col items-center justify-center text-center p-6 space-y-3 opacity-50">
                            <svg class="h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <p class="text-sm font-medium text-zinc-500">Keranjang masih kosong.</p>
                        </div>
                    @else
                        <div class="space-y-1">
                            @foreach ($keranjang as $menuId => $item)
                                <div
                                    class="rounded-xl p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="pr-2">
                                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">
                                                {{ $item['nama'] }}</h4>
                                            <p class="text-xs text-zinc-500">Rp
                                                {{ number_format($item['harga'], 0, ',', '.') }}</p>
                                        </div>
                                        <p class="text-sm font-bold text-zinc-900 dark:text-white shrink-0">
                                            Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <input wire:model="keranjang.{{ $menuId }}.catatan" type="text"
                                            placeholder="Catatan..."
                                            class="w-3/5 rounded-lg border-none bg-zinc-100 px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-zinc-400 dark:bg-zinc-800 dark:text-white placeholder-zinc-400" />

                                        <div
                                            class="flex items-center rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                                            <button wire:click="kurangiQty({{ $menuId }})"
                                                class="px-2.5 py-1 text-zinc-500 hover:text-zinc-900 dark:hover:text-white">−</button>
                                            <span
                                                class="px-2 text-sm font-bold text-zinc-900 dark:text-white min-w-6 text-center">{{ $item['qty'] }}</span>
                                            <button wire:click="tambahKeKeranjang({{ $menuId }})"
                                                class="px-2.5 py-1 text-zinc-500 hover:text-zinc-900 dark:hover:text-white">+</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Footer Keranjang (Total & Checkout) --}}
                @if (!empty($keranjang))
                    <div class="border-t border-zinc-100 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <textarea wire:model="catatanUmum" rows="1" placeholder="Catatan pesanan keseluruhan..."
                            class="mb-4 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 dark:bg-zinc-800 dark:border-zinc-700 text-zinc-900 dark:text-white resize-none"></textarea>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium text-zinc-500">Total Tagihan</span>
                            <span class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Rp
                                {{ number_format($this->total, 0, ',', '.') }}</span>
                        </div>

                        <button wire:click="simpanPesanan" wire:loading.attr="disabled"
                            class="relative flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-3.5 text-sm font-bold text-white shadow-lg transition-all hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                            <span wire:loading.remove wire:target="simpanPesanan">Kirim ke Dapur</span>
                            <span wire:loading wire:target="simpanPesanan">Memproses...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
