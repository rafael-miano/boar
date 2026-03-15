<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlatformFeeSettlementResource\Pages;
use App\Models\BoarReservation;
use App\Models\PlatformFeeSettlement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlatformFeeSettlementResource extends Resource
{
    protected static ?string $model = PlatformFeeSettlement::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Platform Fee Settlements';

    protected static ?string $slug = 'platform-fee-settlements';

    public static function getLabel(): string
    {
        return 'Settlement';
    }

    public static function getPluralLabel(): string
    {
        return 'Platform Fee Settlements';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('boarRaiser.name')
                    ->label('Boar Raiser')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => '₱' . number_format((float) $state, 2))
                    ->weight('bold')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('F j, Y g:i A'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->formatStateUsing(fn ($state) => $state
                        ? \Carbon\Carbon::parse($state)->format('F j, Y g:i A')
                        : '—')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        \Filament\Infolists\Components\Grid::make(2)->schema([
                            Section::make('Boar Raiser')
                                ->schema([
                                    TextEntry::make('boarRaiser.name')->label('Name')->weight('bold'),
                                    TextEntry::make('boarRaiser.email')->label('Email')->copyable(),
                                    TextEntry::make('boarRaiser.phone_number')->label('Phone'),
                                    TextEntry::make('amount')
                                        ->label('Amount Submitted')
                                        ->formatStateUsing(fn ($state) => '₱' . number_format((float) $state, 2))
                                        ->weight('bold')
                                        ->color('warning'),
                                    TextEntry::make('submitted_at')
                                        ->label('Submitted At')
                                        ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('F j, Y g:i A')),
                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                        ->color(fn (string $state): string => match ($state) {
                                            'pending'  => 'warning',
                                            'verified' => 'success',
                                            'rejected' => 'danger',
                                            default    => 'gray',
                                        }),
                                    TextEntry::make('rejection_reason')
                                        ->label('Rejection Reason')
                                        ->placeholder('—')
                                        ->visible(fn ($record) => ! empty($record->rejection_reason)),
                                ])
                                ->columnSpan(1),

                            Section::make('Payment Receipt')
                                ->schema([
                                    ImageEntry::make('receipt_image')
                                        ->label('GCash Receipt')
                                        ->disk('public')
                                        ->height(280)
                                        ->defaultImageUrl(url('/img/no-image.svg')),
                                ])
                                ->columnSpan(1),
                        ]),
                    ])
                    ->modalWidth('4xl')
                    ->modalCancelAction(false),

                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verify this payment?')
                    ->modalDescription('Confirm that you have received the GCash payment. All unpaid platform fees for this boar raiser will be marked as paid.')
                    ->modalSubmitActionLabel('Yes, verify')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status'      => 'verified',
                            'verified_at' => now(),
                        ]);

                        // Stamp platform_fee_paid_at on all unpaid reservations for this boar raiser
                        BoarReservation::query()
                            ->whereHas('boar', fn ($q) => $q->where('user_id', $record->boar_raiser_id))
                            ->where('service_fee_type', 'money')
                            ->whereNotNull('platform_fee')
                            ->whereNull('platform_fee_paid_at')
                            ->where(function ($q) {
                                $q->where('reservation_status', 'confirmed')
                                    ->orWhere('service_status', 'completed');
                            })
                            ->update(['platform_fee_paid_at' => now()]);

                        // Notify boar raiser
                        FilamentNotification::make()
                            ->title('Platform fee payment verified!')
                            ->body('Your GCash payment receipt of ₱' . number_format((float) $record->amount, 2) . ' has been verified. Your platform fee balance has been cleared.')
                            ->icon('heroicon-o-check-badge')
                            ->sendToDatabase($record->boarRaiser);

                        FilamentNotification::make()
                            ->title('Payment verified')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject this receipt?')
                    ->modalDescription('The boar raiser will be notified and asked to re-submit a valid receipt.')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason (shown to boar raiser)')
                            ->placeholder('e.g. Receipt is not clear, wrong amount, payment not received...')
                            ->rows(3)
                            ->required(),
                    ])
                    ->modalSubmitActionLabel('Reject')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        FilamentNotification::make()
                            ->title('Platform fee receipt rejected')
                            ->body('Your GCash receipt was rejected. Reason: ' . $data['rejection_reason'] . '. Please submit a new receipt on the Pay Platform Fee page.')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->actions([
                                NotificationAction::make('resubmit')
                                    ->label('Go to settlement page')
                                    ->button()
                                    ->url(\App\Filament\Pages\Settlement::getUrl()),
                            ])
                            ->sendToDatabase($record->boarRaiser);

                        FilamentNotification::make()
                            ->title('Receipt rejected')
                            ->warning()
                            ->send();
                    }),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatformFeeSettlements::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
