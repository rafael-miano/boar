<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boar extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'boar_picture',
        'boar_name',
        'boar_type',
        'boar_type_other',
        'breeding_maturity_date',
        'health_status',
        'health_status_other',
        'breeding_status',
        'breeding_status_other',
        'default_price_money',
        'default_downpayment',
        'default_pay_with_pigs',
        'gcash_qr_image',
        'is_published',
        'publish_status',
        'archived_at',
        'marketplace_available_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'archived_at' => 'datetime',
        'marketplace_available_at' => 'datetime',
    ];

    /**
     * Boars that are published AND not in a post-service cooldown period.
     * Used by the marketplace — no cron needed; evaluated live on every query.
     */
    public function scopeMarketplaceAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('marketplace_available_at')
                    ->orWhere('marketplace_available_at', '<=', now());
            });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studServices()
    {
        return $this->hasMany(StudService::class);
    }

    public function reservations()
    {
        return $this->hasMany(BoarReservation::class, 'boar_id');
    }

    public function ratings()
    {
        return $this->hasMany(BoarRating::class);
    }
}
