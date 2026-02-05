<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@rillbite.com',
            'password' => Hash::make('admin123'), // Ganti dengan password yang kuat
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Bisa tambah admin lain
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@rillbite.com',
            'password' => Hash::make('superadmin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
