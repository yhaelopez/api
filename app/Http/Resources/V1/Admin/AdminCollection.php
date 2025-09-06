<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AdminCollection",
 *     title="Admin Collection",
 *     description="Admin collection resource",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/AdminResource")
 *     ),
 *
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string", example="http://localhost/api/v1/admins?page=1"),
 *         @OA\Property(property="last", type="string", example="http://localhost/api/v1/admins?page=2"),
 *         @OA\Property(property="prev", type="string", example="http://localhost/api/v1/admins?page=1"),
 *         @OA\Property(property="next", type="string", example="http://localhost/api/v1/admins?page=2")
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
 *                 @OA\Property(property="url", type="string", example="http://localhost/api/v1/admins?page=1"),
 *                 @OA\Property(property="label", type="string", example="1"),
 *                 @OA\Property(property="active", type="boolean", example=true)
 *             ),
 *         ),
 *         @OA\Property(property="path", type="string", example="http://localhost/api/v1/admins"),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=30)
 *     )
 * )
 */
class AdminCollection extends ResourceCollection {}
