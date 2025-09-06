<?php

namespace App\Http\Controllers\Admin\V1;

use App\Exceptions\ForceDeleteActiveRecordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\V1\Admin\AdminIndexRequest;
use App\Http\Requests\Admin\V1\Admin\AdminStoreRequest;
use App\Http\Requests\Admin\V1\Admin\AdminUpdateRequest;
use App\Http\Resources\V1\Admin\AdminCollection;
use App\Http\Resources\V1\Admin\AdminResource;
use App\Models\Admin;
use App\Services\AdminService;
use App\Services\TemporaryFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Admin management"
 * )
 */
class AdminController extends Controller
{
    public function __construct(
        private AdminService $adminService,
        private TemporaryFileService $temporaryFileService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/admins",
     *     summary="Display a listing of admins",
     *     tags={"Admin"},
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
     *         name="search",
     *         in="query",
     *         description="Search term for name or email",
     *         required=false,
     *
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by verification status",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"verified", "pending"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"name", "email", "created_at", "updated_at"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="with_inactive",
     *         in="query",
     *         description="Include soft-deleted admins",
     *         required=false,
     *
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Parameter(
     *         name="only_inactive",
     *         in="query",
     *         description="Show only soft-deleted admins",
     *         required=false,
     *
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdminCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index(AdminIndexRequest $request): AdminCollection
    {
        Gate::authorize('viewAny', Admin::class);

        $page = $request->validated('page', 1);
        $perPage = $request->validated('per_page', 15);

        $filters = $request->only([
            'search',
            'role',
            'role_id',
            'created_from',
            'created_to',
            'updated_from',
            'updated_to',
            'deleted_from',
            'deleted_to',
            'with_inactive',
            'only_inactive',
            'sort_by',
            'sort_direction',
        ]);

        $admins = $this->adminService->getAdminsList($page, $perPage, $filters);

        return new AdminCollection($admins);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admins/{id}",
     *     summary="Display the specified admin",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function show(Admin $admin): AdminResource
    {
        Gate::authorize('view', $admin);

        $admin = $this->adminService->getAdmin($admin->id);

        return new AdminResource($admin);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admins",
     *     summary="Store a newly created admin",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdminStoreRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Admin created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function store(AdminStoreRequest $request): AdminResource
    {
        Gate::authorize('create', Admin::class);

        $data = $request->validated();
        $admin = $this->adminService->createAdmin($data);

        // Handle file upload if temp_folder is provided
        if ($request->has('temp_folder') && $request->input('temp_folder')) {
            $this->temporaryFileService->moveTempToMedia(
                $admin,
                $request->input('temp_folder'),
                'profile_photos',
            );
        }

        return new AdminResource($admin);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admins/{id}",
     *     summary="Update the specified admin",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdminUpdateRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Admin updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function update(AdminUpdateRequest $request, Admin $admin): AdminResource
    {
        Gate::authorize('update', $admin);

        $data = $request->validated();
        $updatedAdmin = $this->adminService->updateAdmin($admin, $data);

        // Handle file upload if temp_folder is provided
        if ($request->has('temp_folder') && $request->input('temp_folder')) {
            $this->temporaryFileService->moveTempToMedia(
                $updatedAdmin,
                $request->input('temp_folder'),
                'profile_photos',
            );
        }

        return new AdminResource($updatedAdmin);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admins/{id}",
     *     summary="Remove the specified admin",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Admin deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Admin deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function destroy(Admin $admin): JsonResponse
    {
        Gate::authorize('delete', $admin);

        $this->adminService->deleteAdmin($admin);

        return response()->json(['message' => 'Admin deleted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admins/{id}/restore",
     *     summary="Restore a soft-deleted admin",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Admin restored successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function restore(Admin $admin): AdminResource
    {
        Gate::authorize('restore', $admin);

        $restoredAdmin = $this->adminService->restore($admin);

        return new AdminResource($restoredAdmin);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admins/{id}/force-delete",
     *     summary="Permanently delete an admin",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Admin permanently deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Admin permanently deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Cannot force delete active admin"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function forceDelete(Admin $admin): JsonResponse
    {
        Gate::authorize('forceDelete', $admin);

        try {
            $this->adminService->forceDeleteAdmin($admin);

            return response()->json(['message' => 'Admin permanently deleted successfully']);
        } catch (ForceDeleteActiveRecordException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admins/{id}/profile-photo",
     *     summary="Remove admin profile photo",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile photo removed successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Profile photo removed successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function removeProfilePhoto(Admin $admin): JsonResponse
    {
        Gate::authorize('update', $admin);

        $this->adminService->removeProfilePhoto($admin);

        return response()->json(['message' => 'Profile photo removed successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admins/{id}/send-password-reset",
     *     summary="Send password reset link to admin",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Admin ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Password reset link sent successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function sendPasswordResetLink(Admin $admin): JsonResponse
    {
        Gate::authorize('sendPasswordResetLink', $admin);

        $this->adminService->sendPasswordResetLink($admin);

        return response()->json(['message' => 'Password reset link sent successfully']);
    }
}
