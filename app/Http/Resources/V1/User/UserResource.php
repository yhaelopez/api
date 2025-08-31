<?php

namespace App\Http\Resources\V1\User;

use App\Http\Resources\V1\Permission\PermissionResource;
use App\Http\Resources\V1\Role\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     title="User Resource",
 *     description="User resource representation",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *     @OA\Property(
 *         property="roles",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(ref="#/components/schemas/RoleResource"),
 *     ),
 *
 *     @OA\Property(
 *         property="permissions",
 *         description="All permissions the user has through their roles",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(ref="#/components/schemas/PermissionResource"),
 *     ),
 *
 * @OA\Property(
 *         property="profile_photo",
 *         description="User's profile photo information",
 *         ref="#/components/schemas/ProfilePhotoResource",
 *         nullable=true,
 *     ),
 *     @OA\Property(property="created_at", type="datetime", example="2021-01-01 12:00:00", nullable=true),
 *     @OA\Property(property="updated_at", type="datetime", example="2021-01-01 12:00:00", nullable=true),
 *     @OA\Property(property="deleted_at", type="datetime", example="null", nullable=true),
 * )
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles', function () {
                return RoleResource::collection($this->roles);
            }),
            'permissions' => $this->whenLoaded('roles', function () {
                // Get all permissions through roles
                $permissions = $this->roles->flatMap(function ($role) {
                    return $role->permissions ?? collect();
                })->unique('id');

                return PermissionResource::collection($permissions);
            }),
            'profile_photo' => $this->when($this->getMedia('profile_photos')->isNotEmpty(), function () {
                return new ProfilePhotoResource($this->getMedia('profile_photos')->first());
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
