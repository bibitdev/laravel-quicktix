<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@curugpinang.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@curugpinang.com',
                'password' => Hash::make('admin123'),
                'phone' => '081234567890',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create default staff user
        User::updateOrCreate(
            ['email' => 'staff@curugpinang.com'],
            [
                'name' => 'Staff Loket',
                'email' => 'staff@curugpinang.com',
                'password' => Hash::make('staff123'),
                'phone' => '081234567891',
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );
    }
}
