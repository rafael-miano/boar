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
     * Read the EXIF orientation from a file on the public disk and rotate
     * the image pixels so it displays correctly on any device/browser.
     * Mobile phones embed EXIF rotation in JPEGs without rotating the pixels.
     */
    public static function fixExifOrientation(string $diskPath): void
    {
        if (! class_exists(ImageManager::class)) {
            return;
        }

        try {
            $fullPath = Storage::disk('public')->path($diskPath);

            if (! file_exists($fullPath)) {
                return;
            }

            $exif        = @exif_read_data($fullPath);
            $orientation = $exif['Orientation'] ?? 1;

            if ($orientation === 1) {
                return; // Already correct — nothing to do.
            }

            $manager = new ImageManager(new Driver());
            $image   = $manager->read($fullPath);

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

            $image->save($fullPath);
        } catch (\Throwable) {
            // Never break the save — orientation correction is best-effort.
        }
    }
}
