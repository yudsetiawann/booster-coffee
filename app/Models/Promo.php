<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_promo', 'tipe', 'nilai', 'aktif', 'berlaku_mulai', 'berlaku_sampai'])]
class Promo extends Model
{
    protected function casts(): array
    {
        return [
            'nilai'          => 'decimal:2',
            'aktif'          => 'boolean',
            'berlaku_mulai'  => 'date',
            'berlaku_sampai' => 'date',
        ];
    }
}
