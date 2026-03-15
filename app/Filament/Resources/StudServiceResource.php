<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudServiceResource\Pages;
use App\Filament\Resources\StudServiceResource\RelationManagers;
use App\Models\StudService;
use App\Models\BoarReservation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudServiceResource extends Resource
{
    protected static ?string $model = StudService::class;

    protected static ?string $navigationIcon = 'icon-stud-service-pig';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stud Service Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('boar_id')
                            ->label('Boar Name')
                            ->required()
                            ->relationship(
                                'boar',
                                'boar_name',
                                fn (Builder $query) => $query->where('user_id', auth()->id())
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->placeholder('Select a boar')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Reset dependent fields when boar changes
                                $set('reservation_client_id', null);
                                $set('boar_reservation_id', null);
                                $set('client_name', null);
                                $set('service_date', null);
                                $set('service_fee_type', null);
                                $set('service_fee_amount', null);
                                $set('service_status', null);

                                if (! $state) {
                                    return;
                                }

                                // Optionally auto-select client if there is only one
                                $clientIds = BoarReservation::query()
                                    ->where('boar_id', $state)
                                    ->pluck('user_id')
                                    ->unique();

                                if ($clientIds->count() === 1) {
                                    $set('reservation_client_id', $clientIds->first());
                                }
                            }),
                        Forms\Components\Select::make('reservation_client_id')
                            ->label('Client Name')
                            ->options(function (Forms\Get $get) {
                                $boarId = $get('boar_id');
                                if (! $boarId) {
                                    return [];
                                }

                                $options = BoarReservation::query()
                                    ->where('boar_id', $boarId)
                                    ->with('user:id,name')
                                    ->get()
                                    ->filter(fn ($r) => $r->user_id !== null)
                                    ->mapWithKeys(function ($reservation) {
                                        $label = $reservation->user?->name ?? 'Unknown';
                                        return [$reservation->user_id => $label];
                                    })
                                    ->toArray();

                                return $options;
                            })
                            ->disabled(fn (Forms\Get $get) => ! $get('boar_id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Reset reservation and dependent fields when client changes
                                $set('boar_reservation_id', null);
                                $set('service_date', null);
                                $set('service_fee_type', null);
                                $set('service_fee_amount', null);
                                $set('service_status', null);
                            }),
                        Forms\Components\Select::make('boar_reservation_id')
                            ->label('Reservation requested at')
                            ->options(function (Forms\Get $get) {
                                $boarId = $get('boar_id');
                                $clientId = $get('reservation_client_id');

                                if (! $boarId || ! $clientId) {
                                    return [];
                                }

                                return BoarReservation::query()
                                    ->where('boar_id', $boarId)
                                    ->where('user_id', $clientId)
                                    ->orderByDesc('service_date')
                                    ->get()
                                    ->mapWithKeys(function (BoarReservation $reservation) {
                                        $label = $reservation->created_at
                                            ? $reservation->created_at->format('F j, Y, g:i A')
                                            : 'Unknown date';
                                        return [$reservation->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->disabled(fn (Forms\Get $get) => ! $get('boar_id') || ! $get('reservation_client_id'))
                            ->live()
                            ->placeholder('Select reservation by requested date/time')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (! $state) {
                                    $set('service_date', null);
                                    $set('service_fee_type', null);
                                    $set('service_fee_amount', null);
                                    $set('service_status', null);
                                    return;
                                }

                                $reservation = BoarReservation::with('user')->find($state);
                                if (! $reservation) {
                                    return;
                                }

                                // Ensure service date on stud service exactly matches the reservation date (Y-m-d)
                                $set('service_date', $reservation->service_date
                                    ? Carbon::parse($reservation->service_date)->toDateString()
                                    : null);
                                $set('service_fee_type', $reservation->service_fee_type);
                                $set('service_fee_amount', $reservation->service_fee_amount);
                                $set('service_status', $reservation->service_status ?? 'pending');
                                $set('client_name', $reservation->user?->name);
                            }),
                        Forms\Components\DatePicker::make('service_date')
                            ->label('Service Date')
                            ->required(),
                        Forms\Components\Select::make('service_fee_type')
                            ->label('Service Fee Type')
                            ->required()
                            ->options([
                                'pig' => 'Pay with Pig (Offspring)',
                                'money' => 'Pay with Money',
                            ])
                            ->preload()
                            ->live(),
                        Forms\Components\TextInput::make('service_fee_amount')
                            ->label('Service Fee Amount')
                            ->required()
                            ->numeric()
                            ->step(1)
                            ->disabled(fn (Forms\Get $get) => empty($get('service_fee_type')))
                            ->prefix(fn (Forms\Get $get) => $get('service_fee_type') === 'money' ? '₱' : null)
                            ->suffix(fn (Forms\Get $get) => $get('service_fee_type') === 'pig' ? 'pig(s)' : null),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('service_status')
                                    ->label('Service Status')
                                    ->required()
                                    ->options([
                                        'pending' => 'Pending',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->preload(),
                                Forms\Components\Placeholder::make('reservation_payment_status_display')
                                    ->label('Payment Status')
                                    ->content(function (Forms\Get $get) {
                                        $reservationId = $get('boar_reservation_id');
                                        if (! $reservationId) {
                                            return '—';
                                        }

                                        $reservation = BoarReservation::find($reservationId);
                                        if (! $reservation) {
                                            return '—';
                                        }

                                        if ($reservation->service_fee_type !== 'money') {
                                            return 'Not applicable (Pay with Pig)';
                                        }

                                        return $reservation->payment_status
                                            ? ucfirst($reservation->payment_status)
                                            : '—';
                                    }),
                                Forms\Components\Placeholder::make('reservation_status_display')
                                    ->label('Reservation Status')
                                    ->content(function (Forms\Get $get) {
                                        $reservationId = $get('boar_reservation_id');
                                        if (! $reservationId) {
                                            return '—';
                                        }

                                        $reservation = BoarReservation::find($reservationId);
                                        if (! $reservation) {
                                            return '—';
                                        }

                                        return match ($reservation->reservation_status) {
                                            'pending_boar_raiser' => 'Pending boar raiser',
                                            'confirmed' => 'Confirmed (paid)',
                                            default => $reservation->reservation_status
                                                ? ucfirst(str_replace('_', ' ', $reservation->reservation_status))
                                                : '—',
                                        };
                                    }),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('boar.boar_name')
                    ->label('Boar Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime('F j, Y, g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_fee_type')
                    ->label('Service Fee Type')
                    ->formatStateUsing(fn(string $state) => ucfirst($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('service_fee_amount')
                    ->label('Service Fee Amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_status')
                    ->label('Service Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state) => ucfirst($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('boarReservation.payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '—')
                    ->color(fn (?string $state): string => match ($state) {
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('boarReservation.reservation_status')
                    ->label('Reservation Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending_boar_raiser' => 'Pending boar raiser',
                        'confirmed' => 'Confirmed (paid)',
                        default => $state ? ucfirst(str_replace('_', ' ', $state)) : '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'pending_boar_raiser' => 'info',
                        'accepted' => 'success',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Added')
                    ->dateTime('F j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Date Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordUrl(fn(StudService $record) => static::getUrl('edit', ['record' => $record]))
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'boar-raiser';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudServices::route('/'),
            'create' => Pages\CreateStudService::route('/create'),
            'edit' => Pages\EditStudService::route('/{record}/edit'),
        ];
    }
}
