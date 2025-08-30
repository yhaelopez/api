<?php

namespace App\Http\Resources\V1\Permission;

use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PermissionCollection",
 *     title="Permission Collection",
 *     description="Permission collection resource",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/PermissionResource")
 *     ),
 *
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string", example="http://localhost/api/v1/permissions?page=1"),
 *         @OA\Property(property="last", type="string", example="http://localhost/api/v1/permissions?page=2"),
 *         @OA\Property(property="prev", type="string", example="http://localhost/api/v1/permissions?page=1"),
 *         @OA\Property(property="next", type="string", example="http://localhost/api/v1/permissions?page=2")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=2),
 *         @OA\Property(property="links", type="array",
 *
 *             @OA\Items(type="object",
 *
 *                 @OA\Property(property="url", type="string", example="http://localhost/api/v1/permissions?page=1"),
 *                 @OA\Property(property="label", type="string", example="1"),
 *                 @OA\Property(property="active", type="boolean", example=true)
 *             ),
 *         ),
 *         @OA\Property(property="path", type="string", example="http://localhost/api/v1/permissions"),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=30)
 *     )
 * )
 */
class PermissionCollection extends ResourceCollection {}
