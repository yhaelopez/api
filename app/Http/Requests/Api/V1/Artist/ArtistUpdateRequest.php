<?php

namespace App\Http\Requests\Api\V1\Artist;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArtistUpdateRequest extends FormRequest
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
        $artistId = $this->route('artist');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'spotify_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('artists', 'spotify_id')->ignore($artistId),
            ],
            'popularity' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'followers_count' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],
            'profile_photo' => [
                'sometimes',
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
            'name.max' => 'The artist name may not be greater than 255 characters.',
            'spotify_id.unique' => 'An artist with this Spotify ID already exists.',
            'popularity.min' => 'The popularity must be at least 0.',
            'popularity.max' => 'The popularity may not be greater than 100.',
            'followers_count.min' => 'The followers count must be at least 0.',
            'profile_photo.file' => 'The profile photo must be a valid file.',
            'profile_photo.image' => 'The profile photo must be an image.',
            'profile_photo.mimes' => 'The profile photo must be a JPEG, PNG, or WebP file.',
            'profile_photo.max' => 'The profile photo may not be greater than 5MB.',
        ];
    }
}
