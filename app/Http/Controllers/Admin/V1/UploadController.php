<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\V1\Upload\StoreTempFileRequest;
use App\Http\Resources\V1\Upload\TempFolderResource;
use App\Services\TemporaryFileService;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class UploadController extends Controller
{
    public function __construct(
        private TemporaryFileService $temporaryFileService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/admin/v1/upload/temp",
     *     summary="Store a temporary file",
     *     tags={"Upload"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"file"},
     *
     *                 @OA\Property(property="file", type="string", format="binary", example="profile_photo.jpg", description="The file to upload (jpg, jpeg, png, webp, max 10MB)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/TempFolderResource")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File upload failed"),
     *             @OA\Property(property="error", type="string", example="Internal server error details")
     *         )
     *     )
     * )
     */
    public function storeTemp(StoreTempFileRequest $request): TempFolderResource|JsonResponse
    {
        try {
            $file = $request->file('file');
            $folder = $this->temporaryFileService->storeTemporaryFile($file);

            $data = [
                'folder' => $folder,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ];

            return new TempFolderResource($data);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
