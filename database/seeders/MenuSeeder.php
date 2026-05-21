<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // Kopi
            ['nama_menu' => 'Extra Shot',           'kategori' => 'kopi', 'harga' => 5000],
            ['nama_menu' => 'Espresso',              'kategori' => 'kopi', 'harga' => 10000],
            ['nama_menu' => 'Americano',             'kategori' => 'kopi', 'harga' => 15000],
            ['nama_menu' => 'Vietnam Drip',          'kategori' => 'kopi', 'harga' => 15000],
            ['nama_menu' => 'Kopi Susu',             'kategori' => 'kopi', 'harga' => 15000],
            ['nama_menu' => 'Avogato Ice Cream',     'kategori' => 'kopi', 'harga' => 15000],
            ['nama_menu' => 'Signature Booster',     'kategori' => 'kopi', 'harga' => 20000],
            ['nama_menu' => 'Caffe Latte',           'kategori' => 'kopi', 'harga' => 20000],
            ['nama_menu' => 'Machiato Lezato',       'kategori' => 'kopi', 'harga' => 20000],
            ['nama_menu' => 'Kopi Gula Aren',        'kategori' => 'kopi', 'harga' => 20000],
            ['nama_menu' => 'Cappucino Neverno',     'kategori' => 'kopi', 'harga' => 20000],
            ['nama_menu' => 'Moccacino',             'kategori' => 'kopi', 'harga' => 20000],
            ['nama_menu' => 'Duren Latte',           'kategori' => 'kopi', 'harga' => 25000],
            ['nama_menu' => 'Curacao Latte',         'kategori' => 'kopi', 'harga' => 25000],
            ['nama_menu' => 'Creamy Avocado',        'kategori' => 'kopi', 'harga' => 25000],
            ['nama_menu' => 'Scotch Mocha',          'kategori' => 'kopi', 'harga' => 25000],
            ['nama_menu' => 'Kopi Klepon',           'kategori' => 'kopi', 'harga' => 25000],

            // Non Kopi
            ['nama_menu' => 'Taro Milk',             'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Chocolatte',            'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Cotton Milk',           'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Marqisah Squash',       'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Mango Squash',          'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Soda Stawberry',        'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Yakult Lychee',         'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Yakult Stawberry',      'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Sparkling Violet',      'kategori' => 'non_kopi', 'harga' => 20000],
            ['nama_menu' => 'Sparkling Blue Sky',    'kategori' => 'non_kopi', 'harga' => 25000],
            ['nama_menu' => 'Choco Avocado',         'kategori' => 'non_kopi', 'harga' => 25000],
            ['nama_menu' => 'Choco Durian',          'kategori' => 'non_kopi', 'harga' => 25000],
            ['nama_menu' => 'Jahe Susu Rempah',      'kategori' => 'non_kopi', 'harga' => 25000],
            ['nama_menu' => 'Klepon Original',       'kategori' => 'non_kopi', 'harga' => 25000],
            ['nama_menu' => 'Matcha Latte',          'kategori' => 'non_kopi', 'harga' => 25000],
            ['nama_menu' => 'Red Velvet',            'kategori' => 'non_kopi', 'harga' => 25000],
            ['nama_menu' => 'Orange Fresh',          'kategori' => 'non_kopi', 'harga' => 25000],

            // Tea
            ['nama_menu' => 'Teh Tawar',             'kategori' => 'tea', 'harga' => 10000],
            ['nama_menu' => 'Teh Manis',             'kategori' => 'tea', 'harga' => 15000],
            ['nama_menu' => 'Current Tea',           'kategori' => 'tea', 'harga' => 20000],
            ['nama_menu' => 'Thai Tea',              'kategori' => 'tea', 'harga' => 20000],
            ['nama_menu' => 'Lemon Tea',             'kategori' => 'tea', 'harga' => 20000],
            ['nama_menu' => 'Lychee Tea',            'kategori' => 'tea', 'harga' => 20000],
            ['nama_menu' => 'Teh Tarik',             'kategori' => 'tea', 'harga' => 20000],

            // Main Course
            ['nama_menu' => 'Extra Nasi',                    'kategori' => 'main_course', 'harga' => 8000],
            ['nama_menu' => 'Nasi Goreng',                   'kategori' => 'main_course', 'harga' => 35000],
            ['nama_menu' => 'Mie Goreng',                    'kategori' => 'main_course', 'harga' => 35000],
            ['nama_menu' => 'Mie Rebus',                     'kategori' => 'main_course', 'harga' => 35000],
            ['nama_menu' => 'Kwetiau Goreng',                'kategori' => 'main_course', 'harga' => 35000],
            ['nama_menu' => 'Kwetiau Rebus',                 'kategori' => 'main_course', 'harga' => 35000],
            ['nama_menu' => 'Ayam Bakar Bumbu Kacang + Nasi', 'kategori' => 'main_course', 'harga' => 36000],
            ['nama_menu' => 'Ayam Goreng Kremes + Nasi',     'kategori' => 'main_course', 'harga' => 36000],
            ['nama_menu' => 'Bebek Goreng Kremes + Nasi',    'kategori' => 'main_course', 'harga' => 60000],
            ['nama_menu' => 'Sop Iga + Nasi',                'kategori' => 'main_course', 'harga' => 75000],
            ['nama_menu' => 'Iga Bakar Bumbu Kacang + Nasi', 'kategori' => 'main_course', 'harga' => 90000],

            // Pasta
            ['nama_menu' => 'Spaghetti Bolognase',   'kategori' => 'pasta', 'harga' => 35000],
            ['nama_menu' => 'Fettuccine Carbonara',  'kategori' => 'pasta', 'harga' => 35000],
            ['nama_menu' => 'Mac N Cheese',          'kategori' => 'pasta', 'harga' => 35000],

            // Snack
            ['nama_menu' => 'Otak - Otak',           'kategori' => 'snack', 'harga' => 20000],
            ['nama_menu' => 'Rujak Cireng',          'kategori' => 'snack', 'harga' => 20000],
            ['nama_menu' => 'Risol Ayam',            'kategori' => 'snack', 'harga' => 20000],
            ['nama_menu' => 'Pastel Daging',         'kategori' => 'snack', 'harga' => 24000],
            ['nama_menu' => 'Risol Beefmayo',        'kategori' => 'snack', 'harga' => 25000],
            ['nama_menu' => 'Sosis Goreng',          'kategori' => 'snack', 'harga' => 25000],
            ['nama_menu' => 'Kentang Goreng',        'kategori' => 'snack', 'harga' => 25000],
            ['nama_menu' => 'Pisang Bakar',          'kategori' => 'snack', 'harga' => 25000],
            ['nama_menu' => 'Bakso Goreng',          'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Molen Coklat',          'kategori' => 'snack', 'harga' => 25000],
            ['nama_menu' => 'Roti Maryam Coklat',   'kategori' => 'snack', 'harga' => 25000],
            ['nama_menu' => 'Sandwich',              'kategori' => 'snack', 'harga' => 20000],
            ['nama_menu' => 'Nugget Ayam',           'kategori' => 'snack', 'harga' => 30000],
            ['nama_menu' => 'Cireng Booster',        'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Donat Kentang',         'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Bakpau Coklat',         'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Chicken Wings',         'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Roti Bakar Coklat Keju', 'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Roti Goreng Coklat Keju', 'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Roti Goreng Coklat',   'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Pastel Sayur',          'kategori' => 'snack', 'harga' => 28000],
            ['nama_menu' => 'Siomay Bumbu Kacang',  'kategori' => 'snack', 'harga' => 29000],
            ['nama_menu' => 'Roti Maryam Coklat Keju', 'kategori' => 'snack', 'harga' => 30000],
            ['nama_menu' => 'Dimsum',                'kategori' => 'snack', 'harga' => 30000],
            ['nama_menu' => 'Mix Snack Platter',     'kategori' => 'snack', 'harga' => 35000],
            ['nama_menu' => 'Sambosa Cream Cheese',  'kategori' => 'snack', 'harga' => 32000],
            ['nama_menu' => 'Sambosa Daging',        'kategori' => 'snack', 'harga' => 32000],
            ['nama_menu' => 'Kebab Daging',          'kategori' => 'snack', 'harga' => 36000],

            // Dessert
            ['nama_menu' => 'Puding Kelapa',         'kategori' => 'dessert', 'harga' => 30000],
            ['nama_menu' => 'Puding Kelapa Cup',     'kategori' => 'dessert', 'harga' => 15000],
            ['nama_menu' => 'Ice Cream',             'kategori' => 'dessert', 'harga' => 15000],
            ['nama_menu' => 'Jongkong Kelapa Cup',   'kategori' => 'dessert', 'harga' => 15000],

            // Others
            ['nama_menu' => 'Air Mineral 750 Ml',    'kategori' => 'others', 'harga' => 15000],
            ['nama_menu' => 'Extra Ice',             'kategori' => 'others', 'harga' => 3000],
            ['nama_menu' => 'Shisha Booster',        'kategori' => 'others', 'harga' => 65000],
        ];

        foreach ($menus as $menu) {
            Menu::create(array_merge($menu, [
                'tersedia' => true,
                'deskripsi' => null,
                'foto' => null,
            ]));
        }
    }
}
