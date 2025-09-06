<?php

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Dashboard', function () {
    test('superadmin can access dashboard', function () {
        // Act as superadmin admin
        $admin = Admin::factory()->superadmin()->create();
        $this->actingAs($admin, GuardEnum::ADMIN->value);

        // Act
        $response = $this->get(route('dashboard'));

        // Assert
        $response->assertStatus(200);
    });

    test('unauthenticated user is redirected to login', function () {
        // Act
        $response = $this->get(route('dashboard'));

        // Assert
        $response->assertRedirect(route('login'));
    });
});
