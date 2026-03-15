<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PigletSale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'seller_id',
        'listing_title',
        'description',
        'photos',
        'piglets_available',
        'minimum_order',
        'price_per_piglet',
        'currency',
        'available_from',
        'available_until',
        'pickup_location',
        'pickup_details',
        'contact_phone',
        'facebook_profile_link',
        'delivery_available',
        'delivery_fee',
        'sale_status',
        'published_at',
        'sold_out_at',
        'archived_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'available_from' => 'date',
        'available_until' => 'date',
        'delivery_available' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'price_per_piglet' => 'decimal:2',
        'published_at' => 'datetime',
        'sold_out_at' => 'datetime',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
