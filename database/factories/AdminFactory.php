<?php

namespace Database\Factories;

use App\Enums\GuardEnum;
use App\Enums\RoleEnum;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'spotify_id' => null,
            'google_id' => null,
        ];
    }

    /**
     * Create a superadmin admin with all permissions
     */
    public function superadmin(): static
    {
        return $this->afterCreating(function (Admin $admin) {
            $role = Role::where('name', RoleEnum::SUPERADMIN->value)
                ->where('guard_name', GuardEnum::ADMIN->value)
                ->first();

            $admin->assignRole($role);
        });
    }

    /**
     * Create an unverified admin
     */
    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
