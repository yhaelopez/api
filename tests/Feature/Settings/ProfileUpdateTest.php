<?php

use App\Helpers\TestHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Profile Update', function () {
    test('profile page is displayed', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->get('/settings/profile');

        $response->assertOk();
    });

    test('profile information can be updated', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $email = $this->faker->email;

        $response = $this
            ->actingAs($admin, 'admin')
            ->patch('/settings/profile', [
                'name' => 'Test Admin',
                'email' => $email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $admin->refresh();

        expect($admin->name)->toBe('Test Admin');
        expect($admin->email)->toBe($email);
        expect($admin->email_verified_at)->toBeNull();
    });

    test('email verification status is unchanged when the email address is unchanged', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->patch('/settings/profile', [
                'name' => 'Test Admin',
                'email' => $admin->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        expect($admin->refresh()->email_verified_at)->not->toBeNull();
    });

    test('admin can delete their account', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->delete('/settings/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest('admin');
        expect($admin->fresh())->toBeNull();
    });

    test('correct password must be provided to delete account', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->from('/settings/profile')
            ->delete('/settings/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/settings/profile');

        expect($admin->fresh())->not->toBeNull();
    });

    test('unauthenticated user cannot access profile page', function () {
        $response = $this->get('/settings/profile');

        $response->assertRedirect(route('login'));
    });

    test('profile update requires valid email format', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this
            ->actingAs($admin, 'admin')
            ->from('/settings/profile')
            ->patch('/settings/profile', [
                'name' => 'Test Admin',
                'email' => 'invalid-email',
            ]);

        $response
            ->assertSessionHasErrors('email')
            ->assertRedirect('/settings/profile');
    });
});
