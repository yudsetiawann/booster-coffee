<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_meja', 'zona', 'kapasitas', 'status', 'posisi_x', 'posisi_y'])]
class Table extends Model
{
    protected function casts(): array
    {
        return [
            'kapasitas' => 'integer',
            'posisi_x'  => 'integer',
            'posisi_y'  => 'integer',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
