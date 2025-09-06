<?php

namespace App\Http\Requests\Api\V1\Artist;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ArtistIndexRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
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
            'owner_id' => [
                'nullable',
                'integer',
                'exists:users,id',
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
                'in:id,name,created_at,updated_at',
            ],
            'sort_direction' => [
                'nullable',
                'string',
                'in:asc,desc',
            ],
        ];
    }
}
