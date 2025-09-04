<?php

namespace App\Http\Resources\V1\Artist;

use App\Http\Resources\V1\User\ProfilePhotoResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ArtistResource",
 *     title="Artist Resource",
 *     description="Artist resource representation",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="spotify_id", type="string", example="0TnOYISbd1XYRBk9myaseg", nullable=true),
 *     @OA\Property(property="name", type="string", example="Radiohead"),
 *     @OA\Property(property="popularity", type="integer", example=85, nullable=true),
 *     @OA\Property(property="followers_count", type="integer", example=5432109, nullable=true),
 *     @OA\Property(
 *         property="owner",
 *         description="The user who owns this artist",
 *         ref="#/components/schemas/UserResource",
 *         nullable=true
 *     ),
 *      @OA\Property(
 *         property="profile_photo",
 *         description="Artist's profile photo information",
 *         ref="#/components/schemas/ProfilePhotoResource",
 *         nullable=true
 *     ),
 *     @OA\Property(property="created_at", type="datetime", example="2021-01-01 12:00:00", nullable=true),
 *     @OA\Property(property="updated_at", type="datetime", example="2021-01-01 12:00:00", nullable=true),
 *     @OA\Property(property="deleted_at", type="datetime", example="2021-01-01 12:00:00", nullable=true),
 *     @OA\Property(property="restored_at", type="datetime", example="2021-01-01 12:00:00", nullable=true),
 * )
 */
class ArtistResource extends JsonResource
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
            'spotify_id' => $this->spotify_id,
            'name' => $this->name,
            'popularity' => $this->popularity,
            'followers_count' => $this->followers_count,
            'owner' => $this->whenLoaded('owner', function () {
                return new UserResource($this->owner);
            }),
            'profile_photo' => $this->when($this->getMedia('profile_photos')->isNotEmpty(), function () {
                return new ProfilePhotoResource($this->getMedia('profile_photos')->first());
            }, null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'restored_at' => $this->restored_at,
        ];
    }
}
