<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['order_id', 'jumlah_bayar', 'metode', 'is_split', 'nama_pembayar'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'jumlah_bayar' => 'decimal:2',
            'is_split'     => 'boolean',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
