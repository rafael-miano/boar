<?php

namespace App\Filament\Resources;

use App\Filament\Customer\Resources\BoarReservationResource as CustomerBoarReservationResource;
use App\Filament\Resources\ApprovedReservationResource\Pages;
use App\Models\BoarReservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Table;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class ApprovedReservationResource extends Resource
{
    protected static ?string $model = BoarReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Boar Reservation Requests';

    protected static ?string $slug = 'boar-reservation-requests';

    public static function getLabel(): string
    {
        return 'Boar Reservation Request';
    }

    public static function getPluralLabel(): string
    {
        return 'Boar Reservation Requests';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = BoarReservation::query()
            ->whereHas('boar', fn (Builder $q) => $q->where('user_id', auth()->id()))
            ->where('reservation_status', 'pending_boar_raiser')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('boar', fn (Builder $q) => $q->where('user_id', auth()->id()))
            ->whereIn('reservation_status', ['pending_boar_raiser', 'accepted', 'confirmed']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Panel::make([
                    // Row 1: images (boar / sow) side by side
                    Split::make([
                        Stack::make([
                            ImageColumn::make('boar.boar_picture')
                                ->label('Boar Image')
                                ->size(110)
                                ->alignCenter()
                                ->extraAttributes(['class' => 'rounded-lg shadow-sm'])
                                ->defaultImageUrl(url('/img/no-image.svg')),
                        ]),
                        Stack::make([
                            ImageColumn::make('female_pig_photo')
                                ->label('Sow Image')
                                ->size(110)
                                ->alignCenter()
                                ->extraAttributes(['class' => 'rounded-lg shadow-sm'])
                                ->defaultImageUrl(url('/img/no-image.svg')),
                        ]),
                    ])
                        ->extraAttributes(['class' => '!flex !flex-row !flex-nowrap gap-4']),

                    // Row 2: names (boar / customer) + payment under customer
                    Split::make([
                        Stack::make([
                            TextColumn::make('boar.boar_name')
                                ->label('Boar')
                                ->weight('bold')
                                ->size('lg')
                                ->alignCenter(),
                        ]),
                        Stack::make([
                            TextColumn::make('user.name')
                                ->label('Customer')
                                ->weight('bold')
                                ->size('lg')
                                ->alignCenter()
                                ->extraAttributes(['style' => 'margin-top: 32px;']),
                            TextColumn::make('service_fee_type')
                                ->label('Payment')
                                ->formatStateUsing(function ($record) {
                                    $label = 'Payment: ';
                                    if ($record->service_fee_type === 'pig') {
                                        $amount = (int) $record->service_fee_amount;
                                        return $label . $amount . ' ' . ($amount === 1 ? 'Pig' : 'Pigs');
                                    }

                                    return $label . '₱' . number_format($record->service_fee_amount);
                                })
                                ->badge()
                                ->color(fn ($record) => $record->service_fee_type === 'pig' ? 'warning' : 'success')
                                ->alignCenter()
                                ->extraAttributes(['class' => 'mt-3']),
                        ]),
                    ]),

                    // Row 3: date & statuses
                    Stack::make([
                        TextColumn::make('service_date')
                            ->label('Service Date')
                            ->date()
                            ->icon('heroicon-o-calendar'),
                        TextColumn::make('reservation_status')
                            ->label('Reservation Status')
                            ->formatStateUsing(fn (string $state): string => 'Reservation: ' . match ($state) {
                                'pending_boar_raiser' => 'Pending boar raiser',
                                'confirmed' => 'Confirmed (paid)',
                                default => ucfirst(str_replace('_', ' ', $state)),
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'pending_boar_raiser' => 'info',
                                'accepted' => 'success',
                                'confirmed' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextColumn::make('service_status')
                            ->label('Service Status')
                            ->formatStateUsing(fn ($record) => 'Service: ' . match (true) {
                                $record->reservation_status === 'rejected' => 'Cancelled',
                                $record->service_status === 'cancelled' => 'Cancelled',
                                default => ucfirst($record->service_status),
                            })
                            ->badge()
                            ->color(fn ($record) => match (true) {
                                $record->reservation_status === 'rejected' => 'danger',
                                $record->service_status === 'cancelled' => 'danger',
                                $record->service_status === 'completed' => 'success',
                                default => 'gray',
                            }),
                    ])->space(2),
                ])
                    ->extraAttributes(['class' => 'gap-4']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Section::make('Boar Information')
                            ->schema([
                                ImageEntry::make('boar.boar_picture')
                                    ->label('Boar Image')
                                    ->size(120)
                                    ->defaultImageUrl(url('/img/no-image.svg')),
                                TextEntry::make('boar.boar_name')
                                    ->label('Boar Name')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('boar.boar_type')
                                    ->label('Boar Type')
                                    ->badge()
                                    ->color('info'),
                            ])
                            ->columnSpan(1),

                        Section::make('Customer Information')
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Customer Name')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('user.email')
                                    ->label('Customer Email')
                                    ->copyable()
                                    ->copyMessage('Email copied!'),
                                TextEntry::make('user.phone_number')
                                    ->label('Phone Number')
                                    ->copyable()
                                    ->copyMessage('Phone copied!'),
                                TextEntry::make('address')
                                    ->label('Customer Address'),
                            ])
                            ->columnSpan(1),

                        Section::make('Service Details')
                            ->schema([
                                ImageEntry::make('female_pig_photo')
                                    ->label('Female Pig Photo')
                                    ->size(120)
                                    ->defaultImageUrl(url('/img/no-image.svg')),
                                TextEntry::make('service_date')
                                    ->label('Service Date')
                                    ->date(),
                                TextEntry::make('expected_due_date')
                                    ->label('Expected Due Date')
                                    ->date()
                                    ->visible(fn ($record) => ! empty($record->expected_due_date)),
                                TextEntry::make('service_fee_type')
                                    ->label('Payment Type')
                                    ->formatStateUsing(fn ($record) => $record->service_fee_type === 'pig' ? 'Pay with Pig (Offspring)' : 'Pay with Money')
                                    ->badge()
                                    ->color(fn ($record) => $record->service_fee_type === 'pig' ? 'warning' : 'success'),
                                TextEntry::make('service_fee_amount')
                                    ->label('Service Fee Amount')
                                    ->formatStateUsing(function ($record) {
                                        if ($record->service_fee_type === 'pig') {
                                            $amount = (int) $record->service_fee_amount;
                                            return $amount . ' ' . ($amount === 1 ? 'Pig' : 'Pigs');
                                        }
                                        return 'Money - ₱' . number_format($record->service_fee_amount);
                                    })
                                    ->weight('bold')
                                    ->color('success'),
                                TextEntry::make('platform_fee')
                                    ->label('Platform fee')
                                    ->formatStateUsing(fn ($state) => $state !== null ? '₱' . number_format((float) $state, 2) : '—')
                                    ->visible(fn ($record) => $record->platform_fee !== null),
                                TextEntry::make('boar_raiser_share')
                                    ->label('Your share')
                                    ->formatStateUsing(fn ($state) => $state !== null ? '₱' . number_format((float) $state, 2) : '—')
                                    ->visible(fn ($record) => $record->boar_raiser_share !== null),
                                ImageEntry::make('payment_receipt_image')
                                    ->label('Payment receipt')
                                    ->disk('public')
                                    ->height(140)
                                    ->visible(fn ($record) => $record->service_fee_type === 'money' && $record->payment_receipt_image),
                                TextEntry::make('payment_status')
                                    ->label('Payment status')
                                    ->badge()
                                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '—')
                                    ->color(fn (?string $state): string => match ($state) {
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        'pending' => 'warning',
                                        default => 'gray',
                                    })
                                    ->visible(fn ($record) => $record->service_fee_type === 'money'),
                                TextEntry::make('reservation_status')
                                    ->label('Reservation status')
                                    ->badge()
                                    ->formatStateUsing(fn ($record): string => match ($record->reservation_status) {
                                        'pending_boar_raiser' => 'Pending your decision',
                                        'accepted' => $record->service_status === 'pending' ? 'Accepted – to fulfill' : 'Completed',
                                        'confirmed' => 'Confirmed (paid)',
                                        default => ucfirst($record->reservation_status ?? ''),
                                    })
                                    ->color(fn ($record): string => match (true) {
                                        $record->reservation_status === 'pending_boar_raiser' => 'warning',
                                        $record->reservation_status === 'confirmed' => 'success',
                                        $record->service_status === 'completed' => 'success',
                                        default => 'info',
                                    }),
                                TextEntry::make('service_status')
                                    ->label('Service Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => $state === 'cancelled' ? 'Cancelled' : ucfirst($state))
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('notes')
                                    ->label('Additional Notes')
                                    ->visible(fn ($record) => ! empty($record->notes)),
                                TextEntry::make('created_at')
                                    ->label('Approved At')
                                    ->date(),
                            ])
                            ->columnSpan(1),
                    ])
                    ->modalCancelAction(false),
                Tables\Actions\Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Accept this reservation?')
                    ->modalDescription('You are confirming that you will fulfill this service. The customer will be notified.')
                    ->modalSubmitActionLabel('Yes, accept')
                    ->visible(fn ($record) => $record && $record->reservation_status === 'pending_boar_raiser')
                    ->action(function ($record) {
                        $alreadyAccepted = BoarReservation::where('boar_id', $record->boar_id)
                            ->where('service_date', $record->service_date)
                            ->whereIn('reservation_status', ['accepted', 'confirmed'])
                            ->where('id', '!=', $record->id)
                            ->exists();

                        if ($alreadyAccepted) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot accept this reservation')
                                ->body('This boar already has an accepted reservation on ' . \Carbon\Carbon::parse($record->service_date)->format('F j, Y') . '. Only one reservation can be accepted per boar per day.')
                                ->danger()
                                ->send();

                            return;
                        }

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
                        \Filament\Notifications\Notification::make()
                            ->title('Reservation accepted')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->modalHeading('Reject this reservation?')
                    ->modalDescription('The customer will be notified. You can leave a message so they know the reason.')
                    ->form([
                        Forms\Components\Textarea::make('rejection_message')
                            ->label('Message to customer (optional)')
                            ->placeholder('e.g. Boar is unavailable on that date, or other reason...')
                            ->rows(4)
                            ->maxLength(1000)
                            ->helperText('The customer will see this message when they view their reservation.'),
                    ])
                    ->modalSubmitActionLabel('Reject')
                    ->visible(fn ($record) => $record && $record->reservation_status === 'pending_boar_raiser')
                    ->action(function ($record, array $data) {
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
                        \Filament\Notifications\Notification::make()
                            ->title('Reservation rejected')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\Action::make('verifyPayment')
                    ->label('Verify payment')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verify payment?')
                    ->modalDescription('Confirm you have received the downpayment. Reservation will be marked Confirmed and breeding can proceed.')
                    ->modalSubmitActionLabel('Yes, verify')
                    ->visible(function ($record) {
                        if (! $record) {
                            return false;
                        }

                        $user = auth()->user();
                        if (! $user || $user->role !== 'boar-raiser') {
                            return false;
                        }

                        return $record->service_fee_type === 'money'
                            && $record->reservation_status === 'accepted'
                            && $record->payment_receipt_image
                            && $record->payment_status === 'pending';
                    })
                    ->action(function ($record) {
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
                        \Filament\Notifications\Notification::make()
                            ->title('Payment verified')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('rejectPayment')
                    ->label('Reject payment')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject payment?')
                    ->modalDescription('The customer will be notified and can upload a new receipt.')
                    ->modalSubmitActionLabel('Yes, reject')
                    ->visible(fn ($record) => $record && $record->service_fee_type === 'money' && $record->reservation_status === 'accepted' && $record->payment_receipt_image && $record->payment_status === 'pending')
                    ->action(function ($record) {
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
                        \Filament\Notifications\Notification::make()
                            ->title('Payment rejected')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\Action::make('completeService')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark service as completed?')
                    ->modalDescription('Confirm that this reservation\'s service has been fulfilled.')
                    ->modalSubmitActionLabel('Yes, mark completed')
                    ->visible(function ($record) {
                        if (! $record) {
                            return false;
                        }

                        if (auth()->user()?->role !== 'boar-raiser') {
                            return false;
                        }

                        // Only allow marking completed when service is pending AND:
                        // - payment type is pig, or
                        // - for money payments, the payment has been verified.
                        $isRightStatus = in_array($record->reservation_status, ['accepted', 'confirmed'])
                            && $record->service_status === 'pending';

                        if (! $isRightStatus) {
                            return false;
                        }

                        if ($record->service_fee_type === 'pig') {
                            return true;
                        }

                        return $record->payment_status === 'verified';
                    })
                    ->action(function ($record) {
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
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Boar Information')
                    ->schema([
                        ImageEntry::make('boar.boar_picture')
                            ->label('Boar Image')
                            ->size(140)
                            ->defaultImageUrl(url('/img/no-image.svg')),
                        TextEntry::make('boar.boar_name')
                            ->label('Boar Name')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('boar.boar_type')
                            ->label('Boar Type')
                            ->badge()
                            ->color('info'),
                    ])->columns(1)->columnSpan(1),

                Section::make('Customer Information')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Customer Name')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('user.email')
                            ->label('Customer Email')
                            ->copyable()
                            ->copyMessage('Email copied!'),
                        TextEntry::make('user.phone_number')
                            ->label('Phone Number')
                            ->copyable()
                            ->copyMessage('Phone copied!'),
                        TextEntry::make('address')
                            ->label('Customer Address'),
                    ])->columns(1)->columnSpan(1),

                Section::make('Service Details')
                    ->schema([
                        ImageEntry::make('female_pig_photo')
                            ->label('Female Pig Photo')
                            ->size(140)
                            ->defaultImageUrl(url('/img/no-image.svg')),
                        TextEntry::make('service_date')
                            ->label('Service Date')
                            ->date(),
                        TextEntry::make('expected_due_date')
                            ->label('Expected Due Date')
                            ->date()
                            ->visible(fn ($record) => ! empty($record->expected_due_date)),
                        TextEntry::make('service_fee_type')
                            ->label('Payment Type')
                            ->formatStateUsing(fn ($record) => $record->service_fee_type === 'pig' ? 'Pay with Pig (Offspring)' : 'Pay with Money')
                            ->badge()
                            ->color(fn ($record) => $record->service_fee_type === 'pig' ? 'warning' : 'success'),
                        TextEntry::make('service_fee_amount')
                            ->label('Service Fee Amount')
                            ->formatStateUsing(function ($record) {
                                if ($record->service_fee_type === 'pig') {
                                    $amount = (int) $record->service_fee_amount;
                                    return $amount . ' ' . ($amount === 1 ? 'Pig' : 'Pigs');
                                }
                                return 'Money - ₱' . number_format($record->service_fee_amount);
                            })
                            ->weight('bold')
                            ->color('success'),
                        TextEntry::make('platform_fee')
                            ->label('Platform fee')
                            ->formatStateUsing(fn ($state) => $state !== null ? '₱' . number_format((float) $state, 2) : '—')
                            ->visible(fn ($record) => $record->platform_fee !== null),
                        TextEntry::make('boar_raiser_share')
                            ->label('Your share')
                            ->formatStateUsing(fn ($state) => $state !== null ? '₱' . number_format((float) $state, 2) : '—')
                            ->visible(fn ($record) => $record->boar_raiser_share !== null),
                        ImageEntry::make('payment_receipt_image')
                            ->label('Payment receipt')
                            ->disk('public')
                            ->height(140)
                            ->visible(fn ($record) => $record->service_fee_type === 'money' && $record->payment_receipt_image),
                        TextEntry::make('payment_status')
                            ->label('Payment status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '—')
                            ->color(fn (?string $state): string => match ($state) {
                                'verified' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            })
                            ->visible(fn ($record) => $record->service_fee_type === 'money'),
                        TextEntry::make('reservation_status')
                            ->label('Reservation status')
                            ->badge()
                            ->formatStateUsing(fn ($record): string => match ($record->reservation_status) {
                                'pending_boar_raiser' => 'Pending your decision',
                                'accepted' => $record->service_status === 'pending' ? 'Accepted – to fulfill' : 'Completed',
                                'confirmed' => 'Confirmed (paid)',
                                default => ucfirst($record->reservation_status ?? ''),
                            })
                            ->color(fn ($record): string => match (true) {
                                $record->reservation_status === 'pending_boar_raiser' => 'warning',
                                $record->reservation_status === 'confirmed' => 'success',
                                $record->service_status === 'completed' => 'success',
                                default => 'info',
                            }),
                        TextEntry::make('service_status')
                            ->label('Service Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => $state === 'cancelled' ? 'Cancelled' : ucfirst($state))
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('notes')
                            ->label('Additional Notes')
                            ->visible(fn ($record) => ! empty($record->notes)),
                        // TextEntry::make('created_at')
                        //     ->label('Approved At')
                        //     ->date(),
                    ])->columns(2)->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovedReservations::route('/'),
            'view' => Pages\ViewApprovedReservation::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'boar-raiser';
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
