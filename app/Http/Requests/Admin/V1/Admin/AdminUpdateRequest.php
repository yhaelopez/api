<?php

namespace App\Http\Requests\Admin\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AdminUpdateRequest",
 *     title="Admin Update Request",
 *     description="Request data for updating an existing admin",
 *
 *     @OA\Property(property="name", type="string", example="John Doe", maxLength=255),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", maxLength=255),
 *     @OA\Property(property="password", type="string", example="password123", minLength=8),
 *     @OA\Property(property="password_confirmation", type="string", example="password123"),
 *     @OA\Property(property="role_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="temp_folder", type="string", example="temp_uploads_123", nullable=true),
 * )
 */
class AdminUpdateRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:admins,email,'.$this->route('admin')->id.'|max:255',
            'password' => ['sometimes', 'confirmed', Password::min(8)],
            'password_confirmation' => 'required_with:password|string',
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
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters.',
            'password_confirmation.required_with' => 'The password confirmation field is required when password is present.',
            'role_id.exists' => 'The selected role is invalid.',
        ];
    }
}
