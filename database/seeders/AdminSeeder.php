<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a super admin
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'yhaelopez@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Assign superadmin role
        $superAdmin->assignRole(RoleEnum::SUPERADMIN->value);
    }
}
