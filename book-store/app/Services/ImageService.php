<?php

namespace App\Services;

use Intervention\Image\Facades\Image;

class ImageService
{
    public static function processAndStore($image, $path)
    {
        $filename = uniqid() . '.jpg';
        $storagePath = storage_path('app/public/' . $path);

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $image = Image::make($image);

        // Add watermark if needed
        // $image->insert(public_path('watermark.png'), 'bottom-right', 10, 10);

        $image->save($storagePath . '/' . $filename);

        return $path . '/' . $filename;
    }
}
