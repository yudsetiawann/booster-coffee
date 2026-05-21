<?php

use Livewire\Volt\Component;
use App\Models\Reservation;
use App\Models\Table;

new class extends Component {
    public string $nama_pelanggan = '';
    public string $nomor_hp = '';
    public ?int $table_id = null;
    public string $tanggal = '';
    public string $jam_mulai = '';
    public string $jam_selesai = '';
    public int|string $jumlah_tamu = '';
    public bool $showBerhasil = false;

    public function simpan(): void
    {
        $this->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'nomor_hp' => 'required|string|max:20',
            'table_id' => 'required|exists:tables,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'jumlah_tamu' => 'required|integer|min:1',
        ]);

        Reservation::create([
            'nama_pelanggan' => $this->nama_pelanggan,
            'nomor_hp' => $this->nomor_hp,
            'table_id' => $this->table_id,
            'tanggal' => $this->tanggal,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'jumlah_tamu' => $this->jumlah_tamu,
            'status' => 'pending',
            'dp_amount' => 0,
            'dp_status' => 'belum_bayar',
        ]);

        $this->reset(['nama_pelanggan', 'nomor_hp', 'table_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'jumlah_tamu']);
        $this->showBerhasil = true;
    }

    public function with(): array
    {
        $mejas = Table::orderBy('zona')->orderBy('posisi_x')->get();
        return compact('mejas');
    }
}; ?>

<div>

    @if ($showBerhasil)
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-5 text-center">
            <p class="text-2xl mb-2">🎉</p>
            <p class="text-lg font-bold text-green-700">Reservasi Berhasil!</p>
            <p class="text-sm text-green-600 mt-1">Kami akan segera menghubungi kamu untuk konfirmasi.</p>
            <p class="text-sm text-green-600">Terima kasih telah memilih Booster Coffee!</p>
            <button wire:click="$set('showBerhasil', false)"
                class="mt-4 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                Buat Reservasi Lain
            </button>
        </div>
    @else
        <div class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
            <h1 class="mb-1 text-xl font-bold text-primary-dark">Reservasi Meja</h1>
            <p class="mb-6 text-sm text-text-muted">Isi form di bawah untuk memesan meja di Booster Coffee</p>

            <div class="space-y-4">

                {{-- Nama & HP --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Nama Lengkap</label>
                        <input wire:model="nama_pelanggan" type="text" placeholder="Contoh: Budi Santoso"
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        @error('nama_pelanggan')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Nomor HP / WhatsApp</label>
                        <input wire:model="nomor_hp" type="text" placeholder="08xx-xxxx-xxxx"
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        @error('nomor_hp')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Pilih Meja --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-text">Pilih Meja</label>
                    <select wire:model="table_id"
                        class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
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
                                    <option value="{{ $meja->id }}">{{ $meja->nama_meja }} ({{ $meja->kapasitas }}
                                        pax)</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('table_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tanggal & Jumlah Tamu --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Tanggal</label>
                        <input wire:model="tanggal" type="date" min="{{ now()->format('Y-m-d') }}"
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        @error('tanggal')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Jumlah Tamu</label>
                        <input wire:model="jumlah_tamu" type="number" min="1" placeholder="Contoh: 4"
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        @error('jumlah_tamu')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Jam --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Jam Mulai</label>
                        <input wire:model="jam_mulai" type="time"
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        @error('jam_mulai')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text">Jam Selesai</label>
                        <input wire:model="jam_selesai" type="time"
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        @error('jam_selesai')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Info --}}
                <div class="rounded-lg bg-amber-50 p-3 text-xs text-amber-700">
                    Reservasi akan dikonfirmasi oleh tim kami melalui WhatsApp dalam 1x24 jam.
                </div>

                {{-- Submit --}}
                <button wire:click="simpan"
                    class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white hover:bg-primary-dark">
                    Kirim Reservasi
                </button>

            </div>
        </div>

    @endif

</div>
