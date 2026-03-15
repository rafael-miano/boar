<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'gcash_qr_image',
        'platform_fee_percentage',
    ];

    protected $casts = [
        'platform_fee_percentage' => 'float',
    ];

    public static function get(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'platform_fee_percentage' => 10,
        ]);
    }
}
