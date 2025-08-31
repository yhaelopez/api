<?php

namespace App\Http\Requests\Api\V1\Upload;

use Illuminate\Foundation\Http\FormRequest;

class StoreTempFileRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:10240', // 10MB max
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required for upload.',
            'file.file' => 'The uploaded content must be a valid file.',
            'file.mimes' => 'The file must be a valid image (JPG, PNG, or WebP).',
            'file.max' => 'The file size must not exceed 10MB.',
        ];
    }

    /**
     * Get custom attribute names for validation rules.
     */
    public function attributes(): array
    {
        return [
            'file' => 'uploaded file',
        ];
    }
}
