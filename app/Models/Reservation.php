<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'table_id',
    'nama_pelanggan',
    'nomor_hp',
    'tanggal',
    'jam_mulai',
    'jam_selesai',
    'jumlah_tamu',
    'status',
    'dp_amount',
    'dp_status',
    'dp_metode',
])]
class Reservation extends Model
{
    protected function casts(): array
    {
        return [
            'tanggal'     => 'date',
            'jumlah_tamu' => 'integer',
        ];
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function isDpLunas(): bool
    {
        return $this->dp_status === 'sudah_bayar';
    }

    public function hasDp(): bool
    {
        return $this->dp_amount > 0;
    }
}
