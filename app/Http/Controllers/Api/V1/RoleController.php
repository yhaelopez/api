<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Role\RoleCollection;
use App\Services\RoleService;
use Illuminate\Support\Facades\Gate;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="RoleController",
 *     description="Role management"
 * )
 */
class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/roles",
     *     summary="Display a listing of roles",
     *     tags={"RoleController"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/RoleCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index()
    {
        Gate::authorize('viewAny', 'App\Models\User');

        $roles = $this->roleService->getAllRoles();

        return new RoleCollection($roles);
    }
}
