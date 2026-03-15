<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    protected static ?string $navigationLabel = 'User Management';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('role', ['customer', 'boar-raiser']))
            ->columns([
                ImageColumn::make('profile_picture')
                    ->label('Picture')
                    ->circular()
                    ->size(40)
                    ->alignment(Alignment::Center)
                    ->defaultImageUrl(url('/img/no-profile-picture.svg')),
                Tables\Columns\TextColumn::make('name')
                    ->label('Full Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'boar-raiser' => 'warning',
                        'customer'    => 'info',
                        default       => 'gray',
                    }),
                IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->getStateUsing(fn ($record) => ! is_null($record->email_verified_at))
                    ->trueIcon('ri-verified-badge-fill')
                    ->falseIcon('phosphor-x-fill')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->alignment(Alignment::Center),
                Tables\Columns\TextColumn::make('id_status')
                    ->label('ID Status')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        if ($record->id_verified_at) return 'Verified';
                        if ($record->id_photo)       return 'Pending Review';
                        return 'No ID Uploaded';
                    })
                    ->color(function ($record): string {
                        if ($record->id_verified_at) return 'success';
                        if ($record->id_photo)       return 'warning';
                        return 'danger';
                    }),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'boar-raiser' => 'Boar Raiser',
                        'customer'    => 'Customer',
                    ]),
                Tables\Filters\SelectFilter::make('id_status')
                    ->label('ID Status')
                    ->options([
                        'verified' => 'Verified',
                        'pending'  => 'Pending Review',
                        'none'     => 'No ID Uploaded',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'verified' => $query->whereNotNull('id_verified_at'),
                            'pending'  => $query->whereNull('id_verified_at')->whereNotNull('id_photo'),
                            'none'     => $query->whereNull('id_photo'),
                            default    => $query,
                        };
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->infolist([
                            \Filament\Infolists\Components\Grid::make(2)->schema([
                            Section::make('Account Information')
                                ->schema([
                                    ImageEntry::make('profile_picture')
                                        ->label('Profile Picture')
                                        ->circular()
                                        ->size(80)
                                        ->defaultImageUrl(url('/img/no-profile-picture.svg')),
                                    TextEntry::make('name')->label('Full Name')->weight('bold'),
                                    TextEntry::make('email')->label('Email')->copyable(),
                                    TextEntry::make('role')->label('Role')->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'boar-raiser' => 'warning',
                                            'customer'    => 'info',
                                            default       => 'gray',
                                        }),
                                    TextEntry::make('phone_number')->label('Phone Number'),
                                    TextEntry::make('address')->label('Address'),
                                    TextEntry::make('email_verified_at')
                                        ->label('Email Verified At')
                                        ->formatStateUsing(fn ($state) => $state
                                            ? \Carbon\Carbon::parse($state)->format('F j, Y g:i A')
                                            : 'Not verified')
                                        ->placeholder('Not verified'),
                                ])
                                ->columns(2)
                                ->columnSpan(1),

                            Section::make('Identity Verification')
                                ->schema([
                                    ImageEntry::make('id_photo')
                                        ->label('Submitted ID Photo')
                                        ->disk('public')
                                        ->height(220)
                                        ->defaultImageUrl(url('/img/no-image.svg')),
                                    TextEntry::make('id_verified_at')
                                        ->label('Verified At')
                                        ->formatStateUsing(fn ($state) => $state
                                            ? \Carbon\Carbon::parse($state)->format('F j, Y g:i A')
                                            : 'Not yet verified')
                                        ->placeholder('Not yet verified'),
                                    TextEntry::make('id_rejection_reason')
                                        ->label('Last Rejection Reason')
                                        ->placeholder('—')
                                        ->visible(fn ($record) => ! empty($record->id_rejection_reason)),
                                ])
                                ->columns(1)
                                ->columnSpan(1),
                            ]),
                        ])
                        ->modalWidth('4xl')
                        ->modalCancelAction(false),

                    Tables\Actions\Action::make('verifyId')
                        ->label('Verify ID')
                        ->icon('heroicon-o-shield-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Verify this user\'s ID?')
                        ->modalDescription('Confirm that the submitted ID is valid. The user will be notified and will gain full access to the platform.')
                        ->modalSubmitActionLabel('Yes, verify')
                        ->visible(fn ($record) => $record->id_photo && ! $record->id_verified_at)
                        ->action(function ($record) {
                            $record->update([
                                'id_verified_at'      => now(),
                                'id_rejection_reason' => null,
                            ]);

                            FilamentNotification::make()
                                ->title('Your identity has been verified!')
                                ->body('Your government ID has been reviewed and approved. You now have full access to all features.')
                                ->icon('heroicon-o-shield-check')
                                ->sendToDatabase($record);

                            FilamentNotification::make()
                                ->title('ID verified successfully')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('rejectId')
                        ->label('Reject ID')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->modalHeading('Reject this user\'s ID?')
                        ->modalDescription('The user will be notified and asked to re-upload a valid ID.')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Reason (shown to user)')
                                ->placeholder('e.g. ID photo is blurry, ID is expired, ID type not accepted...')
                                ->rows(3)
                                ->required(),
                        ])
                        ->modalSubmitActionLabel('Reject')
                        ->visible(fn ($record) => $record->id_photo && ! $record->id_verified_at)
                        ->action(function ($record, array $data) {
                            $record->update([
                                'id_photo'            => null,
                                'id_verified_at'      => null,
                                'id_rejection_reason' => $data['rejection_reason'],
                            ]);

                            FilamentNotification::make()
                                ->title('ID verification failed')
                                ->body('Your ID was rejected. Reason: ' . $data['rejection_reason'] . '. Please log in and re-upload a valid government ID.')
                                ->icon('heroicon-o-x-circle')
                                ->color('danger')
                                ->sendToDatabase($record);

                            FilamentNotification::make()
                                ->title('ID rejected')
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\Action::make('revokeId')
                        ->label('Revoke Verification')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Revoke ID verification?')
                        ->modalDescription('This will remove the verified status from this user and require them to re-submit their ID.')
                        ->modalSubmitActionLabel('Yes, revoke')
                        ->visible(fn ($record) => (bool) $record->id_verified_at)
                        ->action(function ($record) {
                            $record->update([
                                'id_photo'       => null,
                                'id_verified_at' => null,
                            ]);

                            FilamentNotification::make()
                                ->title('ID verification revoked')
                                ->warning()
                                ->send();
                        }),
                ])
                    ->link()
                    ->label('Action')
                    ->tooltip('Actions'),
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Account Information')
                    ->schema([
                        ImageEntry::make('profile_picture')
                            ->label('Profile Picture')
                            ->circular()
                            ->size(100)
                            ->defaultImageUrl(url('/img/no-profile-picture.svg')),
                        TextEntry::make('name')->label('Full Name')->weight('bold')->size('lg'),
                        TextEntry::make('email')->label('Email')->copyable(),
                        TextEntry::make('role')->label('Role')->badge(),
                        TextEntry::make('phone_number')->label('Phone Number'),
                        TextEntry::make('address')->label('Address'),
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                Section::make('Identity Verification')
                    ->schema([
                        ImageEntry::make('id_photo')
                            ->label('Submitted ID Photo')
                            ->disk('public')
                            ->height(220)
                            ->defaultImageUrl(url('/img/no-image.svg')),
                        TextEntry::make('id_verified_at')
                            ->label('Verified At')
                            ->formatStateUsing(fn ($state) => $state
                                ? \Carbon\Carbon::parse($state)->format('F j, Y g:i A')
                                : 'Not yet verified')
                            ->placeholder('Not yet verified'),
                        TextEntry::make('id_rejection_reason')
                            ->label('Last Rejection Reason')
                            ->placeholder('—')
                            ->visible(fn ($record) => ! empty($record->id_rejection_reason)),
                    ])
                    ->columns(1)
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }
}
