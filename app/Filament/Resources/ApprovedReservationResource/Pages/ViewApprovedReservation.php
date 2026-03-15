<?php

namespace App\Filament\Resources\ApprovedReservationResource\Pages;

use App\Filament\Customer\Resources\BoarReservationResource as CustomerBoarReservationResource;
use App\Filament\Resources\ApprovedReservationResource;
use App\Models\BoarReservation;
use Filament\Actions;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\ViewRecord;

class ViewApprovedReservation extends ViewRecord
{
    protected static string $resource = ApprovedReservationResource::class;

    protected static ?string $title = 'Boar Reservation Request Details';

    public function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $needsDecision = $record->reservation_status === 'pending_boar_raiser';
        $canComplete = auth()->user()?->role === 'boar-raiser'
            && in_array($record->reservation_status, ['accepted', 'confirmed'])
            && $record->service_status === 'pending'
            && (
                $record->service_fee_type === 'pig'
                || $record->payment_status === 'verified'
            );
        $canVerifyPayment = $record->service_fee_type === 'money'
            && $record->reservation_status === 'accepted'
            && $record->payment_receipt_image
            && $record->payment_status === 'pending';

        $actions = [];

        if ($canVerifyPayment) {
            $actions[] = Actions\Action::make('verifyPayment')
                ->label('Verify payment')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verify payment?')
                ->modalDescription('Confirm you have received the downpayment. Reservation will be marked Confirmed and breeding can proceed.')
                ->modalSubmitActionLabel('Yes, verify')
                ->action(function () {
                    $record = $this->getRecord();
                    $record->update(array_merge([
                        'payment_status' => 'verified',
                        'payment_verified_at' => now(),
                        'reservation_status' => 'confirmed',
                    ], $record->calculatePlatformFee()));
                    $customer = $record->user;
                    if ($customer) {
                        FilamentNotification::make()
                            ->title('Payment verified')
                            ->body('Your payment has been verified. Breeding can proceed.')
                            ->icon('heroicon-o-check-badge')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('View reservation')
                                    ->button()
                                    ->url(CustomerBoarReservationResource::getUrl('view', ['record' => $record], panel: 'customer'))
                            ])
                            ->sendToDatabase($customer);
                    }
                    \Filament\Notifications\Notification::make()->title('Payment verified')->success()->send();
                    $this->redirect(ApprovedReservationResource::getUrl('view', ['record' => $record]));
                });

            $actions[] = Actions\Action::make('rejectPayment')
                ->label('Reject payment')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject payment?')
                ->modalDescription('The customer will be notified and can upload a new receipt.')
                ->modalSubmitActionLabel('Yes, reject')
                ->action(function () {
                    $record = $this->getRecord();
                    $record->update(['payment_status' => 'rejected']);
                    $customer = $record->user;
                    if ($customer) {
                        FilamentNotification::make()
                            ->title('Payment rejected')
                            ->body('Your payment receipt was rejected. Please upload a valid receipt or contact the boar raiser.')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('View reservation')
                                    ->button()
                                    ->url(CustomerBoarReservationResource::getUrl('view', ['record' => $record], panel: 'customer'))
                            ])
                            ->sendToDatabase($customer);
                    }
                    \Filament\Notifications\Notification::make()->title('Payment rejected')->warning()->send();
                    $this->redirect(ApprovedReservationResource::getUrl('view', ['record' => $record]));
                });
        }

        if ($needsDecision) {
            $actions[] = Actions\Action::make('accept')
                ->label('Accept')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Accept this reservation?')
                ->modalDescription('You are confirming that you will fulfill this service. The customer will be notified.')
                ->modalSubmitActionLabel('Yes, accept')
                ->action(function () {
                    $record = $this->getRecord();
                    $record->update([
                        'reservation_status' => 'accepted',
                        'service_status' => 'pending',
                        'expected_due_date' => \Carbon\Carbon::parse($record->service_date)->addDays(115)->toDateString(),
                        'approved_at' => now(),
                    ]);
                    $customer = $record->user;
                    if ($customer) {
                        $boarRaiserName = $record->boar?->user?->name ?? 'boar raiser';
                        $feeSummary = $record->service_fee_type === 'pig'
                            ? ((int) $record->service_fee_amount) . ' ' . (((int) $record->service_fee_amount) === 1 ? 'Pig' : 'Pigs')
                            : 'Money - ₱' . number_format((int) $record->service_fee_amount);
                        $body = 'Your reservation was accepted. Payment: ' . $feeSummary;
                        if ($record->service_fee_type === 'money') {
                            $body .= ' You can pay the downpayment in My Reservations (see GCash QR and upload your receipt after paying).';
                        }

                        FilamentNotification::make()
                            ->title('Reservation accepted by ' . $boarRaiserName)
                            ->body($body)
                            ->icon('heroicon-o-check')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('View request')
                                    ->button()
                                    ->url(CustomerBoarReservationResource::getUrl('view', ['record' => $record], panel: 'customer'))
                            ])
                            ->sendToDatabase($customer);
                    }
                    FilamentNotification::make()->title('Reservation accepted')->success()->send();
                    $this->redirect(ApprovedReservationResource::getUrl('view', ['record' => $record]));
                });

            $actions[] = Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->modalHeading('Reject this reservation?')
                ->modalDescription('The customer will be notified. You can leave a message so they know the reason.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_message')
                        ->label('Message to customer (optional)')
                        ->placeholder('e.g. Boar is unavailable on that date, or other reason...')
                        ->rows(4)
                        ->maxLength(1000)
                        ->helperText('The customer will see this message when they view their reservation.'),
                ])
                ->modalSubmitActionLabel('Reject')
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    $record->update([
                        'reservation_status' => 'rejected',
                        'service_status' => 'cancelled',
                        'expected_due_date' => null,
                        'rejection_message' => $data['rejection_message'] ?? null,
                        'rejected_at' => now(),
                    ]);
                    $customer = $record->user;
                    if ($customer) {
                        $boarRaiserName = $record->boar?->user?->name ?? 'boar raiser';
                        $body = 'The boar raiser declined your reservation request.';
                        if (! empty(trim($record->rejection_message ?? ''))) {
                            $body .= ' Reason: ' . $record->rejection_message;
                        }
                        FilamentNotification::make()
                            ->title('Reservation declined by ' . $boarRaiserName)
                            ->body($body)
                            ->icon('heroicon-o-x-mark')
                            ->color('danger')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('View details')
                                    ->button()
                                    ->url(CustomerBoarReservationResource::getUrl('view', ['record' => $record], panel: 'customer'))
                            ])
                            ->sendToDatabase($customer);
                    }
                    \Filament\Notifications\Notification::make()->title('Reservation rejected')->warning()->send();
                    $this->redirect(ApprovedReservationResource::getUrl('index'));
                });
        }

        if ($canComplete) {
            $actions[] = Actions\Action::make('completeService')
                ->label('Mark Service Completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark service as completed?')
                ->modalDescription('Confirm that this reservation\'s service has been fulfilled.')
                ->modalSubmitActionLabel('Yes, mark completed')
                ->action(function () {
                    $record = $this->getRecord();
                    $record->update(['service_status' => 'completed']);

                    $boar = $record->boar;
                    if ($boar) {
                        $boar->update([
                            'marketplace_available_at' => now()->addDays(14),
                        ]);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Service marked as completed')
                        ->body('The boar has been temporarily hidden from the marketplace and will reappear automatically in 2 weeks.')
                        ->success()
                        ->send();
                    $this->redirect(ApprovedReservationResource::getUrl('view', ['record' => $record]));
                });
        }

        return $actions;
    }
}
