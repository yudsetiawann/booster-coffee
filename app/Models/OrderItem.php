<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['order_id', 'menu_id', 'qty', 'harga_saat_pesan', 'catatan', 'status'])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'qty'             => 'integer',
            'harga_saat_pesan' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
