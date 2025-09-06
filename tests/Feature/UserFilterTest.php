<?php

use App\Enums\GuardEnum;
use App\Helpers\TestHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Create permissions and roles for all tests
    TestHelper::createPermissionsAndRoles();
});

describe('User Filtering', function () {
    beforeEach(function () {
        // Act as superadmin to access user management
        $superadmin = TestHelper::createTestSuperAdmin();
        $this->actingAs($superadmin, GuardEnum::ADMIN->value);
    });

    describe('search filter', function () {
        test('filters users by name', function () {
            // Arrange
            User::factory()->create(['name' => 'John Doe']);
            User::factory()->create(['name' => 'Jane Smith']);
            User::factory()->create(['name' => 'Bob Johnson']);

            // Act
            $response = $this->getJson(route('v1.users.index', ['search' => 'John']));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(2);
            expect(collect($users)->pluck('name')->toArray())->toContain('John Doe');
            expect(collect($users)->pluck('name')->toArray())->toContain('Bob Johnson');
        });

        test('filters users by email', function () {
            // Arrange
            User::factory()->create(['email' => 'john@example.com']);
            User::factory()->create(['email' => 'jane@example.com']);
            User::factory()->create(['email' => 'bob@test.com']);

            // Act
            $response = $this->getJson(route('v1.users.index', ['search' => 'example.com']));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(2);
            expect(collect($users)->pluck('email')->toArray())->toContain('john@example.com');
            expect(collect($users)->pluck('email')->toArray())->toContain('jane@example.com');
        });

        test('returns empty results for non-matching search', function () {
            // Arrange
            User::factory()->create(['name' => 'John Doe']);

            // Act
            $response = $this->getJson(route('v1.users.index', ['search' => 'NonExistent']));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(0);
        });
    });

    describe('role filter', function () {
        test('filters users by role name', function () {
            // Arrange
            $memberRole = \Spatie\Permission\Models\Role::where('name', 'member')
                ->where('guard_name', GuardEnum::API->value)
                ->first();

            $user1 = User::factory()->create();
            $user1->assignRole($memberRole);

            $user2 = User::factory()->create();
            $user2->assignRole($memberRole);

            $user3 = User::factory()->create(); // No role

            // Act
            $response = $this->getJson(route('v1.users.index', ['role' => 'member']));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(2);
        });

        test('filters users by role id', function () {
            // Arrange
            $memberRole = \Spatie\Permission\Models\Role::where('name', 'member')
                ->where('guard_name', GuardEnum::API->value)
                ->first();

            $user1 = User::factory()->create();
            $user1->assignRole($memberRole);

            $user2 = User::factory()->create();
            $user2->assignRole($memberRole);

            $user3 = User::factory()->create(); // No role

            // Act
            $response = $this->getJson(route('v1.users.index', ['role_id' => $memberRole->id]));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(2);
        });
    });

    describe('date filters', function () {
        test('filters users by created date range', function () {
            // Arrange
            $user1 = User::factory()->create();
            $user1->created_at = now()->subDays(10);
            $user1->save();

            $user2 = User::factory()->create();
            $user2->created_at = now()->subDays(5);
            $user2->save();

            $user3 = User::factory()->create();
            $user3->created_at = now()->subDays(1);
            $user3->save();

            // Act
            $response = $this->getJson(route('v1.users.index', [
                'created_from' => now()->subDays(7)->format('Y-m-d'),
                'created_to' => now()->subDays(2)->format('Y-m-d'),
            ]));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(1);
        });

        test('filters users by updated date range', function () {
            // Arrange
            $user1 = User::factory()->create();
            $user1->updated_at = now()->subDays(10);
            $user1->save();

            $user2 = User::factory()->create();
            $user2->updated_at = now()->subDays(5);
            $user2->save();

            $user3 = User::factory()->create();
            $user3->updated_at = now()->subDays(1);
            $user3->save();

            // Act
            $response = $this->getJson(route('v1.users.index', [
                'updated_from' => now()->subDays(7)->format('Y-m-d'),
                'updated_to' => now()->subDays(2)->format('Y-m-d'),
            ]));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(1);
        });
    });

    describe('inactive users filter', function () {
        test('includes inactive users when with_inactive is true', function () {
            // Arrange
            User::factory()->create();
            $inactiveUser = User::factory()->create();
            $inactiveUser->delete();

            // Act
            $response = $this->getJson(route('v1.users.index', ['with_inactive' => true]));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(2);
        });

        test('shows only inactive users when only_inactive is true', function () {
            // Arrange
            User::factory()->create();
            $inactiveUser = User::factory()->create();
            $inactiveUser->delete();

            // Act
            $response = $this->getJson(route('v1.users.index', ['only_inactive' => true]));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            expect(count($users))->toBe(1);
        });
    });

    describe('sorting', function () {
        test('sorts users by name ascending', function () {
            // Arrange
            User::factory()->create(['name' => 'Charlie']);
            User::factory()->create(['name' => 'Alice']);
            User::factory()->create(['name' => 'Bob']);

            // Act
            $response = $this->getJson(route('v1.users.index', [
                'sort_by' => 'name',
                'sort_direction' => 'asc',
            ]));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            $names = collect($users)->pluck('name')->toArray();
            expect($names)->toBe(['Alice', 'Bob', 'Charlie']);
        });

        test('sorts users by name descending', function () {
            // Arrange
            User::factory()->create(['name' => 'Charlie']);
            User::factory()->create(['name' => 'Alice']);
            User::factory()->create(['name' => 'Bob']);

            // Act
            $response = $this->getJson(route('v1.users.index', [
                'sort_by' => 'name',
                'sort_direction' => 'desc',
            ]));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            $names = collect($users)->pluck('name')->toArray();
            expect($names)->toBe(['Charlie', 'Bob', 'Alice']);
        });

        test('sorts users by created_at descending by default', function () {
            // Arrange
            $user1 = User::factory()->create();
            $user1->created_at = now()->subDays(2);
            $user1->save();

            $user2 = User::factory()->create();
            $user2->created_at = now()->subDays(1);
            $user2->save();

            $user3 = User::factory()->create();
            $user3->created_at = now();
            $user3->save();

            // Act
            $response = $this->getJson(route('v1.users.index'));

            // Assert
            $response->assertStatus(200);
            $users = $response->json('data');
            $ids = collect($users)->pluck('id')->toArray();
            expect($ids)->toBe([$user3->id, $user2->id, $user1->id]);
        });
    });

    describe('pagination', function () {
        test('paginates users correctly', function () {
            // Arrange
            User::factory()->count(25)->create();

            // Act
            $response = $this->getJson(route('v1.users.index', ['per_page' => 10, 'page' => 1]));

            // Assert
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'links' => ['first', 'last', 'prev', 'next'],
                    'meta' => [
                        'current_page',
                        'from',
                        'last_page',
                        'per_page',
                        'to',
                        'total',
                    ],
                ])
                ->assertJsonCount(10, 'data')
                ->assertJsonPath('meta.per_page', 10)
                ->assertJsonPath('meta.current_page', 1)
                ->assertJsonPath('meta.total', 25);
        });

        test('returns second page correctly', function () {
            // Arrange
            User::factory()->count(25)->create();

            // Act
            $response = $this->getJson(route('v1.users.index', ['per_page' => 10, 'page' => 2]));

            // Assert
            $response->assertStatus(200)
                ->assertJsonCount(10, 'data')
                ->assertJsonPath('meta.current_page', 2);
        });
    });
});
