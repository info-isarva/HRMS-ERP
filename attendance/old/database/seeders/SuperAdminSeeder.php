<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@hrms.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'financial_year' => '2024-2025',
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@hrms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'financial_year' => '2024-2025',
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@hrms.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'financial_year' => '2024-2025',
        ]);
    }
}
