<?php

namespace App\Http\Resources\V1\Role;

use App\Http\Resources\V1\Permission\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="RoleResource",
 *     title="Role Resource",
 *     description="Role resource representation",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Admin"),
 *     @OA\Property(property="guard_name", type="string", example="web"),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         nullable=true,
 *
 *         @OA\Items(ref="#/components/schemas/PermissionResource"),
 *     ),
 * )
 */
class RoleResource extends JsonResource
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
            'guard_name' => $this->guard_name,
            'permissions' => $this->whenLoaded('permissions', function () {
                return PermissionResource::collection($this->permissions);
            }),
        ];
    }
}
