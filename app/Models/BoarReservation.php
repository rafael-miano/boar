<?php

namespace App\Models;

use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Model;

class BoarReservation extends Model implements Eventable
{
    protected $fillable = [
        'boar_id',
        'user_id',
        'address',
        'service_date',
        'service_fee_type',
        'service_fee_amount',
        'platform_fee',
        'boar_raiser_share',
        'platform_fee_paid_at',
        'female_pig_photo',
        'notes',
        'payment_receipt_image',
        'payment_status',
        'payment_verified_at',
        'rejection_message',
        'rejected_at',
        'approved_at',
        'reservation_status',
        'service_status',
        'expected_due_date',
        'birth_confirmed_at',
        'piglet_count',
    ];

    protected $casts = [
        'service_date' => 'date',
        'expected_due_date' => 'date',
        'platform_fee_paid_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'approved_at' => 'datetime',
        'birth_confirmed_at' => 'datetime',
    ];

    public function boar()
    {
        return $this->belongsTo(Boar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rating()
    {
        return $this->hasOne(BoarRating::class, 'boar_reservation_id');
    }

    /**
     * Map this reservation to a calendar event (accepted/confirmed only).
     */
    public function toCalendarEvent(): CalendarEvent
    {
        $boarName = $this->boar?->boar_name ?? 'Boar';
        $customerName = $this->user?->name ?? 'Customer';

        return CalendarEvent::make($this)
            ->title("{$boarName} – {$customerName}")
            ->start($this->service_date)
            ->end($this->service_date)
            ->allDay(true)
            ->backgroundColor($this->reservation_status === 'confirmed' ? '#22c55e' : '#3b82f6')
            ->textColor('#ffffff');
    }

    /**
     * Calculate platform fee and boar raiser share for money payments.
     * Returns attributes to merge into update; empty if not applicable.
     */
    public function calculatePlatformFee(): array
    {
        if ($this->service_fee_type !== 'money') {
            return [];
        }
        $amount = (float) $this->service_fee_amount;
        if ($amount <= 0) {
            return [];
        }
        $settings = PlatformSetting::get();
        $pct = $settings->platform_fee_percentage / 100;
        $platformFee = round($amount * $pct, 2);
        $boarRaiserShare = round($amount - $platformFee, 2);
        return [
            'platform_fee' => $platformFee,
            'boar_raiser_share' => $boarRaiserShare,
        ];
    }

    /**
     * Total unpaid platform fee for a given boar raiser (by user_id).
     */
    public static function unpaidPlatformFeeTotalForBoarRaiser(int $userId): float
    {
        return (float) static::query()
            ->whereHas('boar', fn ($q) => $q->where('user_id', $userId))
            ->where('service_fee_type', 'money')
            ->whereNotNull('platform_fee')
            ->whereNull('platform_fee_paid_at')
            ->where(function ($q) {
                $q->where('reservation_status', 'confirmed')
                    ->orWhere('service_status', 'completed');
            })
            ->sum('platform_fee');
    }
}

