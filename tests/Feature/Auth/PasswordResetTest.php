<?php

use App\Helpers\TestHelper;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Password Reset', function () {
    test('reset password link screen can be rendered', function () {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    });

    test('reset password link can be requested for admin', function () {
        Notification::fake();

        $admin = TestHelper::createTestSuperAdmin();

        $this->post('/forgot-password', ['email' => $admin->email]);

        Notification::assertSentTo($admin, ResetPassword::class);
    });

    test('reset password screen can be rendered', function () {
        Notification::fake();

        $admin = TestHelper::createTestSuperAdmin();

        $this->post('/forgot-password', ['email' => $admin->email]);

        Notification::assertSentTo($admin, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    });

    test('password can be reset with valid token', function () {
        Notification::fake();

        $admin = TestHelper::createTestSuperAdmin();

        $this->post('/forgot-password', ['email' => $admin->email]);

        Notification::assertSentTo($admin, ResetPassword::class, function ($notification) use ($admin) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $admin->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    });

    test('reset password link cannot be requested for non-admin email', function () {
        Notification::fake();

        $user = \App\Models\User::factory()->regularUser()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertNotSentTo($user, ResetPassword::class);
    });

    test('password reset fails with invalid token', function () {
        $admin = TestHelper::createTestSuperAdmin();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $admin->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['email']);
    });
});
