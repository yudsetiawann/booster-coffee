<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['menu_id', 'ingredient_id', 'jumlah_pakai'])]
class Recipe extends Model
{
    protected function casts(): array
    {
        return [
            'jumlah_pakai' => 'float',
        ];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
