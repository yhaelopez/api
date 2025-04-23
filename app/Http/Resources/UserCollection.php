<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserCollection",
 *     title="User Collection",
 *     description="User collection resource",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/UserResource")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="self", type="string", example="http://localhost/api/users")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object"
 *     )
 * )
 */
class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                // 'self' => $this->url($request->url()),
            ],
            'meta' => [
                // 'current_page' => $this->currentPage(),
                // 'from' => $this->firstItem(),
                // 'last_page' => $this->lastPage(),
                // 'path' => $this->path(),
            ],
        ];
    }
}
