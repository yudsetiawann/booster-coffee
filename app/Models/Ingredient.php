<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_bahan', 'stok_saat_ini', 'satuan', 'stok_minimum'])]
class Ingredient extends Model
{
    protected function casts(): array
    {
        return [
            'stok_saat_ini' => 'float',
            'stok_minimum'  => 'float',
        ];
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
}
