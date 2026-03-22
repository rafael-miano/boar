<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

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
}
