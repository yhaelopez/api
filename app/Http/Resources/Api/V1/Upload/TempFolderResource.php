<?php

namespace App\Http\Resources\Api\V1\Upload;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TempFolderResource",
 *     title="Temp Folder Resource",
 *     description="Temporary file upload response",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="folder", type="string", example="temp_1234567890"),
 *     @OA\Property(property="filename", type="string", example="profile_photo.jpg"),
 *     @OA\Property(property="size", type="integer", example=12345),
 *     @OA\Property(property="uploaded_at", type="string", format="date-time", example="2025-08-31T01:52:43.000000Z"),
 * )
 */
class TempFolderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'folder' => $this->resource['folder'],
            'filename' => $this->resource['filename'],
            'size' => $this->resource['size'],
            'uploaded_at' => now()->toISOString(),
        ];
    }
}
