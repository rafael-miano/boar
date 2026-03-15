<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoarRating extends Model
{
    protected $fillable = [
        'boar_reservation_id',
        'boar_id',
        'customer_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function reservation()
    {
        return $this->belongsTo(BoarReservation::class, 'boar_reservation_id');
    }

    public function boar()
    {
        return $this->belongsTo(Boar::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
