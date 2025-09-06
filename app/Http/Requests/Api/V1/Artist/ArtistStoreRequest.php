<?php

namespace App\Http\Requests\Api\V1\Artist;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ArtistStoreRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'spotify_id' => [
                'nullable',
                'string',
                'max:255',
                'unique:artists,spotify_id',
            ],
            'owner_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'profile_photo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,png,webp',
                'max:5120', // 5MB
            ],
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
            'name.required' => 'The artist name field is required.',
            'name.max' => 'The artist name may not be greater than 255 characters.',
            'spotify_id.unique' => 'An artist with this Spotify ID already exists.',
            'owner_id.integer' => 'The owner must be a valid user ID.',
            'owner_id.exists' => 'The selected owner does not exist.',
            'profile_photo.file' => 'The profile photo must be a valid file.',
            'profile_photo.image' => 'The profile photo must be an image.',
            'profile_photo.mimes' => 'The profile photo must be a JPEG, PNG, or WebP file.',
            'profile_photo.max' => 'The profile photo may not be greater than 5MB.',
        ];
    }
}
