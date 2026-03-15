<?php

namespace App\Notifications;

use App\Models\BoarReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BoarReservationAccepted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public BoarReservation $boarReservation
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'boar_reservation_accepted',
            'boar_reservation_id' => $this->boarReservation->id,
            'boar_id' => $this->boarReservation->boar_id,
            'customer_id' => $this->boarReservation->user_id,
            'service_date' => (string) $this->boarReservation->service_date,
            'message' => 'Your boar reservation request was accepted by ' . ($this->boarReservation->boar?->user?->name ?? 'the boar raiser') . '.',
        ];
    }
}

