<?php

namespace App\Filament\Resources\BoarResource\Pages;

use App\Filament\Resources\BoarResource;
use App\Models\Boar;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class EditBoar extends EditRecord
{
    protected static string $resource = BoarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->color('primary')
                ->keyBindings(['mod+s'])
                ->action(function () {
                    $this->save();
                }),
            Actions\DeleteAction::make()
                ->label('Archive')
                ->modalHeading('Archive Boar')
                ->modalDescription('Are you sure you want to archive this boar? You can restore it later from Archived tab.')
                ->before(function (Actions\DeleteAction $action, Boar $record): void {
                    $record->archived_at = now();
                    $record->save();
                })
                ->successNotification(fn (Boar $record) =>
                    Notification::make()
                        ->title('Boar archived')
                        ->body('The boar "' . $record->boar_name . '" has been archived on ' . ($record->archived_at?->format('F j, Y g:i A') ?? now()->format('F j, Y g:i A')) . '.')
                        ->success()
                        ->color('success')
                ),
            $this->getCancelFormAction()->label('Cancel')
                ->color('warning'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return null;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Boar Saved Successfully')
            ->body('The boar "' . $this->record->boar_name . '" has been saved.')
            ->success()
            ->color('success')
            ->send();

        if ($this->record->publish_status === 'pending_admin' && $this->record->wasChanged('publish_status')) {
            $boarRaiserName = auth()->user()?->name ?? 'A boar raiser';
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Boar Awaiting Marketplace Approval')
                    ->body($boarRaiserName . ' requested approval to publish the boar "' . $this->record->boar_name . '" to the marketplace. Go to Boar Approval to review.')
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        // For boar raisers, publishing must be approved by admin.
        if ($user && $user->role === 'boar-raiser') {
            if (!empty($data['is_published'])) {
                // Request (or keep) publish – admin will approve.
                if ($this->record->publish_status !== 'approved') {
                    $data['publish_status'] = 'pending_admin';
                } else {
                    $data['publish_status'] = 'approved';
                }

                // Boar raiser cannot directly make it visible.
                $data['is_published'] = $this->record->is_published;
            } else {
                // Unpublish / keep as draft.
                $data['publish_status'] = 'draft';
                $data['is_published'] = false;
            }
        }

        return $data;
    }

    protected function getFooterActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
