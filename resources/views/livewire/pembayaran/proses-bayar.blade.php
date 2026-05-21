<?php

use Livewire\Volt\Component;
use App\Models\Table;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public ?int $selectedMejaId = null;
    public ?int $selectedOrderId = null;
    public string $metode = 'tunai';
    public int|string $jumlahBayar = '';
    public bool $isSplit = false;
    public string $namaPembayar = '';
    public bool $showBerhasil = false;
    public array $splitPembayaran = [];
    public int|string $splitJumlah = '';
    public string $splitNama = '';
    public string $splitMetode = 'tunai';

    public function pilihMeja(int $id): void
    {
        $this->selectedMejaId = $id;
        $this->selectedOrderId = null;
        $this->resetForm();
    }

    public function pilihOrder(int $id): void
    {
        $this->selectedOrderId = $id;
        $this->resetForm();
    }

    public function getOrderProperty(): ?Order
    {
        if (!$this->selectedOrderId) {
            return null;
        }
        return Order::with(['items.menu', 'table', 'payments'])->find($this->selectedOrderId);
    }

    public function getTotalProperty(): int
    {
        if (!$this->order) {
            return 0;
        }
        return $this->order->items->sum(fn($item) => $item->harga_saat_pesan * $item->qty);
    }

    public function getTotalTerbayarProperty(): int
    {
        if (!$this->order) {
            return 0;
        }
        return $this->order->payments->sum('jumlah_bayar');
    }

    public function getSisaProperty(): int
    {
        return max(0, $this->total - $this->totalTerbayar);
    }

    public function tambahSplitPembayaran(): void
    {
        $this->validate([
            'splitNama' => 'required|string|max:255',
            'splitJumlah' => 'required|numeric|min:1',
            'splitMetode' => 'required|in:tunai,transfer,qris',
        ]);

        $this->splitPembayaran[] = [
            'nama' => $this->splitNama,
            'jumlah' => (int) $this->splitJumlah,
            'metode' => $this->splitMetode,
        ];

        $this->splitNama = '';
        $this->splitJumlah = '';
        $this->splitMetode = 'tunai';
    }

    public function hapusSplit(int $index): void
    {
        unset($this->splitPembayaran[$index]);
        $this->splitPembayaran = array_values($this->splitPembayaran);
    }

    public function prosesBayar(): void
    {
        if (!$this->selectedOrderId) {
            return;
        }

        $order = Order::with('items')->findOrFail($this->selectedOrderId);

        // Guard: hanya order yang belum dibayar yang boleh diproses
        if ($order->status !== 'menunggu_pembayaran') {
            return;
        }

        if ($this->isSplit) {
            foreach ($this->splitPembayaran as $split) {
                Payment::create([
                    'order_id' => $this->selectedOrderId,
                    'jumlah_bayar' => $split['jumlah'],
                    'metode' => $split['metode'],
                    'is_split' => true,
                    'nama_pembayar' => $split['nama'],
                ]);
            }
        } else {
            $this->validate([
                'jumlahBayar' => 'required|numeric|min:1',
                'metode' => 'required|in:tunai,transfer,qris',
            ]);

            Payment::create([
                'order_id' => $this->selectedOrderId,
                'jumlah_bayar' => (int) $this->jumlahBayar,
                'metode' => $this->metode,
                'is_split' => false,
                'nama_pembayar' => $this->namaPembayar,
            ]);
        }

        // Setelah bayar: order & semua item → pending (siap diambil dapur)
        // Status meja → terisi (dapur sedang memproses)
        $order->update(['status' => 'pending']);
        $order->items()->update(['status' => 'pending']);
        $order->table()->update(['status' => 'terisi']);

        $this->resetForm();
        $this->selectedOrderId = null;
        $this->showBerhasil = true;
    }

    public function resetForm(): void
    {
        $this->metode = 'tunai';
        $this->jumlahBayar = '';
        $this->namaPembayar = '';
        $this->isSplit = false;
        $this->splitPembayaran = [];
        $this->splitNama = '';
        $this->splitJumlah = '';
        $this->splitMetode = 'tunai';
        $this->showBerhasil = false;
    }

    public function with(): array
    {
        $mejas = Table::orderBy('zona')->orderBy('posisi_x')->get();

        $orders = collect();
        if ($this->selectedMejaId) {
            $orders = Order::with(['items.menu', 'payments'])
                ->where('table_id', $this->selectedMejaId)
                ->where('status', 'menunggu_pembayaran')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return compact('mejas', 'orders');
    }
}; ?>

<div class="relative">
    {{-- Notifikasi Berhasil (Floating Alert) --}}
    @if ($showBerhasil)
        <div
            class="fixed top-6 right-6 z-50 flex items-center gap-3 rounded-xl bg-emerald-500 px-5 py-4 text-white shadow-xl animate-in fade-in slide-in-from-top-4">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <div class="flex-1">
                <h4 class="text-sm font-bold">Pembayaran Berhasil!</h4>
                <p class="text-xs text-emerald-100">Pembayaran diterima. Pesanan masuk ke antrian dapur.</p>
            </div>
            <button type="button" wire:click="$set('showBerhasil', false)"
                class="ml-4 text-white hover:text-emerald-200">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6 items-start">

        {{-- Kolom Kiri: Pemilihan Meja & Order (Menggunakan min-w-0 agar tidak overflow) --}}
        <div class="flex-1 w-full min-w-0 space-y-6">

            {{-- 1. Pemilihan Meja Aktif --}}
            <div
                class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5 shadow-sm">
                <h2 class="text-base font-black text-stone-900 dark:text-white mb-4">Pilih Meja Order</h2>
                <div class="flex gap-4 overflow-x-auto pb-2 snap-x hide-scrollbar">
                    @foreach ($mejas->groupBy('zona') as $zona => $mejasZona)
                        <div class="shrink-0 snap-start" wire:key="zona-bayar-{{ $zona }}">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-widest text-stone-400">
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
                                    <button type="button" wire:key="btn-meja-bayar-{{ $meja->id }}"
                                        wire:click="pilihMeja({{ $meja->id }})"
                                        class="min-w-[64px] rounded-xl border px-3 py-2.5 text-sm font-bold transition-all duration-200
                                        {{ $selectedMejaId === $meja->id
                                            ? 'bg-amber-600 border-amber-600 text-white shadow-md'
                                            : (in_array($meja->status, ['terisi', 'pesanan_masuk'])
                                                ? 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/30 dark:border-amber-900/50 dark:text-amber-400'
                                                : 'bg-stone-50 border-stone-200 text-stone-400 opacity-60 dark:bg-stone-900 dark:border-stone-800') }}">
                                        {{ $meja->nama_meja }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 2. Daftar Order di Meja Terpilih --}}
            <div
                class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5 shadow-sm">
                <h2 class="text-base font-black text-stone-900 dark:text-white mb-4">Daftar Order</h2>

                @if (!$selectedMejaId)
                    <div
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-stone-200 py-10 text-center dark:border-stone-800">
                        <svg class="h-10 w-10 text-stone-300 mb-2 dark:text-stone-700" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                        <p class="text-sm font-bold text-stone-500">Pilih meja yang aktif terlebih dahulu.</p>
                    </div>
                @elseif($orders->isEmpty())
                    <div
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-stone-200 py-10 text-center dark:border-stone-800">
                        <p class="text-sm font-bold text-stone-500">Tidak ada order yang belum lunas di meja ini.</p>
                    </div>
                @else
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($orders as $order)
                            <button type="button" wire:key="btn-order-{{ $order->id }}"
                                wire:click="pilihOrder({{ $order->id }})"
                                class="flex flex-col items-start rounded-xl border-2 p-4 text-left transition-all
                                {{ $selectedOrderId === $order->id
                                    ? 'border-amber-600 bg-amber-50 dark:bg-amber-950/20'
                                    : 'border-stone-200 bg-white hover:border-amber-300 dark:bg-stone-900 dark:border-stone-800 dark:hover:border-amber-700' }}">

                                <div class="flex w-full items-center justify-between mb-2">
                                    <span class="font-black text-stone-900 dark:text-white">Order
                                        #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <span
                                        class="rounded-lg px-2 py-1 text-[10px] font-black uppercase tracking-wider
                                        {{ $order->status === 'selesai' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400' }}">
                                        {{ $order->status }}
                                    </span>
                                </div>
                                <p class="text-sm font-bold text-stone-500 mb-1">
                                    Total: <span class="text-stone-900 dark:text-white">Rp
                                        {{ number_format($order->items->sum(fn($i) => $i->harga_saat_pesan * $i->qty), 0, ',', '.') }}</span>
                                </p>
                                <p class="text-xs text-stone-400 font-medium">{{ $order->items->count() }} Item &bull;
                                    {{ $order->created_at->format('H:i') }}</p>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Panel Kasir/Checkout Sticky (30%) --}}
        <div class="w-full lg:w-[420px] shrink-0 sticky top-24">
            <div
                class="flex flex-col h-[calc(100vh-8rem)] rounded-3xl border border-stone-200 bg-white shadow-xl dark:bg-stone-900 dark:border-stone-800 overflow-hidden">

                {{-- Header Checkout --}}
                <div class="p-5 border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-950/50">
                    <h2 class="text-lg font-black text-stone-900 dark:text-white">Penyelesaian Transaksi</h2>
                </div>

                {{-- Konten Pembayaran (Scrollable) --}}
                <div class="flex-1 overflow-y-auto p-5 hide-scrollbar">
                    @if (!$this->order)
                        <div class="flex h-full flex-col items-center justify-center text-center opacity-50">
                            <svg class="h-12 w-12 text-stone-400 mb-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-sm font-bold text-stone-500">Pilih ID Order untuk menampilkan tagihan.</p>
                        </div>
                    @else
                        {{-- Rincian Item (Struk Digital) --}}
                        <div
                            class="mb-6 rounded-2xl border border-stone-200 bg-stone-50 p-4 dark:bg-stone-800/50 dark:border-stone-700">
                            <p
                                class="mb-3 text-xs font-bold uppercase tracking-wider text-stone-500 text-center border-b border-stone-200 pb-2 border-dashed dark:border-stone-700">
                                Order #{{ $this->order->id }}
                            </p>
                            <div class="space-y-2 mb-3">
                                @foreach ($this->order->items as $item)
                                    <div wire:key="receipt-item-{{ $item->id }}"
                                        class="flex justify-between items-start text-sm">
                                        <span class="font-medium text-stone-600 dark:text-stone-300">
                                            <span
                                                class="font-bold text-stone-900 dark:text-white mr-1">{{ $item->qty }}x</span>
                                            {{ $item->menu->nama_menu }}
                                        </span>
                                        <span class="font-bold text-stone-900 dark:text-white shrink-0">
                                            {{ number_format($item->harga_saat_pesan * $item->qty, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t-2 border-dashed border-stone-200 pt-3 dark:border-stone-700">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-bold text-stone-500">Total Tagihan</span>
                                    <span class="text-lg font-black text-amber-600">Rp
                                        {{ number_format($this->total, 0, ',', '.') }}</span>
                                </div>
                                @if ($this->totalTerbayar > 0)
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-bold text-emerald-600">Sudah Dibayar</span>
                                        <span class="text-sm font-black text-emerald-600">- Rp
                                            {{ number_format($this->totalTerbayar, 0, ',', '.') }}</span>
                                    </div>
                                    <div
                                        class="flex justify-between items-center mt-2 border-t border-stone-200 pt-2 dark:border-stone-700">
                                        <span class="text-sm font-bold text-red-500">Sisa Tagihan</span>
                                        <span class="text-xl font-black text-red-500">Rp
                                            {{ number_format($this->sisa, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Input Pembayaran --}}
                        <div class="space-y-4">
                            <label
                                class="flex cursor-pointer items-center justify-between rounded-xl border border-stone-200 p-3 hover:bg-stone-50 transition-colors dark:border-stone-700 dark:hover:bg-stone-800">
                                <span class="text-sm font-bold text-stone-900 dark:text-white">Aktifkan Split
                                    Bill</span>
                                <input wire:model.live="isSplit" type="checkbox"
                                    class="h-5 w-5 rounded border-stone-300 text-amber-600 focus:ring-amber-600 dark:border-stone-600 dark:bg-stone-800" />
                            </label>

                            @if (!$isSplit)
                                {{-- Single Payment --}}
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Nama
                                            Pelanggan (Opsional)</label>
                                        <input wire:model="namaPembayar" type="text" placeholder="Contoh: Budi"
                                            class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label
                                                class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Metode</label>
                                            <select wire:model="metode"
                                                class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white">
                                                <option value="tunai">Tunai</option>
                                                <option value="transfer">Transfer</option>
                                                <option value="qris">QRIS</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Bayar
                                                (Rp)</label>
                                            <input wire:model="jumlahBayar" type="number" placeholder="0"
                                                class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-bold focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white" />
                                        </div>
                                    </div>
                                    @error('jumlahBayar')
                                        <span class="text-xs font-bold text-red-500">{{ $message }}</span>
                                    @enderror

                                    @if ($jumlahBayar && $jumlahBayar > $this->total)
                                        <div
                                            class="rounded-xl bg-emerald-50 p-3 border border-emerald-100 text-center dark:bg-emerald-950/30 dark:border-emerald-900/50">
                                            <p
                                                class="text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-1">
                                                Kembalian</p>
                                            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300">Rp
                                                {{ number_format($jumlahBayar - $this->total, 0, ',', '.') }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Split Bill Payment --}}
                                <div class="rounded-2xl border border-stone-200 p-4 dark:border-stone-700">
                                    <div class="space-y-3 mb-4 border-b border-stone-100 pb-4 dark:border-stone-800">
                                        <input wire:model="splitNama" type="text" placeholder="Nama Pembayar"
                                            class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm focus:border-amber-600 focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-white" />
                                        <div class="flex gap-2">
                                            <input wire:model="splitJumlah" type="number" placeholder="Nominal Rp"
                                                class="w-2/3 rounded-xl border border-stone-200 px-3 py-2 text-sm focus:border-amber-600 focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-white" />
                                            <select wire:model="splitMetode"
                                                class="w-1/3 rounded-xl border border-stone-200 px-3 py-2 text-sm focus:border-amber-600 focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-white">
                                                <option value="tunai">Tunai</option>
                                                <option value="qris">QRIS</option>
                                            </select>
                                        </div>
                                        <button type="button" wire:click="tambahSplitPembayaran"
                                            class="w-full rounded-xl bg-stone-100 px-4 py-2 text-sm font-bold text-stone-700 hover:bg-stone-200 transition-colors dark:bg-stone-800 dark:text-stone-300">
                                            + Tambah ke List
                                        </button>
                                    </div>

                                    <div class="space-y-2">
                                        @foreach ($splitPembayaran as $index => $split)
                                            <div wire:key="split-item-{{ $index }}"
                                                class="flex items-center justify-between rounded-xl bg-stone-50 px-3 py-2.5 dark:bg-stone-800/50">
                                                <div>
                                                    <p class="text-sm font-bold text-stone-900 dark:text-white">
                                                        {{ $split['nama'] }}</p>
                                                    <p
                                                        class="text-[10px] font-bold uppercase tracking-wider text-stone-500">
                                                        {{ $split['metode'] }}</p>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-sm font-black text-stone-900 dark:text-white">Rp
                                                        {{ number_format($split['jumlah'], 0, ',', '.') }}</span>
                                                    <button type="button"
                                                        wire:click="hapusSplit({{ $index }})"
                                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors dark:bg-red-900/30">✕</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if (!empty($splitPembayaran))
                                        <div class="mt-4 rounded-xl bg-amber-50 p-3 text-center dark:bg-amber-950/20">
                                            <p class="text-xs font-bold text-amber-700 dark:text-amber-500">Total
                                                Terkumpul: Rp
                                                {{ number_format(collect($splitPembayaran)->sum('jumlah'), 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer Checkout Action --}}
                @if ($this->order)
                    <div class="border-t border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900">
                        @error('split')
                            <p class="mb-2 text-xs font-bold text-red-500 text-center">{{ $message }}</p>
                        @enderror

                        <button type="button" wire:click="prosesBayar" wire:loading.attr="disabled"
                            class="relative flex w-full items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-3.5 text-base font-black text-white shadow-lg transition-all hover:bg-amber-700 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span wire:loading.remove wire:target="prosesBayar">Selesaikan Transaksi</span>
                            <span wire:loading wire:target="prosesBayar">Memproses Pembayaran...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
