<?php

namespace App\Filament\Resources\ApprovedReservationResource\Pages;

use App\Filament\Resources\ApprovedReservationResource;
use Filament\Resources\Pages\ListRecords;

class ListApprovedReservations extends ListRecords
{
    protected static string $resource = ApprovedReservationResource::class;

    protected static ?string $title = 'Boar Reservation Requests';
}
