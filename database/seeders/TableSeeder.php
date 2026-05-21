<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Table;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            // Rooftop B
            ['nama_meja' => 'Lisna', 'zona' => 'rooftop_b', 'kapasitas' => 5, 'posisi_x' => 1, 'posisi_y' => 1],
            ['nama_meja' => 'Rilla', 'zona' => 'rooftop_b', 'kapasitas' => 8, 'posisi_x' => 2, 'posisi_y' => 1],
            ['nama_meja' => 'Nur',   'zona' => 'rooftop_b', 'kapasitas' => 4, 'posisi_x' => 3, 'posisi_y' => 1],

            // Indoor
            ['nama_meja' => 'Mega',    'zona' => 'indoor', 'kapasitas' => 15, 'posisi_x' => 1, 'posisi_y' => 2],
            ['nama_meja' => 'Laila',   'zona' => 'indoor', 'kapasitas' => 3,  'posisi_x' => 2, 'posisi_y' => 2],
            ['nama_meja' => 'Lyafitri', 'zona' => 'indoor', 'kapasitas' => 3,  'posisi_x' => 3, 'posisi_y' => 2],
            ['nama_meja' => 'Fara',    'zona' => 'indoor', 'kapasitas' => 2,  'posisi_x' => 4, 'posisi_y' => 2],

            // Bangku
            ['nama_meja' => 'Dian', 'zona' => 'bangku', 'kapasitas' => 5, 'posisi_x' => 1, 'posisi_y' => 3],

            // Lesehan
            ['nama_meja' => 'M.Aisyah', 'zona' => 'lesehan', 'kapasitas' => 6,  'posisi_x' => 1, 'posisi_y' => 4],
            ['nama_meja' => 'Huda',     'zona' => 'lesehan', 'kapasitas' => 10, 'posisi_x' => 2, 'posisi_y' => 4],
            ['nama_meja' => 'Ariyani',  'zona' => 'lesehan', 'kapasitas' => 10, 'posisi_x' => 1, 'posisi_y' => 5],
            ['nama_meja' => 'Nitha',    'zona' => 'lesehan', 'kapasitas' => 6,  'posisi_x' => 2, 'posisi_y' => 5],
            ['nama_meja' => 'Winda',    'zona' => 'lesehan', 'kapasitas' => 7,  'posisi_x' => 3, 'posisi_y' => 4],
            ['nama_meja' => 'Salwa',    'zona' => 'lesehan', 'kapasitas' => 7,  'posisi_x' => 4, 'posisi_y' => 4],
            ['nama_meja' => 'Ilaa',     'zona' => 'lesehan', 'kapasitas' => 6,  'posisi_x' => 4, 'posisi_y' => 5],
            ['nama_meja' => 'Tanti',    'zona' => 'lesehan', 'kapasitas' => 7,  'posisi_x' => 3, 'posisi_y' => 5],
            ['nama_meja' => 'Indri',    'zona' => 'lesehan', 'kapasitas' => 6,  'posisi_x' => 4, 'posisi_y' => 6],

            // Rooftop A
            ['nama_meja' => 'Fauziah', 'zona' => 'rooftop_a', 'kapasitas' => 20, 'posisi_x' => 1, 'posisi_y' => 7],
            ['nama_meja' => 'Sri',     'zona' => 'rooftop_a', 'kapasitas' => 6,  'posisi_x' => 2, 'posisi_y' => 7],
        ];

        foreach ($tables as $table) {
            Table::create(array_merge($table, ['status' => 'tersedia']));
        }
    }
}
