<?php

use App\Helpers\TestHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Password Confirmation', function () {
    test('confirm password screen can be rendered', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->get('/confirm-password');

        $response->assertStatus(200);
    });

    test('password can be confirmed', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    });

    test('password is not confirmed with invalid password', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    });

    test('password confirmation sets session timestamp', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('auth.password_confirmed_at');
    });

    test('unauthenticated user cannot access confirm password screen', function () {
        $response = $this->get('/confirm-password');

        $response->assertRedirect(route('login'));
    });
});
