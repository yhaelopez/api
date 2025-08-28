<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository;
    }

    public function test_can_find_user_by_id()
    {
        $user = User::factory()->create();

        $foundUser = $this->userRepository->find($user->id);

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_can_find_user_with_roles()
    {
        $user = User::factory()->create();

        $foundUser = $this->userRepository->findWithRoles($user->id);

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertTrue($foundUser->relationLoaded('roles'));
    }

    public function test_can_paginate_users()
    {
        User::factory()->count(25)->create();

        $paginatedUsers = $this->userRepository->paginate(1, 15);

        $this->assertEquals(15, $paginatedUsers->count());
        $this->assertEquals(25, $paginatedUsers->total());
        $this->assertEquals(1, $paginatedUsers->currentPage());
    }

    public function test_can_create_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ];

        $user = $this->userRepository->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    public function test_can_update_user()
    {
        $user = User::factory()->create();
        $updateData = ['name' => 'Updated Name'];

        $updatedUser = $this->userRepository->update($user, $updateData);

        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals($user->id, $updatedUser->id);
    }

    public function test_can_delete_user()
    {
        $user = User::factory()->create();

        $result = $this->userRepository->delete($user);

        $this->assertTrue($result);
        $this->assertSoftDeleted($user);
    }
}
