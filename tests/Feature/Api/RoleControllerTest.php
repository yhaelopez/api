<?php

namespace Tests\Feature\Api;

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

test('superadmin can view all roles', function () {
    // Act as superadmin
    $superadmin = TestHelper::createTestSuperAdmin();
    $this->actingAs($superadmin, GuardEnum::ADMIN->value);

    // Act - Get all roles
    $response = $this->getJson(route('roles.index'));

    // Assert - Check response structure and data
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'guard_name',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);

    // Should have at least 1 role (member for API guard)
    $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    
    // All roles should be from API guard (user roles)
    $roles = $response->json('data');
    foreach ($roles as $role) {
        $this->assertEquals('api', $role['guard_name']);
    }
});

test('authorized user can view all roles', function () {
    // Act as user with view permission
    $user = TestHelper::createTestUser();
    $user->givePermissionTo('users.viewAny');
    $this->actingAs($user, GuardEnum::API->value);

    // Act - Get all roles
    $response = $this->getJson(route('roles.index'));

    // Assert - Should succeed
    $response->assertStatus(200);
    
    // All roles should be from API guard (user roles)
    $roles = $response->json('data');
    foreach ($roles as $role) {
        $this->assertEquals('api', $role['guard_name']);
    }
});

test('unauthorized user cannot view roles', function () {
    // Act as unauthorized user
    $user = TestHelper::createTestUnauthorizedUser();
    $this->actingAs($user, GuardEnum::API->value);

    // Act - Try to get roles
    $response = $this->getJson(route('roles.index'));

    // Assert - Should be forbidden
    $response->assertStatus(403);
});

test('unauthenticated user cannot view roles', function () {
    // Create a test without authentication
    $this->refreshApplication();

    // Act - Try to get roles without authentication
    $response = $this->getJson(route('roles.index'));

    // Assert - Check for 401 Unauthorized
    $response->assertStatus(401);
});
