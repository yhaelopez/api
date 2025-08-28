<?php

namespace Database\Factories;

use App\Enums\GuardEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

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
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a superadmin user with all permissions
     */
    public function superadmin(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', RoleEnum::SUPERADMIN->value)
                ->where('guard_name', GuardEnum::WEB->value)
                ->first();

            $user->assignRole($role);
        });
    }

    /**
     * Create a regular user with basic role
     */
    public function regularUser(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', RoleEnum::USER->value)
                ->where('guard_name', GuardEnum::WEB->value)
                ->first();

            $user->assignRole($role);
        });
    }

    /**
     * Create an unauthorized user with no roles or permissions
     */
    public function unauthorized(): static
    {
        return $this->afterCreating(function (User $user) {
            // No roles or permissions assigned - this user has no access
        });
    }
}
