<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name'              => 'Admin Booster',
            'email'             => 'admin@boostercoffee.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Kasir
        $kasir = User::create([
            'name'              => 'Kasir 1',
            'email'             => 'kasir@boostercoffee.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $kasir->assignRole('kasir');

        // Dapur
        $dapur = User::create([
            'name'              => 'Staf Dapur',
            'email'             => 'dapur@boostercoffee.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $dapur->assignRole('dapur');
    }
}
