<?php

namespace App\Livewire;

use App\Filament\Resources\ApprovedReservationResource;
use App\Models\BoarReservation;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;

class MyCalendarWidget extends CalendarWidget
{
    protected bool $eventClickEnabled = true;

    /**
     * When a reservation event is clicked, open the full reservation view page.
     */
    public function onEventClick(array $info = [], ?string $action = null): void
    {
        $modelClass = data_get($info, 'event.extendedProps.model');
        $key = data_get($info, 'event.extendedProps.key');

        if ($modelClass === BoarReservation::class && $key) {
            $this->redirect(ApprovedReservationResource::getUrl('view', ['record' => $key]));

            return;
        }

        parent::onEventClick($info, $action);
    }

    /**
     * Show accepted and confirmed boar reservations for the current boar raiser.
     */
    public function getEvents(array $fetchInfo = []): Collection|array
    {
        $userId = auth()->id();
        if (! $userId) {
            return collect();
        }

        $query = BoarReservation::query()
            ->whereHas('boar', fn ($q) => $q->where('user_id', $userId))
            ->whereIn('reservation_status', ['accepted', 'confirmed'])
            ->with(['boar', 'user']);

        if (! empty($fetchInfo['startStr']) && ! empty($fetchInfo['endStr'])) {
            $query->whereDate('service_date', '>=', $fetchInfo['startStr'])
                ->whereDate('service_date', '<=', $fetchInfo['endStr']);
        }

        return $query->get();
    }
}
