<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_menu', 'kategori', 'deskripsi', 'harga', 'tersedia', 'foto'])]
class Menu extends Model
{
    protected function casts(): array
    {
        return [
            'harga'    => 'decimal:2',
            'tersedia' => 'boolean',
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
}
