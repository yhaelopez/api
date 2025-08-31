<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ProfilePhotoResource",
 *     title="Profile Photo Resource",
 *     description="Profile photo information for FilePond integration",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="url", type="string", example="/storage/users/48/profile_photo/profile_photo_1756599663.jpg"),
 *     @OA\Property(property="name", type="string", example="profile_photo_1756599663.jpg"),
 *     @OA\Property(property="size", type="integer", example=12345),
 *     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
 * )
 */
class ProfilePhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->getUrl(),
            'name' => $this->file_name,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
        ];
    }
}
