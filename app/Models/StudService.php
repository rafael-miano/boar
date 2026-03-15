<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudService extends Model
{
    protected $fillable = [
        'boar_id',
        'boar_reservation_id',
        'client_name',
        'service_date',
        'service_fee_type',
        'service_fee_amount',
        'service_status',
    ];

    protected $casts = [
        'service_date' => 'date',
    ];

    public function boar()
    {
        return $this->belongsTo(Boar::class);
    }

    public function boarReservation()
    {
        return $this->belongsTo(BoarReservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
