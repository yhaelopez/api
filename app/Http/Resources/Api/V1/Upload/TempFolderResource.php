<?php

namespace App\Http\Resources\Api\V1\Upload;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TempFolderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'folder' => $this->resource['folder'],
            'filename' => $this->resource['filename'],
            'size' => $this->resource['size'],
            'uploaded_at' => now()->toISOString(),
        ];
    }
}
