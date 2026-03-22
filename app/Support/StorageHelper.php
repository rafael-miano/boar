<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class StorageHelper
{
    public static function uploadDisk(): string
    {
        return 'public';
    }

    public static function url(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Fix EXIF orientation AND resize the image server-side.
     *
     * Client-side canvas resizing (Filepond image-transform) silently fails on
     * large mobile photos due to browser memory limits, producing empty files.
     * We skip all client-side processing and do everything here instead:
     *   1. Apply EXIF rotation so portrait/landscape is correct.
     *   2. Scale the image down to $maxWidth × $maxHeight (preserving aspect ratio).
     */
    public static function processUploadedImage(
        string $diskPath,
        int $maxWidth = 400,
        int $maxHeight = 400,
    ): void {
        if (! class_exists(ImageManager::class)) {
            return;
        }

        try {
            $fullPath = Storage::disk('public')->path($diskPath);

            if (! file_exists($fullPath)) {
                return;
            }

            $manager = new ImageManager(new Driver());
            $image   = $manager->read($fullPath);

            // 1. Fix EXIF orientation (mobile phones store rotation in metadata).
            $exif        = @exif_read_data($fullPath);
            $orientation = $exif['Orientation'] ?? 1;

            match ($orientation) {
                2 => $image->flip(),
                3 => $image->rotate(180),
                4 => $image->flop(),
                5 => $image->rotate(90)->flip(),
                6 => $image->rotate(270),
                7 => $image->rotate(270)->flip(),
                8 => $image->rotate(90),
                default => null,
            };

            // 2. Scale down to target dimensions (never upscale).
            $image->scaleDown(width: $maxWidth, height: $maxHeight);

            $image->save($fullPath);
        } catch (\Throwable) {
            // Never break the save — image processing is best-effort.
        }
    }

    /** @deprecated Use processUploadedImage() instead */
    public static function fixExifOrientation(string $diskPath): void
    {
        self::processUploadedImage($diskPath);
    }
}
