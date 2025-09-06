<?php

use App\Helpers\TestHelper;
use App\Models\Admin;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('Email Verification', function () {
    test('email verification screen can be rendered', function () {
        $admin = Admin::factory()->unverified()->create();

        $response = $this->actingAs($admin, 'admin')->get('/verify-email');

        $response->assertStatus(200);
    });

    test('email can be verified', function () {
        $admin = Admin::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $admin->id, 'hash' => sha1($admin->email)]
        );

        $response = $this->actingAs($admin, 'admin')->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        expect($admin->fresh()->hasVerifiedEmail())->toBeTrue();
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    });

    test('email is not verified with invalid hash', function () {
        $admin = Admin::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $admin->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($admin, 'admin')->get($verificationUrl);

        expect($admin->fresh()->hasVerifiedEmail())->toBeFalse();
    });

    test('verified admin is redirected to dashboard', function () {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/verify-email');

        $response->assertRedirect(route('dashboard', absolute: false));
    });

    test('email verification notification can be sent', function () {
        $admin = Admin::factory()->unverified()->create();

        $response = $this->actingAs($admin, 'admin')->post('/email/verification-notification');

        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
    });
});
