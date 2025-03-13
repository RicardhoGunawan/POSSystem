<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role sudah ada
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);

        // Buat user admin
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'), // Ganti dengan password yang lebih aman
        ]);
        $admin->assignRole($adminRole);

        // Buat user cashier
        $cashier = User::firstOrCreate([
            'email' => 'cashier@example.com',
        ], [
            'name' => 'Cashier User',
            'password' => Hash::make('password'), // Ganti dengan password yang lebih aman
        ]);
        $cashier->assignRole($cashierRole);
    }
}
