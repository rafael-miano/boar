<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFeeSettlement extends Model
{
    protected $fillable = [
        'boar_raiser_id',
        'amount',
        'receipt_image',
        'status',
        'submitted_at',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'submitted_at' => 'datetime',
        'verified_at'  => 'datetime',
    ];

    public function boarRaiser()
    {
        return $this->belongsTo(User::class, 'boar_raiser_id');
    }

    /** True if this settlement is still awaiting admin review. */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
