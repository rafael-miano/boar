<?php

namespace App\Filament\Customer\Resources\BoarReservationResource\Pages;

use App\Filament\Customer\Resources\BoarReservationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;

class ViewBoarReservation extends ViewRecord
{
    protected static string $resource = BoarReservationResource::class;

    protected static ?string $title = 'Reservation Details';

    public function openGcashQrModal(): void
    {
        $this->mountAction('viewGcashQr');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewGcashQr')
                ->label('View GCash QR')
                ->icon('heroicon-o-qr-code')
                ->color('gray')
                ->visible(false)
                ->modalHeading('GCash QR code – scan to pay')
                ->modalWidth('md')
                ->modalContent(function (): HtmlString {
                    $path = $this->record->boar?->gcash_qr_image;
                    if (!$path) {
                        return new HtmlString('<p class="text-gray-500 dark:text-gray-400">No QR code available.</p>');
                    }
                    $url = Storage::disk('public')->url($path);
                    return new HtmlString(
                        '<div class="flex flex-col items-center gap-4 p-6">' .
                        '<img src="' . e($url) . '" alt="GCash QR code" class="max-w-sm w-full rounded-lg border-2 border-gray-200 dark:border-gray-600 shadow-md" />' .
                        '<p class="text-sm text-gray-500 dark:text-gray-400 text-center">Scan this QR code with your GCash app to pay the downpayment.</p>' .
                        '</div>'
                    );
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Action::make('viewRejectionReason')
                ->label('Rejection reason')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('gray')
                ->size('sm')
                ->visible(fn () => $this->record->reservation_status === 'rejected' && !empty(trim($this->record->rejection_message ?? '')))
                ->modalHeading('Reason for rejection')
                ->modalContent(function (): HtmlString {
                    $message = $this->record->rejection_message;
                    $html = '<div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">';
                    $html .= '<p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">' . e($message) . '</p>';
                    $html .= '</div>';
                    return new HtmlString($html);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Action::make('uploadReceipt')
                ->label('Upload payment receipt')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => $this->record->service_fee_type === 'money'
                    && in_array($this->record->reservation_status, ['accepted', 'confirmed'])
                    && $this->record->payment_status !== 'verified')
                ->form([
                        FileUpload::make('payment_receipt_image')
                        ->label('GCash Payment Receipt')
                        ->validationAttribute('GCash Payment Receipt')
                        ->image()
                        ->required()
                        ->directory('payment-receipts')
                        ->visibility('public')
                        ->maxSize(5120)
                        ->helperText('Upload a clear image of your GCash payment receipt.'),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'payment_receipt_image' => $data['payment_receipt_image'],
                        'payment_status' => 'pending',
                    ]);

                    $boarRaiser = $this->record->boar->user;
                    if ($boarRaiser) {
                        Notification::make()
                            ->title('Payment receipt uploaded')
                            ->body('A customer uploaded a payment receipt for reservation #' . $this->record->id . '. Please verify or reject.')
                            ->icon('heroicon-o-banknotes')
                            ->sendToDatabase($boarRaiser);
                    }

                    Notification::make()
                        ->title('Receipt uploaded')
                        ->body('Your payment receipt has been submitted. The boar raiser will verify it shortly.')
                        ->success()
                        ->send();
                }),

            Action::make('confirmBirth')
                ->label('Confirm Birth')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn() => !$this->record->birth_confirmed_at
                    && $this->record->user_id === auth()->id()
                    && in_array($this->record->reservation_status, ['accepted', 'confirmed'])
                    && $this->record->service_status !== 'completed')
                ->form([
                    \Filament\Forms\Components\TextInput::make('piglet_count')
                        ->label('Piglets born')
                        ->helperText('Total number of piglets from this birth.')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'birth_confirmed_at' => now(),
                        'piglet_count' => $data['piglet_count'] ?? null,
                        'service_status' => 'completed',
                    ]);

                    // Notify boar-raiser
                    $boarRaiser = $this->record->boar->user;
                    if ($boarRaiser) {
                        Notification::make()
                            ->title('Piglets Born')
                            ->body('Piglets were confirmed born. Piglets born: ' . ($data['piglet_count'] ?? 'N/A'))
                            ->icon('heroicon-o-gift')
                            ->sendToDatabase($boarRaiser);
                    }

                    Notification::make()
                        ->title('Birth Confirmed')
                        ->success()
                        ->send();
                }),
        ];
    }
}

