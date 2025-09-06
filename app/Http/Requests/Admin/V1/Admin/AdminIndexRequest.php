<?php

namespace App\Http\Requests\Admin\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AdminIndexRequest",
 *     title="Admin Index Request",
 *     description="Query parameters for listing admins",
 *
 *     @OA\Property(property="page", type="integer", example=1, minimum=1),
 *     @OA\Property(property="per_page", type="integer", example=15, minimum=1, maximum=100),
 *     @OA\Property(property="search", type="string", example="john", maxLength=255),
 *     @OA\Property(property="status", type="string", enum={"verified", "pending"}, example="verified"),
 *     @OA\Property(property="sort_by", type="string", enum={"name", "email", "created_at", "updated_at"}, example="name"),
 *     @OA\Property(property="sort_direction", type="string", enum={"asc", "desc"}, example="asc"),
 *     @OA\Property(property="with_inactive", type="boolean", example=false),
 *     @OA\Property(property="only_inactive", type="boolean", example=false),
 * )
 */
class AdminIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'search' => [
                'nullable',
                'string',
                'min:2',
            ],
            'role' => [
                'nullable',
                'string',
            ],
            'role_id' => [
                'nullable',
                'integer',
                'exists:roles,id',
            ],
            'created_from' => [
                'nullable',
                'date',
            ],
            'created_to' => [
                'nullable',
                'date',
                'after_or_equal:created_from',
            ],
            'updated_from' => [
                'nullable',
                'date',
            ],
            'updated_to' => [
                'nullable',
                'date',
                'after_or_equal:updated_from',
            ],
            'deleted_from' => [
                'nullable',
                'date',
            ],
            'deleted_to' => [
                'nullable',
                'date',
                'after_or_equal:deleted_from',
            ],
            'with_inactive' => [
                'nullable',
                'boolean',
            ],
            'only_inactive' => [
                'nullable',
                'boolean',
            ],
            'sort_by' => [
                'nullable',
                'string',
                'in:id,name,email,created_at,updated_at',
            ],
            'sort_direction' => [
                'nullable',
                'string',
                'in:asc,desc',
            ],
        ];
    }
}
