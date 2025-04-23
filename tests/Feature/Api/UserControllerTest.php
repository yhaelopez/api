<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function() {
    // Create and authenticate a user for testing
    $user = User::factory()->create();
    Sanctum::actingAs($user);
});

test('index endpoint returns paginated users', function() {
    // Create test users
    User::factory()->count(20)->create();

    // Act - Get the first page with 10 users per page
    $response = $this->getJson(route('users.index', ['page' => 1, 'per_page' => 10]));

    // Assert - Check response structure and data
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
                'total'
            ]
        ])
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.current_page', 1);
});

test('index endpoint validates input parameters', function() {
    // Act - Try with invalid parameters
    $response = $this->getJson(route('users.index', ['page' => 'invalid', 'per_page' => 'invalid']));

    // Assert - Check validation errors
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['page', 'per_page']);
});

test('show endpoint returns the correct user', function() {
    // Create test user
    $user = User::factory()->create();

    // Act - Request specific user
    $response = $this->getJson(route('users.show', $user->id));

    // Assert - Check response structure and data
    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
            'deleted_at',
        ])
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('name', $user->name)
        ->assertJsonPath('email', $user->email);
});

test('show endpoint returns 404 for non-existent user', function() {
    // Act - Request non-existent user
    $response = $this->getJson(route('users.show', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
});

test('destroy endpoint deletes the user', function() {
    // Create test user
    $user = User::factory()->create();

    // Act - Delete the user
    $response = $this->deleteJson(route('users.destroy', $user->id));

    // Assert - Check response and database
    $response->assertStatus(200)
        ->assertJson(['message' => 'User deleted successfully']);

    $this->assertSoftDeleted($user);
});

test('destroy endpoint returns 404 for non-existent user', function() {
    // Act - Try to delete non-existent user
    $response = $this->deleteJson(route('users.destroy', 999999));

    // Assert - Check 404 response
    $response->assertStatus(404);
    // We only care about the status code, not the exact error message
});
