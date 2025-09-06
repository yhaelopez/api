<?php

namespace App\Http\Controllers\Admin\V1;

use App\Enums\GuardEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Role\RoleCollection;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Support\Facades\Gate;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Role",
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
     *     tags={"Role"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *
     *     @OA\Parameter(
     *         name="guard",
     *         in="query",
     *         description="Guard name to filter roles",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"api", "admin"}, default="api")
     *     ),
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
        Gate::authorize('viewAny', User::class);

        $perPage = request('per_page', 15);
        $page = request('page', 1);
        $guard = request('guard', GuardEnum::API->value);

        // Validate guard parameter
        $validGuards = [GuardEnum::API->value, GuardEnum::ADMIN->value];
        if (! in_array($guard, $validGuards)) {
            return response()->json(['error' => 'Invalid guard parameter'], 400);
        }

        // Get roles filtered by the specified guard
        $roles = $this->roleService->getRolesListByGuard($guard, $page, $perPage);

        return new RoleCollection($roles);
    }
}
