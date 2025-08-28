<?php

namespace App\Services;

use App\Cache\UserCache;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private UserCache $userCache
    ) {}

    /**
     * Get paginated list of users with caching
     */
    public function getUsersList(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userCache->rememberList($page, $perPage, function () use ($page, $perPage) {
            return User::paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * Get a single user with caching
     */
    public function getUser(int $id): User
    {
        return $this->userCache->remember($id, function () use ($id) {
            return User::findOrFail($id);
        });
    }
}
