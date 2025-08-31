<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Upload\StoreTempFileRequest;
use App\Http\Resources\Api\V1\Upload\TempFolderResource;
use App\Services\TemporaryFileService;
use Exception;
use Illuminate\Http\JsonResponse;

class UploadController extends Controller
{
    public function __construct(
        private TemporaryFileService $temporaryFileService
    ) {}

    /**
     * Store a temporary file and return the folder name
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
