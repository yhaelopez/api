<?php

namespace App\Helpers;

use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Faker\Factory;

class TestHelper
{
    private static $faker;

    /**
     * Get or create faker instance
     */
    private static function getFaker()
    {
        if (! self::$faker) {
            self::$faker = Factory::create();
        }

        return self::$faker;
    }

    /**
     * Create permissions and roles for testing
     */
    public static function createPermissionsAndRoles(): void
    {
        // Use the seeders to create permissions and roles
        $permissionsSeeder = new PermissionsSeeder;
        $permissionsSeeder->run();

        $rolesSeeder = new RolesSeeder;
        $rolesSeeder->run();
    }

    /**
     * Create a superadmin user for testing
     *
     * @return User The created superadmin user
     */
    public static function createTestSuperAdmin(): User
    {
        return User::factory()->superadmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@'.self::getFaker()->domainName(),
        ]);
    }

    /**
     * Create a regular user for testing
     *
     * @return User The created regular user
     */
    public static function createTestUser(): User
    {
        return User::factory()->regularUser()->create([
            'name' => 'Regular User',
            'email' => 'user@'.self::getFaker()->domainName(),
        ]);
    }

    /**
     * Create an unauthorized user for testing (user with no permissions)
     *
     * @return User The created unauthorized user
     */
    public static function createTestUnauthorizedUser(): User
    {
        return User::factory()->unauthorized()->create([
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@'.self::getFaker()->domainName(),
        ]);
    }
}
