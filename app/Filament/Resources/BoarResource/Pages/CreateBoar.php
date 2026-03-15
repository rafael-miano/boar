<?php

namespace App\Filament\Resources\BoarResource\Pages;

use App\Filament\Resources\BoarResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBoar extends CreateRecord
{
    protected static string $resource = BoarResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $data['user_id'] = $user?->id;

        // Boar raiser publishing should go through admin approval first.
        if ($user && $user->role === 'boar-raiser') {
            if (!empty($data['is_published'])) {
                // Boar raiser requested publish – wait for admin approval.
                $data['publish_status'] = 'pending_admin';
                $data['is_published'] = false;
            } else {
                $data['publish_status'] = 'draft';
                $data['is_published'] = false;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Boar Saved Successfully')
            ->body('The boar "' . $this->record->boar_name . '" has been created with type "' . $this->record->boar_type . '".')
            ->success()
            ->color('success')
            ->send();

        if ($this->record->publish_status === 'pending_admin') {
            $boarRaiserName = auth()->user()?->name ?? 'A boar raiser';
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Boar Awaiting Marketplace Approval')
                    ->body($boarRaiserName . ' submitted the boar "' . $this->record->boar_name . '" for marketplace approval. Go to Boar Approval to review.')
                    ->icon('heroicon-o-clock')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('review')
                            ->label('Review boar')
                            ->button()
                            ->url(BoarResource::getUrl('index')),
                    ])
                    ->sendToDatabase($admin);
            }
        }
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
