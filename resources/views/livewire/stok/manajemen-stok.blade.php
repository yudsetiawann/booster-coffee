<?php

use Livewire\Volt\Component;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

new class extends Component {
    // Tab aktif
    public string $tab = 'bahan';

    // Bahan Baku
    public bool $showModalBahan = false;
    public bool $showHapusModalBahan = false;
    public ?int $editBahanId = null;
    public ?int $hapusBahanId = null;
    public string $nama_bahan = '';
    public int|string $stok_saat_ini = '';
    public string $satuan = '';
    public int|string $stok_minimum = '';

    // Resep
    public bool $showModalResep = false;
    public ?int $selectedMenuId = null;
    public array $resepItems = [];
    public ?int $resepIngredientId = null;
    public int|string $resepJumlah = '';

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
    }

    // ── BAHAN BAKU ──────────────────────────────────────────

    public function tambahBahan(): void
    {
        $this->resetFormBahan();
        $this->showModalBahan = true;
    }

    public function editBahan(int $id): void
    {
        $bahan = Ingredient::findOrFail($id);
        $this->editBahanId = $bahan->id;
        $this->nama_bahan = $bahan->nama_bahan;
        $this->stok_saat_ini = $bahan->stok_saat_ini;
        $this->satuan = $bahan->satuan;
        $this->stok_minimum = $bahan->stok_minimum;
        $this->showModalBahan = true;
    }

    public function simpanBahan(): void
    {
        $this->validate([
            'nama_bahan'    => 'required|string|max:255',
            'stok_saat_ini' => 'required|numeric|min:0',
            'satuan'        => 'required|in:gram,kg,ml,liter,pcs,sachet,botol',
            'stok_minimum'  => 'required|numeric|min:0',
        ]);

        $data = [
            'nama_bahan'    => $this->nama_bahan,
            'stok_saat_ini' => (float) $this->stok_saat_ini,
            'satuan'        => $this->satuan,
            'stok_minimum'  => (float) $this->stok_minimum,
        ];

        DB::transaction(function () use ($data) {
            if ($this->editBahanId) {
                Ingredient::findOrFail($this->editBahanId)->update($data);
                Log::info('Bahan baku diperbarui', ['ingredient_id' => $this->editBahanId, 'admin_id' => auth()->id()]);
            } else {
                $bahan = Ingredient::create($data);
                Log::info('Bahan baku ditambahkan', ['ingredient_id' => $bahan->id, 'admin_id' => auth()->id()]);
            }
        });

        $this->resetFormBahan();
        $this->showModalBahan = false;
    }

    public function konfirmasiHapusBahan(int $id): void
    {
        $this->hapusBahanId = $id;
        $this->showHapusModalBahan = true;
    }

    public function hapusBahan(): void
    {
        if ($this->hapusBahanId) {
            DB::transaction(function () {
                Ingredient::findOrFail($this->hapusBahanId)->delete();
                Log::info('Bahan baku dihapus', ['ingredient_id' => $this->hapusBahanId, 'admin_id' => auth()->id()]);
            });

            $this->hapusBahanId        = null;
            $this->showHapusModalBahan = false;
        }
    }

    public function resetFormBahan(): void
    {
        $this->editBahanId = null;
        $this->nama_bahan = '';
        $this->stok_saat_ini = '';
        $this->satuan = '';
        $this->stok_minimum = '';
    }

    // ── RESEP ────────────────────────────────────────────────

    public function kelolaResep(int $menuId): void
    {
        $this->selectedMenuId = $menuId;
        $this->resepItems = Recipe::where('menu_id', $menuId)
            ->with('ingredient')
            ->get()
            ->map(
                fn($r) => [
                    'id' => $r->id,
                    'ingredient_id' => $r->ingredient_id,
                    'nama_bahan' => $r->ingredient->nama_bahan,
                    'jumlah_pakai' => $r->jumlah_pakai,
                    'satuan' => $r->ingredient->satuan,
                ],
            )
            ->toArray();
        $this->showModalResep = true;
    }

    public function tambahResepItem(): void
    {
        // Bug #8 fix: validasi dengan pesan error, bukan silent fail
        $this->validate([
            'resepIngredientId' => 'required|integer',
            'resepJumlah'       => 'required|numeric|min:0.01',
        ], [
            'resepIngredientId.required' => 'Pilih bahan baku terlebih dahulu.',
            'resepJumlah.required'       => 'Jumlah pemakaian harus diisi.',
            'resepJumlah.min'            => 'Jumlah minimal 0.01.',
        ]);

        $ingredient = Ingredient::findOrFail($this->resepIngredientId);

        // Bug #3 fix: gunakan id dari hasil updateOrCreate agar tombol hapus berfungsi
        $recipe = Recipe::updateOrCreate(
            ['menu_id' => $this->selectedMenuId, 'ingredient_id' => $this->resepIngredientId],
            ['jumlah_pakai' => $this->resepJumlah]
        );

        // Bug #3 fix: hapus entri lama jika ingredient sudah ada (updateOrCreate bisa update),
        // lalu tambah/perbarui dengan data terbaru termasuk id
        $this->resepItems = array_values(
            array_filter($this->resepItems, fn($item) => $item['ingredient_id'] !== $ingredient->id)
        );

        $this->resepItems[] = [
            'id'            => $recipe->id,   // ← id wajib ada agar tombol hapus muncul
            'ingredient_id' => $ingredient->id,
            'nama_bahan'    => $ingredient->nama_bahan,
            'jumlah_pakai'  => $this->resepJumlah,
            'satuan'        => $ingredient->satuan,
        ];

        $this->resepIngredientId = null;
        $this->resepJumlah = '';
    }

    public function hapusResepItem(int $recipeId): void
    {
        Recipe::findOrFail($recipeId)->delete();
        $this->resepItems = array_values(array_filter($this->resepItems, fn($item) => $item['id'] !== $recipeId));
    }

    public function with(): array
    {
        $bahans = Ingredient::orderBy('nama_bahan')->get();
        $menus = Menu::with('recipes')->orderBy('kategori')->orderBy('nama_menu')->get();

        return compact('bahans', 'menus');
    }
}; ?>

<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-black tracking-tight text-stone-900 dark:text-stone-100">Manajemen Stok & Resep</h1>
        <p class="text-sm text-stone-500 mt-1">Pusat kendali inventaris bahan baku dan formulasi menu.</p>
    </div>

    {{-- Tabs Modern --}}
    <div class="flex gap-6 border-b border-stone-200 dark:border-stone-800">
        <button type="button" wire:click="switchTab('bahan')"
            class="pb-3 text-sm font-bold transition-all relative
            {{ $tab === 'bahan' ? 'text-amber-600' : 'text-stone-500 hover:text-stone-800 dark:hover:text-stone-300' }}">
            Stok Bahan Baku
            @if($tab === 'bahan')
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-amber-600 rounded-t-full"></span>
            @endif
        </button>
        <button type="button" wire:click="switchTab('resep')"
            class="pb-3 text-sm font-bold transition-all relative
            {{ $tab === 'resep' ? 'text-amber-600' : 'text-stone-500 hover:text-stone-800 dark:hover:text-stone-300' }}">
            Formulasi Resep Menu
            @if($tab === 'resep')
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-amber-600 rounded-t-full"></span>
            @endif
        </button>
    </div>

    {{-- Tab Konten: Bahan Baku --}}
    @if ($tab === 'bahan')
        <div class="space-y-4 animate-in fade-in duration-300">
            <div class="flex justify-end">
                <button type="button" wire:click="tambahBahan"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-amber-700 transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah Bahan
                </button>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden dark:bg-stone-900 dark:border-stone-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-stone-50 text-stone-500 dark:bg-stone-950/50 dark:text-stone-400">
                            <tr>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Nama Bahan</th>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-center">Stok Fisik</th>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-center">Batas Minimum</th>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-center">Status</th>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                            @forelse($bahans as $bahan)
                                @php $menipis = $bahan->stok_saat_ini <= $bahan->stok_minimum; @endphp
                                <tr wire:key="bahan-{{ $bahan->id }}" class="hover:bg-stone-50/50 dark:hover:bg-stone-800/50 transition-colors group {{ $menipis ? 'bg-red-50/30 dark:bg-red-950/10' : '' }}">
                                    <td class="px-6 py-4 font-bold text-stone-900 dark:text-stone-100">{{ $bahan->nama_bahan }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-black text-lg {{ $menipis ? 'text-red-600' : 'text-stone-900 dark:text-stone-100' }}">
                                            {{ number_format($bahan->stok_saat_ini, 0, ',', '.') }}
                                        </span>
                                        <span class="text-xs font-medium text-stone-500 ml-1">{{ $bahan->satuan }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-medium text-stone-500">
                                        {{ number_format($bahan->stok_minimum, 0, ',', '.') }} {{ $bahan->satuan }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($menipis)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-950/30 dark:text-red-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                                Kritis
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Aman
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button" wire:click="editBahan({{ $bahan->id }})"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-stone-100 text-stone-600 hover:bg-amber-100 hover:text-amber-700 transition-colors dark:bg-stone-800 dark:text-stone-400" title="Edit">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                            <button type="button" wire:click="konfirmasiHapusBahan({{ $bahan->id }})"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-colors dark:bg-red-950/30 dark:text-red-400" title="Hapus">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-stone-400 font-medium">Belum ada data bahan baku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Tab Konten: Resep Menu --}}
    @if ($tab === 'resep')
        <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden dark:bg-stone-900 dark:border-stone-800 animate-in fade-in duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-stone-50 text-stone-500 dark:bg-stone-950/50 dark:text-stone-400">
                        <tr>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Produk Menu</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Kategori</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-center">Komponen Resep</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                        @foreach ($menus as $menu)
                            <tr wire:key="resep-menu-{{ $menu->id }}" class="hover:bg-stone-50/50 dark:hover:bg-stone-800/50 transition-colors group">
                                <td class="px-6 py-4 font-bold text-stone-900 dark:text-stone-100">{{ $menu->nama_menu }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-600 dark:bg-stone-800 dark:text-stone-300">
                                        {{ $menu->kategori }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold {{ $menu->recipes->count() > 0 ? 'text-emerald-600' : 'text-stone-400' }}">
                                        {{ $menu->recipes->count() }} Bahan
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" wire:click="kelolaResep({{ $menu->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-xs font-bold text-stone-700 shadow-sm hover:bg-stone-50 transition-colors dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300 dark:hover:bg-stone-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        Kelola Formulasi
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modal Tambah/Edit Bahan --}}
    @if ($showModalBahan)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl overflow-hidden dark:bg-stone-900">
                <div class="border-b border-stone-100 bg-stone-50/50 px-6 py-4 dark:border-stone-800 dark:bg-stone-950/50">
                    <h3 class="text-lg font-black text-stone-900 dark:text-stone-100">
                        {{ $editBahanId ? 'Edit Bahan Baku' : 'Tambah Bahan Baku' }}
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Nama Bahan</label>
                        <input wire:model="nama_bahan" type="text" placeholder="Contoh: Biji Kopi Arabica"
                            class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white" />
                        @error('nama_bahan') <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Stok Saat Ini</label>
                            <input wire:model="stok_saat_ini" type="number" min="0" placeholder="0"
                                class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white" />
                            @error('stok_saat_ini') <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Satuan</label>
                            <select wire:model="satuan" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white cursor-pointer">
                                <option value="">Pilih...</option>
                                <option value="gram">Gram (g)</option>
                                <option value="kg">Kilogram (Kg)</option>
                                <option value="ml">Mililiter (ml)</option>
                                <option value="liter">Liter (L)</option>
                                <option value="pcs">Pcs</option>
                                <option value="sachet">Sachet</option>
                                <option value="botol">Botol</option>
                            </select>
                            @error('satuan') <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-stone-500">Batas Stok Minimum (Peringatan Kritis)</label>
                        <input wire:model="stok_minimum" type="number" min="0" placeholder="0"
                            class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-800 dark:text-white" />
                        @error('stok_minimum') <span class="mt-1 block text-xs font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="border-t border-stone-100 bg-stone-50/50 px-6 py-4 flex flex-col-reverse sm:flex-row justify-end gap-3 dark:border-stone-800 dark:bg-stone-950/50">
                    <button type="button" wire:click="$set('showModalBahan', false)"
                        class="rounded-xl px-5 py-2.5 text-sm font-bold text-stone-600 hover:bg-stone-200 transition-all dark:text-stone-400 dark:hover:bg-stone-800">Batal</button>
                    <button type="button" wire:click="simpanBahan" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-amber-700 transition-all focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="simpanBahan">{{ $editBahanId ? 'Simpan' : 'Tambah' }}</span>
                        <span wire:loading wire:target="simpanBahan">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Hapus Bahan --}}
    @if ($showHapusModalBahan)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl text-center dark:bg-stone-900">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="h-8 w-8 text-red-600 dark:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="mb-2 text-xl font-black text-stone-900 dark:text-stone-100">Hapus Bahan Baku?</h3>
                <p class="mb-6 text-sm text-stone-500">Tindakan ini permanen dan dapat memengaruhi resep yang menggunakannya.</p>
                <div class="flex flex-col gap-2">
                    <button type="button" wire:click="hapusBahan"
                        class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg hover:bg-red-700 transition-all">Ya, Hapus</button>
                    <button type="button" wire:click="$set('showHapusModalBahan', false)"
                        class="w-full rounded-xl px-4 py-3 text-sm font-bold text-stone-600 hover:bg-stone-100 transition-all dark:text-stone-400 dark:hover:bg-stone-800">Batal</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Kelola Resep --}}
    @if ($showModalResep && $selectedMenuId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden dark:bg-stone-900">
                <div class="border-b border-stone-100 bg-stone-50/50 px-6 py-4 dark:border-stone-800 dark:bg-stone-950/50">
                    <h3 class="text-lg font-black text-stone-900 dark:text-stone-100">Formulasi Resep</h3>
                    <p class="text-sm font-medium text-amber-600 mt-1">{{ $menus->firstWhere('id', $selectedMenuId)?->nama_menu }}</p>
                </div>

                <div class="p-6">
                    {{-- Form Tambah Item Resep --}}
                    <div class="mb-6 rounded-2xl border border-stone-200 bg-stone-50 p-4 dark:bg-stone-800/50 dark:border-stone-700">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-stone-500">Tambahkan Komponen Bahan</p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <select wire:model="resepIngredientId"
                                class="flex-1 rounded-xl border border-stone-200 px-3 py-2 text-sm focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-900 dark:text-white cursor-pointer">
                                <option value="">Pilih Bahan Baku...</option>
                                @foreach ($bahans as $bahan)
                                    <option value="{{ $bahan->id }}">{{ $bahan->nama_bahan }} ({{ $bahan->satuan }})</option>
                                @endforeach
                            </select>
                            <div class="flex gap-2">
                                <input wire:model="resepJumlah" type="number" min="0" placeholder="Qty"
                                    class="w-24 rounded-xl border border-stone-200 px-3 py-2 text-sm text-center focus:border-amber-600 focus:ring-1 focus:ring-amber-600 dark:border-stone-700 dark:bg-stone-900 dark:text-white" />
                                <button type="button" wire:click="tambahResepItem"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-stone-900 text-white hover:bg-stone-800 transition-colors dark:bg-white dark:text-stone-900">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Resep Saat Ini --}}
                    <div class="space-y-2">
                        @forelse($resepItems as $index => $item)
                            <div wire:key="resep-item-{{ $item['id'] ?? $index }}" class="flex items-center justify-between rounded-xl border border-stone-100 bg-white px-4 py-3 shadow-sm dark:border-stone-800 dark:bg-stone-900">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-stone-100 text-stone-500 dark:bg-stone-800">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-stone-900 dark:text-white">{{ $item['nama_bahan'] }}</p>
                                        <p class="text-xs font-medium text-stone-500">{{ $item['jumlah_pakai'] }} {{ $item['satuan'] }} per porsi</p>
                                    </div>
                                </div>
                                @if (isset($item['id']))
                                    <button type="button" wire:click="hapusResepItem({{ $item['id'] }})"
                                        class="p-2 text-stone-400 hover:text-red-500 transition-colors">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-xl border-2 border-dashed border-stone-200 py-8 text-center dark:border-stone-800">
                                <p class="text-sm font-bold text-stone-400">Resep menu ini belum dikonfigurasi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="border-t border-stone-100 bg-stone-50/50 px-6 py-4 dark:border-stone-800 dark:bg-stone-950/50">
                    <button type="button" wire:click="$set('showModalResep', false)"
                        class="w-full rounded-xl bg-stone-900 px-4 py-3 text-sm font-bold text-white hover:bg-stone-800 transition-all dark:bg-white dark:text-stone-900 dark:hover:bg-stone-200">
                        Selesai & Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
