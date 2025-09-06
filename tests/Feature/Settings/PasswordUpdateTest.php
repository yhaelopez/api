<?php

use App\Helpers\TestHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Password Update', function () {
    test('password can be updated', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->from('/settings/password')
            ->put('/settings/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/password');

        expect(Hash::check('new-password', $admin->refresh()->password))->toBeTrue();
    });

    test('correct password must be provided to update password', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->from('/settings/password')
            ->put('/settings/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect('/settings/password');
    });

    test('password confirmation is required', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->from('/settings/password')
            ->put('/settings/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/settings/password');
    });

    test('unauthenticated user cannot update password', function () {
        $response = $this->put('/settings/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('login'));
    });
});
