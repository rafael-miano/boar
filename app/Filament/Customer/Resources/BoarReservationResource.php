<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\BoarReservationResource\Pages;
use App\Models\BoarRating;
use App\Models\BoarReservation;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BoarReservationResource extends Resource
{
    protected static ?string $model = BoarReservation::class;

    protected static ?string $navigationIcon = 'vaadin-file-process';

    protected static ?string $navigationLabel = 'My Reservations';

    protected static ?string $slug = 'boar-reservations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->with('rating');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('boar.boar_picture')
                    ->label('Boar')
                    ->size(80)
                    ->alignCenter()
                    ->extraAttributes(['class' => 'rounded-lg shadow-sm'])
                    ->defaultImageUrl(url('/img/no-image.svg')),
                TextColumn::make('boar.boar_name')
                    ->label('Boar Name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_date')
                    ->label('Service Date')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->visibleFrom('md'),
                TextColumn::make('reservation_status')
                    ->label('Reservation')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_boar_raiser' => 'Pending boar raiser',
                        'confirmed' => 'Confirmed',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'pending_boar_raiser' => 'info',
                        'accepted' => 'success',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('service_status')
                    ->label('Service')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'cancelled' ? 'Cancelled' : ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->visibleFrom('md'),
                TextColumn::make('rating.rating')
                    ->label('Your Rating')
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', (int) $state) : '—')
                    ->placeholder('—')
                    ->visibleFrom('md'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->url(fn($record) => static::getUrl('view', ['record' => $record]))
                    ->button(),

                Tables\Actions\Action::make('rate')
                    ->label('Rate')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->button()
                    ->modalHeading('Rate this service')
                    ->modalDescription('Share your experience with this boar and breeder. Your feedback helps other customers.')
                    ->modalSubmitActionLabel('Submit Rating')
                    ->form([
                        Forms\Components\Radio::make('rating')
                            ->label('Star Rating')
                            ->options([
                                1 => '⭐ 1 — Poor',
                                2 => '⭐⭐ 2 — Fair',
                                3 => '⭐⭐⭐ 3 — Good',
                                4 => '⭐⭐⭐⭐ 4 — Very Good',
                                5 => '⭐⭐⭐⭐⭐ 5 — Excellent',
                            ])
                            ->required()
                            ->inline(),
                        Forms\Components\Textarea::make('comment')
                            ->label('Comment (optional)')
                            ->placeholder('Tell us about your experience...')
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->visible(fn ($record) => $record->service_status === 'completed' && ! $record->rating)
                    ->action(function ($record, array $data) {
                        BoarRating::create([
                            'boar_reservation_id' => $record->id,
                            'boar_id'             => $record->boar_id,
                            'customer_id'         => auth()->id(),
                            'rating'              => $data['rating'],
                            'comment'             => $data['comment'] ?? null,
                        ]);

                        FilamentNotification::make()
                            ->title('Rating submitted!')
                            ->body('Thank you for your feedback.')
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
                        Grid::make(2)
                            ->schema([
                                Section::make()
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
                                    ->columnSpan(1)
                                    ->columns(1),
                                Section::make('Owner')
                                    ->schema([
                                        TextEntry::make('boar.user.name')
                                            ->label('Name')
                                            ->weight('bold'),
                                        TextEntry::make('boar.user.phone_number')
                                            ->label('Phone')
                                            ->visible(fn ($record) => !empty($record->boar?->user?->phone_number)),
                                        TextEntry::make('boar.user.email')
                                            ->label('Email')
                                            ->visible(fn ($record) => !empty($record->boar?->user?->email)),
                                        TextEntry::make('boar.user.address')
                                            ->label('Address')
                                            ->visible(fn ($record) => !empty($record->boar?->user?->address)),
                                    ])
                                    ->columnSpan(1)
                                    ->columns(1),
                            ]),
                    ])->columnSpan(1),

                Section::make('Pay your downpayment (GCash)')
                    ->description('You can pay the downpayment when the boar raiser has accepted your reservation. Scan the GCash QR below and upload your payment receipt after paying.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                View::make('filament.infolists.gcash-qr-clickable')
                                    ->label('GCash QR code')
                                    ->visible(fn ($record) => $record->service_fee_type === 'money' && in_array($record->reservation_status, ['accepted', 'confirmed']) && $record->boar?->gcash_qr_image)
                                    ->columnSpan(1),
                                TextEntry::make('service_fee_amount')
                                    ->label('Downpayment amount')
                                    ->formatStateUsing(fn ($record) => $record->service_fee_type === 'money' ? '₱' . number_format((int) $record->service_fee_amount) : '—')
                                    ->weight('bold')
                                    ->visible(fn ($record) => $record->service_fee_type === 'money' && in_array($record->reservation_status, ['accepted', 'confirmed']))
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->service_fee_type === 'money' && in_array($record->reservation_status, ['accepted', 'confirmed']))
                    ->columns(1)
                    ->columnSpan(1),

                Section::make('Service Details')
                    ->schema([
                        TextEntry::make('service_date')
                            ->label('Service Date')
                            ->date(),
                        TextEntry::make('expected_due_date')
                            ->label('Expected Due Date')
                            ->date()
                            ->visible(fn($record) => !empty($record->expected_due_date)),
                        TextEntry::make('service_fee_type')
                            ->label('Payment Type')
                            ->formatStateUsing(fn($record) => $record->service_fee_type === 'pig' ? 'Pay with Pig (Offspring)' : 'Pay with Money')
                            ->badge()
                            ->color(fn($record) => $record->service_fee_type === 'pig' ? 'warning' : 'success'),
                        TextEntry::make('service_fee_amount')
                            ->label('Service Fee Amount')
                            ->formatStateUsing(function ($record) {
                                if ($record->service_fee_type === 'pig') {
                                    $amount = (int) $record->service_fee_amount;
                                    return $amount . ' ' . ($amount === 1 ? 'Pig' : 'Pigs');
                                }
                                return 'Money - ₱' . number_format($record->service_fee_amount);
                            })
                            ->weight('bold'),
                        TextEntry::make('payment_status')
                            ->label('Payment Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '—')
                            ->color(fn (?string $state): string => match ($state) {
                                'verified' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            })
                            ->visible(fn ($record) => $record->service_fee_type === 'money'),
                        ImageEntry::make('payment_receipt_image')
                            ->label('Your payment receipt')
                            ->disk(\App\Support\StorageHelper::uploadDisk())
                            ->height(120)
                            ->visible(fn ($record) => $record->service_fee_type === 'money' && $record->payment_receipt_image),
                        TextEntry::make('payment_receipt_image')
                            ->label('Payment receipt')
                            ->formatStateUsing(fn ($record) => $record->payment_receipt_image ? 'Uploaded' : 'Not uploaded yet')
                            ->visible(fn ($record) => $record->service_fee_type === 'money' && !$record->payment_receipt_image),
                                        TextEntry::make('birth_confirmed_at')
                                            ->label('Birth Confirmed At')
                                            ->dateTime()
                                            ->visible(fn($record) => !empty($record->birth_confirmed_at)),
                        TextEntry::make('piglet_count')
                            ->label('Piglets born')
                            ->helperText('Total number of piglets from this birth.')
                            ->visible(fn($record) => !empty($record->piglet_count)),
                        TextEntry::make('reservation_status')
                            ->label('Reservation Status')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending_boar_raiser' => 'Pending boar raiser',
                                'confirmed' => 'Confirmed (payment verified)',
                                default => ucfirst(str_replace('_', ' ', $state)),
                            })
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'pending_boar_raiser' => 'info',
                                'accepted' => 'success',
                                'confirmed' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('rejected_at')
                            ->label('Rejected at')
                            ->dateTime(format: 'F j, Y, g:i A')
                            ->visible(fn ($record) => $record->reservation_status === 'rejected' && $record->rejected_at),
                        TextEntry::make('approved_at')
                            ->label('Approved at')
                            ->dateTime(format: 'F j, Y, g:i A')
                            ->visible(fn ($record) => in_array($record->reservation_status, ['accepted', 'confirmed']) && $record->approved_at),
                        TextEntry::make('service_status')
                            ->label('Service Status')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => $state === 'cancelled' ? 'Cancelled' : ucfirst($state))
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'gray',
                                'completed' => 'success',
                                'rejected' => 'danger',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('notes')
                            ->label('Additional Notes')
                            ->visible(fn($record) => !empty($record->notes)),
                        TextEntry::make('created_at')
                            ->label('Requested At')
                            ->dateTime(format: 'F j, Y, g:i A'),
                    ])->columns(2)->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoarReservations::route('/'),
            'view' => Pages\ViewBoarReservation::route('/{record}'),
        ];
    }
}

