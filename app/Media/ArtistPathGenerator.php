<?php

namespace App\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class ArtistPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        // For profile photos, use artists/{id}/profile_photo structure
        if ($media->collection_name === 'profile_photos') {
            return "artists/{$media->model_id}/profile_photo/";
        }

        // Default path for other collections
        return "media/{$media->id}/";
    }

    public function getPathForConversions(Media $media): string
    {
        // For profile photo conversions, use artists/{id}/profile_photo/conversions structure
        if ($media->collection_name === 'profile_photos') {
            return "artists/{$media->model_id}/profile_photo/conversions/";
        }

        // Default path for other collections
        return "media/{$media->id}/conversions/";
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        // For profile photo responsive images, use artists/{id}/profile_photo/responsive-images structure
        if ($media->collection_name === 'profile_photos') {
            return "artists/{$media->model_id}/profile_photo/responsive-images/";
        }

        // Default path for other collections
        return "media/{$media->id}/responsive-images/";
    }
}
