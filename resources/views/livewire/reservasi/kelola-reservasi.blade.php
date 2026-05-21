<?php

use Livewire\Volt\Component;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Validation\Rule;

new class extends Component {
    public int|string $dp_amount = 0;
    public string $dp_status = 'belum_bayar';
    public string $dp_metode = 'tunai';
    public bool $showModal = false;
    public bool $showHapusModal = false;
    public ?int $editId = null;
    public ?int $hapusId = null;
    public string $filterStatus = '';

    public ?int $table_id = null;
    public string $nama_pelanggan = '';
    public string $nomor_hp = '';
    public string $tanggal = '';
    public string $jam_mulai = '';
    public string $jam_selesai = '';
    public int|string $jumlah_tamu = '';
    public string $status = 'pending';

    public function tambahBaru(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $reservasi = Reservation::findOrFail($id);
        $this->editId = $reservasi->id;
        $this->table_id = $reservasi->table_id;
        $this->nama_pelanggan = $reservasi->nama_pelanggan;
        $this->nomor_hp = $reservasi->nomor_hp;
        $this->tanggal = $reservasi->tanggal->format('Y-m-d');
        $this->jam_mulai = $reservasi->jam_mulai;
        $this->jam_selesai = $reservasi->jam_selesai;
        $this->jumlah_tamu = $reservasi->jumlah_tamu;
        $this->status = $reservasi->status;
        $this->dp_amount = $reservasi->dp_amount;
        $this->dp_status = $reservasi->dp_status;
        $this->dp_metode = $reservasi->dp_metode ?? 'tunai';
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $this->validate([
            'table_id' => 'required|exists:tables,id',
            'nama_pelanggan' => 'required|string|max:255',
            'nomor_hp' => 'required|string|max:20',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'jumlah_tamu' => 'required|integer|min:1',
            'dp_amount' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'table_id' => $this->table_id,
            'nama_pelanggan' => $this->nama_pelanggan,
            'nomor_hp' => $this->nomor_hp,
            'tanggal' => $this->tanggal,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'jumlah_tamu' => $this->jumlah_tamu,
            'status' => $this->status,
            'dp_amount' => $this->dp_amount ?? 0,
            'dp_status' => $this->dp_status,
            'dp_metode' => $this->dp_amount > 0 ? $this->dp_metode : null,
        ];

        if ($this->editId) {
            Reservation::findOrFail($this->editId)->update($data);
        } else {
            Reservation::create($data);
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function updateStatus(int $id, string $status): void
    {
        // Validasi enum dari frontend sebelum menyentuh DB
        if (!in_array($status, ['pending', 'dikonfirmasi', 'dibatalkan'])) {
            return;
        }

        Reservation::findOrFail($id)->update(['status' => $status]);
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->showHapusModal = true;
    }

    public function hapus(): void
    {
        if ($this->hapusId) {
            Reservation::findOrFail($this->hapusId)->delete();
            $this->hapusId = null;
            $this->showHapusModal = false;
        }
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->table_id = null;
        $this->nama_pelanggan = '';
        $this->nomor_hp = '';
        $this->tanggal = '';
        $this->jam_mulai = '';
        $this->jam_selesai = '';
        $this->jumlah_tamu = '';
        $this->status = 'pending';
        $this->dp_amount = 0;
        $this->dp_status = 'belum_bayar';
        $this->dp_metode = 'tunai';
    }

    public function with(): array
    {
        $reservasis = Reservation::with('table')->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))->orderBy('tanggal', 'asc')->orderBy('jam_mulai', 'asc')->get();

        $mejas = Table::orderBy('zona')->orderBy('posisi_x')->get();

        return compact('reservasis', 'mejas');
    }
}; ?>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-stone-900">Reservasi Meja</h1>
            <p class="text-sm text-stone-500 mt-1">Kelola reservasi pelanggan Booster Coffee</p>
        </div>
        <button wire:click="tambahBaru"
            class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-amber-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Reservasi
        </button>
    </div>

    {{-- Filter Status --}}
    <div class="flex flex-wrap gap-2 border-b border-stone-200 pb-4">
        @foreach (['' => 'Semua', 'pending' => 'Pending', 'dikonfirmasi' => 'Dikonfirmasi', 'dibatalkan' => 'Dibatalkan'] as $key => $label)
            <button wire:click="$set('filterStatus', '{{ $key }}')"
                class="rounded-lg px-4 py-1.5 text-sm font-semibold transition
                {{ $filterStatus === $key
                    ? 'bg-amber-600 text-white'
                    : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tabel Reservasi --}}
    <div class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">Pelanggan</th>
                        {{-- <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">No. HP</th> --}}
                        <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">Meja</th>
                        <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">Tanggal</th>
                        <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">Jam</th>
                        <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-xs">Tamu</th>
                        <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-xs">Status</th>
                        <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">DP</th>
                        <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-xs">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($reservasis as $reservasi)
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-5 py-4 font-semibold text-stone-900">{{ $reservasi->nama_pelanggan }}</td>
                            {{-- <td class="px-5 py-4 text-stone-500">{{ $reservasi->nomor_hp }}</td> --}}
                            <td class="px-5 py-4">
                                <span
                                    class="inline-block rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                    {{ $reservasi->table?->nama_meja ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-stone-500">{{ $reservasi->tanggal->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-stone-500">
                                {{ date('H:i', strtotime($reservasi->jam_mulai)) }} —
                                {{ date('H:i', strtotime($reservasi->jam_selesai)) }}
                            </td>
                            <td class="px-5 py-4 text-center text-stone-500">{{ $reservasi->jumlah_tamu }}</td>
                            <td class="px-5 py-4 text-center">
                                <select wire:change="updateStatus({{ $reservasi->id }}, $event.target.value)"
                                    class="rounded-lg border px-2 py-1 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500
                                    {{ $reservasi->status === 'dikonfirmasi'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : ($reservasi->status === 'dibatalkan'
                                            ? 'border-red-200 bg-red-50 text-red-700'
                                            : 'border-amber-200 bg-amber-50 text-amber-700') }}">
                                    <option value="pending" {{ $reservasi->status === 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="dikonfirmasi"
                                        {{ $reservasi->status === 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi
                                    </option>
                                    <option value="dibatalkan"
                                        {{ $reservasi->status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($reservasi->dp_amount > 0)
                                    <div class="text-xs">
                                        <p class="font-semibold text-primary">Rp
                                            {{ number_format($reservasi->dp_amount, 0, ',', '.') }}</p>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium
                {{ $reservasi->dp_status === 'sudah_bayar' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $reservasi->dp_status === 'sudah_bayar' ? 'Lunas' : 'Belum' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="edit({{ $reservasi->id }})"
                                        class="rounded-lg bg-stone-900 px-3 py-1 text-xs font-bold text-white hover:bg-stone-700 transition-colors">
                                        Edit
                                    </button>
                                    <button wire:click="konfirmasiHapus({{ $reservasi->id }})"
                                        class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-xs font-bold text-red-600 hover:bg-red-100 transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-sm text-stone-400">Belum ada
                                reservasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/40 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <h3 class="mb-5 text-xl font-black tracking-tight text-stone-900">
                    {{ $editId ? 'Edit Reservasi' : 'Tambah Reservasi Baru' }}
                </h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Nama
                                Pelanggan</label>
                            <input wire:model="nama_pelanggan" type="text" placeholder="Nama lengkap"
                                class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" />
                            @error('nama_pelanggan')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Nomor
                                HP</label>
                            <input wire:model="nomor_hp" type="text" placeholder="08xx-xxxx-xxxx"
                                class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" />
                            @error('nomor_hp')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Pilih
                            Meja</label>
                        <select wire:model="table_id"
                            class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <option value="">Pilih meja...</option>
                            @foreach ($mejas->groupBy('zona') as $zona => $mejasZona)
                                <optgroup
                                    label="{{ match ($zona) {
                                        'rooftop_a' => 'Rooftop A',
                                        'rooftop_b' => 'Rooftop B',
                                        'indoor' => 'Indoor',
                                        'bangku' => 'Bangku',
                                        'lesehan' => 'Lesehan',
                                        default => $zona,
                                    } }}">
                                    @foreach ($mejasZona as $meja)
                                        <option value="{{ $meja->id }}">{{ $meja->nama_meja }}
                                            ({{ $meja->kapasitas }} pax)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('table_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Tanggal</label>
                            <input wire:model="tanggal" type="date"
                                class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" />
                            @error('tanggal')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Jam
                                Mulai</label>
                            <input wire:model="jam_mulai" type="time"
                                class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" />
                            @error('jam_mulai')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Jam
                                Selesai</label>
                            <input wire:model="jam_selesai" type="time"
                                class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" />
                            @error('jam_selesai')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Jumlah
                            Tamu</label>
                        <input wire:model="jumlah_tamu" type="number" min="1" placeholder="Contoh: 4"
                            class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" />
                        @error('jumlah_tamu')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-500">Status</label>
                        <select wire:model="status"
                            class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            <option value="pending">Pending</option>
                            <option value="dikonfirmasi">Dikonfirmasi</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>

                    {{-- DP Section --}}
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 space-y-3">
                        <p class="text-xs font-bold text-amber-700">Uang Muka (DP)</p>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-text">Jumlah DP (Rp)</label>
                            <input wire:model="dp_amount" type="number" min="0"
                                placeholder="0 = tidak ada DP"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                            @error('dp_amount')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        @if ($dp_amount > 0)
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-text">Metode DP</label>
                                    <select wire:model="dp_metode"
                                        class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                        <option value="tunai">Tunai</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-text">Status DP</label>
                                    <select wire:model="dp_status"
                                        class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                        <option value="belum_bayar">Belum Bayar</option>
                                        <option value="sudah_bayar">Sudah Bayar</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>

                <div class="mt-6 flex gap-3">
                    <button wire:click="simpan"
                        class="flex-1 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-700 transition-colors">
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Reservasi' }}
                    </button>
                    <button wire:click="$set('showModal', false)"
                        class="flex-1 rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100 transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Hapus --}}
    @if ($showHapusModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/40 backdrop-blur-sm p-4">
            <div class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100">
                    <svg class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-stone-900">Hapus Reservasi?</h3>
                <p class="mt-2 mb-6 text-sm text-stone-500">Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex gap-3">
                    <button wire:click="hapus"
                        class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition-colors">
                        Ya, Hapus
                    </button>
                    <button wire:click="$set('showHapusModal', false)"
                        class="flex-1 rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100 transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
