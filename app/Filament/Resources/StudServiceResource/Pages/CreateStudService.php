<?php

namespace App\Filament\Resources\StudServiceResource\Pages;

use App\Filament\Resources\StudServiceResource;
use App\Models\BoarReservation;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateStudService extends CreateRecord
{
    protected static string $resource = StudServiceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure client_name is always filled based on the linked reservation.
        if (! empty($data['boar_reservation_id'])) {
            $reservation = BoarReservation::with('user')->find($data['boar_reservation_id']);
            if ($reservation && $reservation->user) {
                $data['client_name'] = $reservation->user->name;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Stud Service Saved Successfully')
            ->body('The stud service for "' . $this->record->client_name . '" has been created with service date "' . $this->record->service_date->format('F j, Y') . '".')
            ->success()
            ->color('success')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->color('primary')
                ->action(function () {
                    $this->save();
                }),
            $this->getCreateAnotherFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Save')
            ->color('primary')
            ->keyBindings(['mod+s']);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Save and Create Another');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancel');
    }

    protected function getFooterActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function save(): void
    {
        $this->create();
    }
}
