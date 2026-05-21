<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Menu;

new class extends Component {
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterKategori = '';
    public bool $showModal = false;
    public bool $showHapusModal = false;

    public ?int $editId = null;
    public string $nama_menu = '';
    public string $kategori = '';
    public string $deskripsi = '';
    public int|string $harga = '';
    public bool $tersedia = true;
    public $foto = null;
    public ?string $fotoLama = null;
    public ?int $hapusId = null;

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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterKategori(): void
    {
        $this->resetPage();
    }

    public function tambahBaru(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $menu = Menu::findOrFail($id);
        $this->editId = $menu->id;
        $this->nama_menu = $menu->nama_menu;
        $this->kategori = $menu->kategori;
        $this->deskripsi = $menu->deskripsi ?? '';
        $this->harga = $menu->harga;
        $this->tersedia = $menu->tersedia;
        $this->fotoLama = $menu->foto;
        $this->foto = null;
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $this->validate([
            'nama_menu' => 'required|string|max:255',
            'kategori' => 'required|in:' . implode(',', array_keys($this->kategoriList)),
            'harga' => 'required|numeric|min:0',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nama_menu' => $this->nama_menu,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
            'harga' => $this->harga,
            'tersedia' => $this->tersedia,
        ];

        if ($this->foto) {
            $path = $this->foto->store('menu', 'public');
            $data['foto'] = $path;
        }

        if ($this->editId) {
            Menu::findOrFail($this->editId)->update($data);
        } else {
            Menu::create($data);
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->showHapusModal = true;
    }

    public function hapus(): void
    {
        if ($this->hapusId) {
            Menu::findOrFail($this->hapusId)->delete();
            $this->hapusId = null;
            $this->showHapusModal = false;
        }
    }

    public function toggleTersedia(int $id): void
    {
        $menu = Menu::findOrFail($id);
        $menu->update(['tersedia' => !$menu->tersedia]);
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->nama_menu = '';
        $this->kategori = '';
        $this->deskripsi = '';
        $this->harga = '';
        $this->tersedia = true;
        $this->foto = null;
        $this->fotoLama = null;
    }

    public function with(): array
    {
        $menus = Menu::query()->when($this->search, fn($q) => $q->where('nama_menu', 'like', "%{$this->search}%"))->when($this->filterKategori, fn($q) => $q->where('kategori', $this->filterKategori))->orderBy('kategori')->orderBy('nama_menu')->paginate(15);

        return compact('menus');
    }
}; ?>

<div class="space-y-6">

    {{-- Header & Aksi Utama --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-stone-900 dark:text-stone-100">Manajemen Menu</h1>
            <p class="text-sm text-stone-500 mt-1">Kelola katalog produk, harga, dan ketersediaan.</p>
        </div>
        <button type="button" wire:click="tambahBaru"
            class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-amber-700 transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Menu
        </button>
    </div>

    {{-- Toolbar Data Grid (Filter & Search) --}}
    <div
        class="flex flex-col sm:flex-row gap-3 rounded-2xl bg-white p-2 border border-stone-200 shadow-sm dark:bg-stone-900 dark:border-stone-800">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-stone-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input wire:model.live="search" type="text" placeholder="Cari nama menu..."
                class="w-full rounded-xl border-none bg-transparent pl-10 pr-4 py-2.5 text-sm font-medium text-stone-900 focus:ring-0 dark:text-white placeholder-stone-400" />
        </div>

        <div class="hidden sm:block w-px bg-stone-200 dark:bg-stone-800 my-2"></div>

        <div class="w-full sm:w-64 shrink-0">
            <select wire:model.live="filterKategori"
                class="w-full rounded-xl border-none bg-stone-50 px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-amber-600 dark:bg-stone-800 dark:text-white cursor-pointer transition-colors">
                <option value="">Semua Kategori</option>
                @foreach ($this->kategoriList as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabel / Data Grid Modern --}}
    <div
        class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden dark:bg-stone-900 dark:border-stone-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-stone-50 text-stone-500 dark:bg-stone-950/50 dark:text-stone-400">
                    <tr>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Produk</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Kategori</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Harga</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-center">Status</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                    @forelse($menus as $menu)
                        <tr wire:key="row-menu-{{ $menu->id }}"
                            class="hover:bg-stone-50/50 dark:hover:bg-stone-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    @if ($menu->foto)
                                        <img src="{{ Storage::url($menu->foto) }}"
                                            class="h-12 w-12 rounded-xl object-cover border border-stone-200 dark:border-stone-700" />
                                    @else
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-400 border border-stone-200 dark:border-stone-700">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-stone-900 dark:text-stone-100">{{ $menu->nama_menu }}
                                        </p>
                                        @if ($menu->deskripsi)
                                            <p class="text-xs text-stone-500 truncate max-w-[200px]">
                                                {{ $menu->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-md bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-600 dark:bg-stone-800 dark:text-stone-300">
                                    {{ $this->kategoriList[$menu->kategori] ?? $menu->kategori }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-stone-900 dark:text-stone-100">
                                Rp {{ number_format($menu->harga, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" wire:click="toggleTersedia({{ $menu->id }})"
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold transition-all hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-1
                                    {{ $menu->tersedia
                                        ? 'bg-emerald-100 text-emerald-700 focus:ring-emerald-500 dark:bg-emerald-950/30 dark:text-emerald-400'
                                        : 'bg-stone-100 text-stone-500 focus:ring-stone-400 dark:bg-stone-800 dark:text-stone-400' }}">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full {{ $menu->tersedia ? 'bg-emerald-500' : 'bg-stone-400' }}"></span>
                                    {{ $menu->tersedia ? 'Tersedia' : 'Habis' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" wire:click="edit({{ $menu->id }})"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-stone-100 text-stone-600 hover:bg-amber-100 hover:text-amber-700 transition-colors dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-amber-900/50 dark:hover:text-amber-500"
                                        title="Edit Menu">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="konfirmasiHapus({{ $menu->id }})"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-colors dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                        title="Hapus Menu">
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
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <p class="text-sm font-medium">Data menu tidak ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Area --}}
        @if ($menus->hasPages())
            <div class="border-t border-stone-100 px-6 py-4 dark:border-stone-800">
                {{ $menus->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah/Edit --}}
    @if ($showModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden dark:bg-stone-900">
                <div
                    class="border-b border-stone-100 bg-stone-50/50 px-6 py-4 dark:border-stone-800 dark:bg-stone-950/50">
                    <h3 class="text-lg font-black text-stone-900 dark:text-stone-100">
                        {{ $editId ? 'Edit Data Menu' : 'Buat Menu Baru' }}
                    </h3>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Nama
                            Menu</label>
                        <input wire:model="nama_menu" type="text"
                            class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white"
                            placeholder="Contoh: Kopi Susu Aren" />
                        @error('nama_menu')
                            <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Kategori</label>
                            <select wire:model="kategori"
                                class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white cursor-pointer">
                                <option value="">Pilih...</option>
                                @foreach ($this->kategoriList as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori')
                                <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Harga
                                (Rp)</label>
                            <input wire:model="harga" type="number"
                                class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white"
                                placeholder="0" />
                            @error('harga')
                                <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Deskripsi
                            Pendek</label>
                        <textarea wire:model="deskripsi" rows="2"
                            class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white resize-none"
                            placeholder="Penjelasan opsional mengenai menu..."></textarea>
                    </div>

                    <div
                        class="rounded-xl border border-stone-200 bg-stone-50 p-4 dark:border-stone-700 dark:bg-stone-800/50">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-stone-500">Gambar
                            Produk</label>
                        <div class="flex items-center gap-4">
                            @if ($fotoLama && !$foto)
                                <img src="{{ Storage::url($fotoLama) }}"
                                    class="h-16 w-16 rounded-xl object-cover border border-stone-200 shadow-sm" />
                            @endif
                            <input wire:model="foto" type="file" accept="image/*"
                                class="w-full text-sm text-stone-500 file:mr-4 file:rounded-lg file:border-0 file:bg-stone-800 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-stone-700 transition-colors" />
                        </div>
                        @error('foto')
                            <span class="mt-2 block text-xs font-bold text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-4 hover:bg-stone-50 transition-colors dark:border-stone-700 dark:hover:bg-stone-800">
                        <input wire:model="tersedia" type="checkbox"
                            class="h-5 w-5 rounded border-stone-300 text-amber-600 focus:ring-amber-600 dark:border-stone-600 dark:bg-stone-800" />
                        <div>
                            <span class="block text-sm font-bold text-stone-900 dark:text-stone-100">Produk
                                Tersedia</span>
                            <span class="block text-xs text-stone-500">Tampilkan produk ini di halaman kasir</span>
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
                            wire:target="simpan">{{ $editId ? 'Simpan Pembaruan' : 'Simpan Menu' }}</span>
                        <span wire:loading wire:target="simpan">Menyimpan...</span>
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
                <h3 class="mb-2 text-xl font-black text-stone-900 dark:text-stone-100">Hapus Data?</h3>
                <p class="mb-6 text-sm text-stone-500">Data menu yang dihapus tidak dapat dikembalikan. Lanjutkan?</p>
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
