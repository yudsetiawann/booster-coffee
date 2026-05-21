<?php

use Livewire\Volt\Component;
use App\Models\Promo;
use Illuminate\Validation\Rule;

new class extends Component {
    public bool $showModal = false;
    public bool $showHapusModal = false;
    public ?int $editId = null;
    public ?int $hapusId = null;

    public string $nama_promo = '';
    public string $tipe = 'persen';
    public int|string $nilai = '';
    public bool $aktif = true;
    public string $berlaku_mulai = '';
    public string $berlaku_sampai = '';

    public array $tipeList = [
        'persen' => 'Diskon Persen (%)',
        'nominal' => 'Diskon Nominal (Rp)',
        'bogo' => 'Buy 1 Get 1',
        'member' => 'Diskon Member',
    ];

    public function tambahBaru(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $promo = Promo::findOrFail($id);
        $this->editId = $promo->id;
        $this->nama_promo = $promo->nama_promo;
        $this->tipe = $promo->tipe;
        $this->nilai = $promo->nilai;
        $this->aktif = $promo->aktif;
        $this->berlaku_mulai = $promo->berlaku_mulai->format('Y-m-d');
        $this->berlaku_sampai = $promo->berlaku_sampai->format('Y-m-d');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $nilaiRules = match ($this->tipe) {
            'persen' => 'required|numeric|min:0|max:100',
            'nominal', 'member' => 'required|numeric|min:0',
            'bogo' => 'nullable|numeric',
            default => 'required|numeric|min:0',
        };

        $this->validate([
            'nama_promo' => 'required|string|max:255',
            'tipe' => 'required|in:persen,nominal,bogo,member',
            'nilai' => $nilaiRules,
            'berlaku_mulai' => 'required|date',
            'berlaku_sampai' => 'required|date|after_or_equal:berlaku_mulai',
        ]);

        $data = [
            'nama_promo' => $this->nama_promo,
            'tipe' => $this->tipe,
            'nilai' => $this->tipe === 'bogo' ? 0 : (int) $this->nilai,
            'aktif' => $this->aktif,
            'berlaku_mulai' => $this->berlaku_mulai,
            'berlaku_sampai' => $this->berlaku_sampai,
        ];

        if ($this->editId) {
            Promo::findOrFail($this->editId)->update($data);
        } else {
            Promo::create($data);
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function toggleAktif(int $id): void
    {
        $promo = Promo::findOrFail($id);
        $promo->update(['aktif' => !$promo->aktif]);
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->showHapusModal = true;
    }

    public function hapus(): void
    {
        if ($this->hapusId) {
            Promo::findOrFail($this->hapusId)->delete();
            $this->hapusId = null;
            $this->showHapusModal = false;
        }
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->nama_promo = '';
        $this->tipe = 'persen';
        $this->nilai = '';
        $this->aktif = true;
        $this->berlaku_mulai = '';
        $this->berlaku_sampai = '';
    }

    public function with(): array
    {
        $promos = Promo::orderBy('aktif', 'desc')->orderBy('berlaku_sampai', 'asc')->get();

        return compact('promos');
    }
}; ?>

<div class="space-y-6">

    {{-- Header & Aksi Utama --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-stone-900 dark:text-stone-100">Promo & Diskon</h1>
            <p class="text-sm text-stone-500 mt-1">Atur strategi promosi dan loyalitas pelanggan Booster Coffee.</p>
        </div>
        <button type="button" wire:click="tambahBaru"
            class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-amber-700 transition-all focus:ring-2 focus:ring-amber-600 focus:ring-offset-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Promo
        </button>
    </div>

    {{-- Tabel Data Grid --}}
    <div
        class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden dark:bg-stone-900 dark:border-stone-800 animate-in fade-in duration-300">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-stone-50 text-stone-500 dark:bg-stone-950/50 dark:text-stone-400">
                    <tr>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Informasi Promo</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Mekanisme</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Durasi Berlaku</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-center">Status</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                    @forelse($promos as $promo)
                        <tr wire:key="row-promo-{{ $promo->id }}"
                            class="hover:bg-stone-50/50 dark:hover:bg-stone-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="font-black text-stone-900 dark:text-white mb-0.5">{{ $promo->nama_promo }}</p>
                                <span
                                    class="inline-flex items-center rounded-md bg-stone-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-stone-500 dark:bg-stone-800 dark:text-stone-400">
                                    {{ $promo->tipe }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($promo->tipe === 'persen')
                                    <span class="text-base font-black text-amber-600">{{ $promo->nilai }}% OFF</span>
                                @elseif($promo->tipe === 'nominal' || $promo->tipe === 'member')
                                    <span class="text-base font-black text-amber-600">- Rp
                                        {{ number_format($promo->nilai, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-sm font-bold text-stone-500">Buy 1 Get 1 Free</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="flex items-center gap-2 text-xs font-medium text-stone-600 dark:text-stone-300">
                                    <svg class="h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $promo->berlaku_mulai->format('d M y') }} <span
                                        class="text-stone-300">&rarr;</span>
                                    {{ $promo->berlaku_sampai->format('d M y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" wire:click="toggleAktif({{ $promo->id }})"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2
                                    {{ $promo->aktif ? 'bg-emerald-500' : 'bg-stone-300 dark:bg-stone-600' }}"
                                    role="switch">
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $promo->aktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" wire:click="edit({{ $promo->id }})"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-stone-100 text-stone-600 hover:bg-amber-100 hover:text-amber-700 transition-colors dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-amber-900/50 dark:hover:text-amber-500"
                                        title="Edit Promo">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="konfirmasiHapus({{ $promo->id }})"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-colors dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                        title="Hapus Promo">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-stone-400">
                                    <svg class="h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
                                    </svg>
                                    <p class="text-sm font-medium">Belum ada program promo aktif.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah/Edit Promo --}}
    @if ($showModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden dark:bg-stone-900">
                <div
                    class="border-b border-stone-100 bg-stone-50/50 px-6 py-4 dark:border-stone-800 dark:bg-stone-950/50">
                    <h3 class="text-lg font-black text-stone-900 dark:text-stone-100">
                        {{ $editId ? 'Ubah Konfigurasi Promo' : 'Buat Program Promo' }}
                    </h3>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Nama
                            Kampanye Promo</label>
                        <input wire:model="nama_promo" type="text" placeholder="Contoh: Diskon Kemerdekaan"
                            class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white" />
                        @error('nama_promo')
                            <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Mekanisme
                                Promo</label>
                            <select wire:model.live="tipe"
                                class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white cursor-pointer">
                                @foreach ($this->tipeList as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('tipe')
                                <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        @if (in_array($tipe, ['persen', 'nominal', 'member']))
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">
                                    Besaran {{ $tipe === 'persen' ? 'Diskon (%)' : 'Potongan (Rp)' }}
                                </label>
                                <input wire:model="nilai" type="number"
                                    placeholder="{{ $tipe === 'persen' ? 'Maks 100%' : 'Nominal Rupiah' }}"
                                    class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-bold text-amber-600 focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800" />
                                @error('nilai')
                                    <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        @else
                            <div
                                class="flex items-center justify-center rounded-xl border border-dashed border-stone-200 bg-stone-50 px-4 py-2.5 dark:border-stone-700 dark:bg-stone-800/50">
                                <span class="text-xs font-bold text-stone-400 text-center">Nilai dihitung otomatis oleh
                                    sistem POS</span>
                            </div>
                        @endif
                    </div>

                    <div
                        class="rounded-xl border border-stone-200 bg-stone-50 p-4 dark:border-stone-700 dark:bg-stone-800/50">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-stone-500">Durasi Validitas</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-[11px] font-bold text-stone-400">Mulai</label>
                                <input wire:model="berlaku_mulai" type="date"
                                    class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-600 dark:bg-stone-900 dark:text-white" />
                                @error('berlaku_mulai')
                                    <span class="mt-1 block text-[10px] font-bold text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold text-stone-400">Berakhir</label>
                                <input wire:model="berlaku_sampai" type="date"
                                    class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-600 dark:bg-stone-900 dark:text-white" />
                                @error('berlaku_sampai')
                                    <span class="mt-1 block text-[10px] font-bold text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-4 hover:bg-stone-50 transition-colors dark:border-stone-700 dark:hover:bg-stone-800">
                        <input wire:model="aktif" type="checkbox"
                            class="h-5 w-5 rounded border-stone-300 text-amber-600 focus:ring-amber-600 dark:border-stone-600 dark:bg-stone-800" />
                        <div>
                            <span class="block text-sm font-bold text-stone-900 dark:text-white">Aktifkan
                                Langsung</span>
                            <span class="block text-xs text-stone-500">Promo ini akan otomatis terpotong di halaman
                                kasir</span>
                        </div>
                    </label>
                </div>

                <div
                    class="border-t border-stone-100 bg-stone-50/50 px-6 py-4 flex flex-col-reverse sm:flex-row justify-end gap-3 dark:border-stone-800 dark:bg-stone-950/50">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="rounded-xl px-5 py-2.5 text-sm font-bold text-stone-600 hover:bg-stone-200 transition-all dark:text-stone-400 dark:hover:bg-stone-800">
                        Batal
                    </button>
                    <button type="button" wire:click="simpan" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-amber-700 transition-all focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 disabled:opacity-50">
                        <span wire:loading.remove
                            wire:target="simpan">{{ $editId ? 'Simpan Pembaruan' : 'Terapkan Promo' }}</span>
                        <span wire:loading wire:target="simpan">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Hapus --}}
    @if ($showHapusModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl text-center dark:bg-stone-900">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="h-8 w-8 text-red-600 dark:text-red-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-black text-stone-900 dark:text-stone-100">Hapus Promo?</h3>
                <p class="mb-6 text-sm text-stone-500">Pelanggan tidak akan bisa lagi menggunakan promo ini. Lanjutkan?
                </p>
                <div class="flex flex-col gap-2">
                    <button type="button" wire:click="hapus" wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg hover:bg-red-700 transition-all focus:ring-2 focus:ring-red-600 focus:ring-offset-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="hapus">Ya, Hapus Permanen</span>
                        <span wire:loading wire:target="hapus">Memproses...</span>
                    </button>
                    <button type="button" wire:click="$set('showHapusModal', false)"
                        class="w-full rounded-xl px-4 py-3 text-sm font-bold text-stone-600 hover:bg-stone-100 transition-all dark:text-stone-400 dark:hover:bg-stone-800">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
