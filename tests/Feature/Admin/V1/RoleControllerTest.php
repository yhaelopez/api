<?php

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Role API', function () {
    describe('index', function () {
        test('member user can view roles', function () {
            // Act as member user
            $admin = Admin::factory()->superadmin()->create();
            $this->actingAs($admin, GuardEnum::ADMIN->value);

            // Act
            $response = $this->getJson(route('admin.v1.roles.index'));

            // Assert
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'links' => ['first', 'last', 'prev', 'next'],
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

            // Verify all returned roles are from the API guard
            $roles = $response->json('data');
            foreach ($roles as $role) {
                expect($role['guard_name'])->toBe(GuardEnum::API->value);
            }
        });

        test('returns only API roles', function () {
            // Act as member user
            $admin = Admin::factory()->superadmin()->create();
            $this->actingAs($admin, GuardEnum::ADMIN->value);

            // Act
            $response = $this->getJson(route('admin.v1.roles.index'));

            // Assert
            $response->assertStatus(200);

            // Verify all returned roles are from the API guard
            $roles = $response->json('data');
            foreach ($roles as $role) {
                expect($role['guard_name'])->toBe(GuardEnum::API->value);
            }

            // Verify we have the member role
            $roleNames = collect($roles)->pluck('name')->toArray();
            expect($roleNames)->toContain('member');
        });

        test('unauthenticated user cannot view roles', function () {
            // Act
            $response = $this->getJson(route('admin.v1.roles.index'));

            // Assert
            $response->assertStatus(401);
        });
    });
});
