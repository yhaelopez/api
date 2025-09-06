<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ForceDeleteActiveRecordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Artist\ArtistIndexRequest;
use App\Http\Requests\Api\V1\Artist\ArtistStoreRequest;
use App\Http\Requests\Api\V1\Artist\ArtistUpdateRequest;
use App\Http\Resources\V1\Artist\ArtistCollection;
use App\Http\Resources\V1\Artist\ArtistResource;
use App\Models\Artist;
use App\Services\ArtistService;
use App\Services\TemporaryFileService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Artist",
 *     description="Artist management"
 * )
 */
class ArtistController extends Controller
{
    public function __construct(
        private ArtistService $artistService,
        private TemporaryFileService $temporaryFileService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/artists",
     *     summary="Display a listing of artists",
     *     tags={"Artist"},
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
     *         @OA\JsonContent(ref="#/components/schemas/ArtistCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index(ArtistIndexRequest $request): ArtistCollection
    {
        Gate::authorize('viewAny', Artist::class);

        $perPage = $request->validated('per_page', 10);
        $page = $request->validated('page', 1);

        /** @var Request $request - Extract filter parameters */
        $filters = $request->only([
            'search',
            'owner_id',
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

        $artists = $this->artistService->getArtistsList($page, $perPage, $filters);

        return new ArtistCollection($artists);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/artists",
     *     summary="Store a newly created artist",
     *     tags={"Artist"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name"},
     *
     *             @OA\Property(property="name", type="string", example="Radiohead"),
     *             @OA\Property(property="spotify_id", type="string", example="0TnOYISbd1XYRBk9myaseg", description="Optional Spotify artist ID"),
     *             @OA\Property(property="temp_folder", type="string", example="uuid-string", description="Optional temporary folder name from FilePond upload")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Artist created",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ArtistResource")
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
    public function store(ArtistStoreRequest $request): ArtistResource
    {
        Gate::authorize('create', Artist::class);

        $data = $request->validated();

        // Set the current user as the owner
        $data['owner_id'] = Auth::id();

        // Create artist first
        $artist = $this->artistService->createArtist($data);

        // Handle temporary profile photo if provided
        /** @var Request $request */
        if ($request->has('temp_folder') && $request->input('temp_folder')) {
            $this->temporaryFileService->moveTempToMedia(
                $artist,
                $request->input('temp_folder'),
                'profile_photos',
            );
        }

        return new ArtistResource($artist);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/artists/{artist}",
     *     summary="Display the specified artist",
     *     tags={"Artist"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="artist",
     *         in="path",
     *         description="Artist ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ArtistResource")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function show(Artist $artist): ArtistResource
    {
        Gate::authorize('view', $artist);

        return new ArtistResource($artist);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/artists/{artist}",
     *     summary="Update the specified artist",
     *     tags={"Artist"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="artist",
     *         in="path",
     *         description="Artist ID",
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
     *             @OA\Property(property="name", type="string", example="Radiohead"),
     *             @OA\Property(property="spotify_id", type="string", example="0TnOYISbd1XYRBk9myaseg", description="Optional Spotify artist ID"),
     *             @OA\Property(property="temp_folder", type="string", example="uuid-string", description="Optional temporary folder name from FilePond upload")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Artist updated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ArtistResource")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(ArtistUpdateRequest $request, Artist $artist): ArtistResource
    {
        Gate::authorize('update', $artist);

        $data = $request->validated();
        $updatedArtist = $this->artistService->updateArtist($artist, $data);

        // Handle temporary profile photo update if provided
        /** @var Request $request */
        if ($request->has('temp_folder') && $request->input('temp_folder')) {
            $this->temporaryFileService->moveTempToMedia(
                $updatedArtist,
                $request->input('temp_folder'),
                'profile_photos',
            );
        }

        return new ArtistResource($updatedArtist);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/artists/{artist}",
     *     summary="Delete the specified artist",
     *     tags={"Artist"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="artist",
     *         in="path",
     *         description="Artist ID",
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
     *             @OA\Property(property="message", type="string", example="Artist deleted")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function destroy(Artist $artist): JsonResponse
    {
        Gate::authorize('delete', $artist);

        $this->artistService->deleteArtist($artist);

        return response()->json(['message' => 'Artist deleted'], JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/artists/{artist}/restore",
     *     summary="Restore the specified soft-deleted artist",
     *     tags={"Artist"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="artist",
     *         in="path",
     *         description="Artist ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Artist restored",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Artist restored"),
     *             @OA\Property(property="data", ref="#/components/schemas/ArtistResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function restore(Artist $artist): JsonResponse
    {
        Gate::authorize('restore', $artist);

        $restoredArtist = $this->artistService->restoreArtist($artist);

        return response()->json([
            'message' => 'Artist restored',
            'data' => new ArtistResource($restoredArtist),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/artists/{artist}/force-delete",
     *     summary="Permanently delete the specified artist",
     *     tags={"Artist"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="artist",
     *         in="path",
     *         description="Artist ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Artist permanently deleted",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Artist permanently deleted")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
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
    public function forceDelete(Artist $artist): JsonResponse
    {
        Gate::authorize('forceDelete', $artist);

        try {
            $this->artistService->forceDeleteArtist($artist);

            return response()->json(['message' => 'Artist permanently deleted'], JsonResponse::HTTP_OK);
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
     *     path="/api/v1/artists/{artist}/profile-photo",
     *     summary="Remove the artist's profile photo",
     *     tags={"Artist"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="artist",
     *         in="path",
     *         description="Artist ID",
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
    public function removeProfilePhoto(Artist $artist): JsonResponse
    {
        Gate::authorize('update', $artist);

        $removed = $this->artistService->removeProfilePhoto($artist);

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
}
