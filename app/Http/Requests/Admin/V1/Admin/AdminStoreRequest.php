<?php

namespace App\Http\Requests\Admin\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AdminStoreRequest",
 *     title="Admin Store Request",
 *     description="Request data for creating a new admin",
 *     required={"name", "email", "password"},
 *
 *     @OA\Property(property="name", type="string", example="John Doe", maxLength=255),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", maxLength=255),
 *     @OA\Property(property="password", type="string", example="password123", minLength=8),
 *     @OA\Property(property="role_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="temp_folder", type="string", example="temp_uploads_123", nullable=true),
 * )
 */
class AdminStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email|max:255',
            'password' => ['required', Password::min(8)],
            'role_id' => 'sometimes|integer|exists:roles,id',
            'temp_folder' => 'sometimes|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'role_id.exists' => 'The selected role is invalid.',
        ];
    }
}
