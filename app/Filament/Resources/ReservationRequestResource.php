<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationRequestResource\Pages;
use App\Models\BoarReservation;
use App\Notifications\BoarReservationAccepted;
use App\Filament\Customer\Resources\BoarReservationResource as CustomerBoarReservationResource;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;

class ReservationRequestResource extends Resource
{
    protected static ?string $model = BoarReservation::class;

    protected static ?string $navigationIcon = 'vaadin-file-process';

    protected static ?string $navigationLabel = 'Reservation Requests';

    protected static ?string $slug = 'reservation-requests';

    public static function getLabel(): string
    {
        return 'Reservation Request';
    }

    public static function getPluralLabel(): string
    {
        return 'Reservation Requests';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('boar_id')
                    ->label('Boar')
                    ->relationship('boar', 'boar_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('user_id')
                    ->label('Customer')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('address')
                    ->label('Customer Address')
                    ->required()
                    ->rows(3),
                Forms\Components\DatePicker::make('service_date')
                    ->label('Service Date')
                    ->required(),
                Forms\Components\Radio::make('service_fee_type')
                    ->label('Service Fee Type')
                    ->options([
                        'pig' => 'Pay with Pig (Offspring)',
                        'money' => 'Pay with Money',
                    ])
                    ->required()
                    ->inline(),
                Forms\Components\TextInput::make('service_fee_amount')
                    ->label('Service Fee Amount')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Forms\Components\FileUpload::make('female_pig_photo')
                    ->label('Female Pig Photo')
                    ->image()
                    ->directory('female-pig-photos')
                    ->visibility('private'),
                Forms\Components\Textarea::make('notes')
                    ->label('Additional Notes')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Select::make('service_status')
                    ->label('Service Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Panel::make([
                    // Row 1: images (boar / sow) side by side
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\Layout\Stack::make([
                            ImageColumn::make('boar.boar_picture')
                                ->label('Boar Image')
                                ->size(110)
                                ->alignCenter()
                                ->extraAttributes(['class' => 'rounded-lg shadow-sm'])
                                ->defaultImageUrl(url('/img/no-image.svg')),
                        ]),
                        Tables\Columns\Layout\Stack::make([
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
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\Layout\Stack::make([
                            TextColumn::make('boar.boar_name')
                                ->label('Boar')
                                ->weight('bold')
                                ->size('lg')
                                ->alignCenter(),
                        ]),
                        Tables\Columns\Layout\Stack::make([
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
                                ->color(fn($record) => $record->service_fee_type === 'pig' ? 'warning' : 'success')
                                ->alignCenter()
                                ->extraAttributes(['class' => 'mt-3']),
                        ]),
                    ]),

                    // Row 3: date & statuses
                    Tables\Columns\Layout\Stack::make([
                        TextColumn::make('service_date')
                            ->label('Service Date')
                            ->date()
                            ->icon('heroicon-o-calendar'),
                        TextColumn::make('reservation_status')
                            ->label('Reservation Status')
                            ->formatStateUsing(fn(string $state): string => 'Reservation: ' . match ($state) {
                                'pending_boar_raiser' => 'Pending boar raiser',
                                'confirmed' => 'Confirmed (paid)',
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
                        TextColumn::make('service_status')
                            ->label('Service Status')
                            ->formatStateUsing(fn($record) => 'Service: ' . match (true) {
                                $record->reservation_status === 'rejected' => 'Cancelled',
                                $record->service_status === 'cancelled' => 'Cancelled',
                                default => ucfirst($record->service_status),
                            })
                            ->badge()
                            ->color(fn($record) => match (true) {
                                $record->reservation_status === 'rejected' => 'danger',
                                $record->service_status === 'cancelled' => 'danger',
                                $record->service_status === 'completed' => 'success',
                                default => 'gray',
                            }),
                    ])->space(2),
                ])
                ->extraAttributes(['class' => 'gap-4']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reservation_status')
                    ->label('Reservation Status')
                    ->options([
                        'pending' => 'Pending',
                        'pending_boar_raiser' => 'Pending boar raiser',
                        'accepted' => 'Accepted',
                        'confirmed' => 'Confirmed (paid)',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('service_status')
                    ->label('Service Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('service_fee_type')
                    ->label('Payment Type')
                    ->options([
                        'pig' => 'Pay with Pig',
                        'money' => 'Pay with Money',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Section::make('')
                            ->schema([
                                // Column 1 - Boar Information
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

                                // Column 2 - Customer Information
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
                                        TextEntry::make('user.phone')
                                            ->label('Phone Number')
                                            ->copyable()
                                            ->copyMessage('Phone copied!'),
                                        TextEntry::make('address')
                                            ->label('Customer Address'),
                                    ])
                                    ->columnSpan(1),

                                // Column 3 - Service Details
                                Section::make('Service Details')
                                    ->schema([
                                        ImageEntry::make('female_pig_photo')
                                            ->label('Female Pig Photo')
                                            ->size(120)
                                            ->defaultImageUrl(asset('img/default_pfp.svg')),
                                        TextEntry::make('service_date')
                                            ->label('Service Date')
                                            ->date(),
                                        TextEntry::make('expected_due_date')
                                            ->label('Expected Due Date')
                                            ->date()
                                            ->visible(fn($record) => !empty($record->expected_due_date)),
                                        TextEntry::make('service_fee_type')
                                            ->label('Payment Type')
                                            ->formatStateUsing(function ($record) {
                                                return $record->service_fee_type === 'pig' ? 'Pay with Pig (Offspring)' : 'Pay with Money';
                                            })
                                            ->badge()
                                            ->color(fn($record) => $record->service_fee_type === 'pig' ? 'warning' : 'success'),
                                        TextEntry::make('service_fee_amount')
                                            ->label('Service Fee Amount')
                                            ->formatStateUsing(function ($record) {
                                                if ($record->service_fee_type === 'pig') {
                                                    $amount = (int) $record->service_fee_amount;
                                                    return $amount . ' ' . ($amount === 1 ? 'Pig' : 'Pigs');
                                                } else {
                                                    return 'Money - ₱' . number_format($record->service_fee_amount);
                                                }
                                            })
                                            ->weight('bold')
                                            ->color('success'),
                                        TextEntry::make('birth_confirmed_at')
                                            ->label('Birth Confirmed At')
                                            ->dateTime()
                                            ->visible(fn($record) => !empty($record->birth_confirmed_at)),
                                        TextEntry::make('piglet_count')
                                            ->label('Piglets born')
                                            ->helperText('Total number of piglets from this birth.')
                                            ->visible(fn($record) => !empty($record->piglet_count)),
                                        TextEntry::make('service_status')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(fn(string $state): string => $state === 'cancelled' ? 'Rejected' : ucfirst($state))
                                            ->color(fn(string $state): string => match ($state) {
                                                'pending' => 'warning',
                                                'completed' => 'success',
                                                'rejected' => 'danger',
                                                'cancelled' => 'danger',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('notes')
                                            ->label('Additional Notes')
                                            ->visible(fn($record) => !empty($record->notes)),
                                        TextEntry::make('created_at')
                                            ->label('Request Date')
                                            ->date(),
                                    ])
                                    ->columnSpan(1),
                            ])
                            ->columns(3),
                    ])
                    ->modalCancelAction(false),
                Tables\Actions\Action::make('accept')
                    ->label('Approve (send to boar raiser)')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve reservation request?')
                    ->modalDescription('This will send the request to the boar raiser for their final accept or reject. The customer is not confirmed until the boar raiser accepts.')
                    ->modalSubmitActionLabel('Yes, approve')
                    ->visible(fn($record) => $record->reservation_status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'reservation_status' => 'pending_boar_raiser',
                            'service_status' => 'pending',
                        ]);
                        // Notify the boar raiser to make the final accept/reject decision
                        $boarRaiser = $record->boar?->user;
                        if ($boarRaiser) {
                            $feeSummary = $record->service_fee_type === 'pig'
                                ? ((int) $record->service_fee_amount) . ' ' . (((int) $record->service_fee_amount) === 1 ? 'Pig' : 'Pigs')
                                : 'Money - ₱' . number_format((int) $record->service_fee_amount);

                            $customerName = $record->user?->name ?? 'a customer';
                            FilamentNotification::make()
                                ->title('You have new reservation from ' . $customerName)
                                ->body('There is a new reservation for your boar "' . $record->boar->boar_name . '". Customer: ' . $customerName . '. Payment: ' . $feeSummary . '. Accept or reject to confirm.')
                                ->icon('heroicon-o-check')
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View reservation')
                                        ->button()
                                        ->url(\App\Filament\Resources\ApprovedReservationResource::getUrl('view', ['record' => $record]))
                                ])
                                ->sendToDatabase($boarRaiser);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Request sent to boar raiser')
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record) => $record->reservation_status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject reservation request?')
                    ->modalDescription('Are you sure you want to reject this reservation request?')
                    ->modalSubmitActionLabel('Yes, reject')
                    ->visible(fn($record) => $record->reservation_status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'reservation_status' => 'rejected',
                            'service_status' => 'cancelled',
                            'expected_due_date' => null,
                        ]);
                        // Notify customer with link to view
                        $customer = $record->user;
                        if ($customer) {
                            $feeSummary = $record->service_fee_type === 'pig'
                                ? ((int) $record->service_fee_amount) . ' ' . (((int) $record->service_fee_amount) === 1 ? 'Pig' : 'Pigs')
                                : 'Money - ₱' . number_format((int) $record->service_fee_amount);

                            FilamentNotification::make()
                                ->title('Reservation Request Rejected')
                                ->body('Your reservation request was rejected. Payment: ' . $feeSummary)
                                ->icon('heroicon-o-x-mark')
                                ->color('danger')
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View request')
                                        ->button()
                                        ->url(CustomerBoarReservationResource::getUrl('view', ['record' => $record], panel: 'customer'))
                                ])
                                ->sendToDatabase($customer);
                        }
                        \Filament\Notifications\Notification::make()
                            ->title('Request Rejected')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn($record) => $record->reservation_status === 'pending'),

                Tables\Actions\Action::make('completeService')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Mark service as completed?')
                    ->modalDescription('Confirm that this reservation\'s service has been fulfilled.')
                    ->modalSubmitActionLabel('Yes, mark completed')
                    ->visible(fn($record) => auth()->user()?->role === 'boar-raiser'
                        && in_array($record->reservation_status, ['accepted', 'confirmed'])
                        && $record->service_status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'service_status' => 'completed',
                        ]);

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
            ->bulkActions([
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
                            ->defaultImageUrl(asset('img/default_pfp.svg')),
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
                        TextEntry::make('user.phone')
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
                            ->defaultImageUrl(asset('img/default_pfp.svg')),
                        TextEntry::make('service_date')
                            ->label('Service Date')
                            ->date(),
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
                            ->weight('bold')
                            ->color('success'),
                        TextEntry::make('reservation_status')
                            ->label('Reservation Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending_boar_raiser' => 'Pending boar raiser',
                                'confirmed' => 'Confirmed (paid)',
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
                        TextEntry::make('service_status')
                            ->label('Service Status')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => $state === 'cancelled' ? 'Rejected' : ucfirst($state))
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                                'rejected' => 'danger',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('notes')
                            ->label('Additional Notes')
                            ->visible(fn($record) => !empty($record->notes)),
                        TextEntry::make('created_at')
                            ->label('Request Date')
                            ->date(),
                    ])->columns(2)->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->role !== 'admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservationRequests::route('/'),
            'create' => Pages\CreateReservationRequest::route('/create'),
            'view' => Pages\ViewReservationRequest::route('/{record}'),
            // 'edit' => Pages\EditReservationRequest::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }
}

