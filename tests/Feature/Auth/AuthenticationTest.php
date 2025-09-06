<?php

use App\Helpers\TestHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Authentication', function () {
    test('login screen can be rendered', function () {
        $response = $this->get('/login');

        $response->assertStatus(200);
    });

    test('admins can authenticate using the login screen', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated('admin');
        $response->assertRedirect(route('dashboard', absolute: false));
    });

    test('admins can not authenticate with invalid password', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('admin');
    });

    test('admins can logout', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->post('/logout');

        $this->assertGuest('admin');
        $response->assertRedirect('/');
    });

    test('users cannot authenticate through admin login', function () {
        $user = \App\Models\User::factory()->regularUser()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest('admin');
    });
});
