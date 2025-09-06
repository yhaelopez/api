<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ForceDeleteActiveRecordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UserIndexRequest;
use App\Http\Requests\Api\V1\User\UserStoreRequest;
use App\Http\Requests\Api\V1\User\UserUpdateRequest;
use App\Http\Resources\V1\User\UserCollection;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use App\Services\TemporaryFileService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User management"
 * )
 */
class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private TemporaryFileService $temporaryFileService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Display a listing of users",
     *     tags={"User"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index(UserIndexRequest $request): UserCollection
    {
        Gate::authorize('viewAny', User::class);

        $perPage = $request->validated('per_page', 10);
        $page = $request->validated('page', 1);

        /** @var Request $request - Extract filter parameters */
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

        $users = $this->userService->getUsersList($page, $perPage, $filters);

        return new UserCollection($users);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     summary="Store a newly created user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@company.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="role_id", type="integer", example=1, description="Optional role ID to assign to the user"),
     *             @OA\Property(property="temp_folder", type="string", example="uuid-string", description="Optional temporary folder name from FilePond upload")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User created",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(UserStoreRequest $request): UserResource
    {
        Gate::authorize('create', User::class);

        $data = $request->validated();

        // Create user first
        $user = $this->userService->createUser($data);

        // Handle temporary profile photo if provided
        /** @var Request $request */
        if ($request->has('temp_folder') && $request->input('temp_folder')) {
            $this->temporaryFileService->moveTempToMedia(
                $user,
                $request->input('temp_folder'),
                'profile_photos',
            );
        }

        return new UserResource($user);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user}",
     *     summary="Display the specified user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(User $user): UserResource
    {
        Gate::authorize('view', $user);

        // Get user with relationships loaded
        $user = $this->userService->getUser($user->id);

        return new UserResource($user);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{user}",
     *     summary="Update the specified user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@company.com"),
     *             @OA\Property(property="password", type="string", example="newpassword123", description="Optional new password - leave blank to keep current password"),
     *             @OA\Property(property="role_id", type="integer", example=1, description="Optional role ID to assign to the user"),
     *             @OA\Property(property="temp_folder", type="string", example="uuid-string", description="Optional temporary folder name from FilePond upload")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User updated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UserUpdateRequest $request, User $user): UserResource
    {
        Gate::authorize('update', $user);

        $data = $request->validated();
        $updatedUser = $this->userService->updateUser($user, $data);

        // Handle temporary profile photo update if provided
        /** @var Request $request */
        if ($request->has('temp_folder') && $request->input('temp_folder')) {
            $this->temporaryFileService->moveTempToMedia(
                $updatedUser,
                $request->input('temp_folder'),
                'profile_photos',
            );
        }

        return new UserResource($updatedUser);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{user}",
     *     summary="Delete the specified user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="User deleted")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $this->userService->deleteUser($user);

        return response()->json(['message' => 'User deleted'], JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{user}/restore",
     *     summary="Restore the specified soft-deleted user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User restored",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="User restored"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function restore(User $user): JsonResponse
    {
        Gate::authorize('restore', $user);

        $restoredUser = $this->userService->restore($user);

        return response()->json([
            'message' => 'User restored',
            'data' => new UserResource($restoredUser),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{user}/force-delete",
     *     summary="Permanently delete the specified user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User permanently deleted",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="User permanently deleted")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Active record cannot be force deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function forceDelete(User $user): JsonResponse
    {
        Gate::authorize('forceDelete', $user);

        try {
            $this->userService->forceDeleteUser($user);

            return response()->json(['message' => 'User permanently deleted'], JsonResponse::HTTP_OK);
        } catch (ForceDeleteActiveRecordException $e) {
            return response()->json([
                'error' => $e->getCode(),
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            // SQL errors for example...
            return response()->json([
                'error' => $e->getCode(),
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{user}/profile-photo",
     *     summary="Remove the user's profile photo",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
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
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile photo removed successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="No profile photo found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No profile photo found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Cannot delete profile photo"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function removeProfilePhoto(User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $removed = $this->userService->removeProfilePhoto($user);

        if (! $removed) {
            return response()->json([
                'success' => false,
                'message' => 'No profile photo found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile photo removed successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{user}/send-password-reset",
     *     summary="Send password reset link to user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
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
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Password reset link sent successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Cannot send password reset"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function sendPasswordResetLink(User $user): JsonResponse
    {
        Gate::authorize('sendPasswordResetLink', $user);

        try {
            $this->userService->sendPasswordResetLink($user);

            return response()->json([
                'message' => 'Password reset link sent successfully',
                'status' => 'success',
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getCode(),
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
