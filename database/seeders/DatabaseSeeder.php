<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Always run permissions seeder first since roles depend on permissions
        $this->call(PermissionsSeeder::class);

        // Then run roles seeder
        $this->call(RolesSeeder::class);

        // Other seeders can go here
        $this->call(AdminSeeder::class);
    }
}
