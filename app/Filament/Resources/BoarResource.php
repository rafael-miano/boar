<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoarResource\Pages;
use App\Models\Boar;
use App\Models\BoarReservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ImageColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\ActionsPosition;

class BoarResource extends Resource
{
    protected static ?string $model = Boar::class;

    protected static ?string $navigationIcon = 'phosphor-piggy-bank-fill';

    protected static ?string $navigationLabel = 'Boar Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Column 1: Boar Information + Boar Picture stacked
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Section::make('Boar Information')
                            ->description('Basic details about the boar')
                            ->schema([
                                Forms\Components\TextInput::make('boar_name')
                                    ->label('Boar Name')
                                    ->required()
                                    ->rules([
                                        'regex:/^[a-zA-Z0-9\s\'-]+$/',
                                    ])
                                    ->validationMessages([
                                        'regex' => 'The boar name can only contain letters, numbers, spaces, hyphens, and apostrophes. Special symbols are not allowed.',
                                    ])
                                    ->extraInputAttributes([
                                        'pattern' => "[a-zA-Z0-9\s'-]+",
                                        'oninput' => "this.value = this.value.replace(/[^a-zA-Z0-9\s'-]/g, '')",
                                    ]),
                                Forms\Components\Select::make('boar_type')
                                    ->label('Boar Type')
                                    ->options([
                                        'pietrain' => 'Pietrain',
                                        'large-white' => 'Large White',
                                        'duroc' => 'Duroc',
                                        'other' => 'Other',
                                    ])
                                    ->preload()
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('boar_type_other')
                                    ->label('Specify Boar Type')
                                    ->placeholder('Enter the specific boar type')
                                    ->visible(fn(Forms\Get $get) => $get('boar_type') === 'other')
                                    ->required(fn(Forms\Get $get) => $get('boar_type') === 'other'),
                                Forms\Components\DatePicker::make('breeding_maturity_date')
                                    ->label('Maturity Date')
                                    ->required(),
                                Forms\Components\Select::make('health_status')
                                    ->label('Health Status')
                                    ->options([
                                        'healthy' => 'Healthy',
                                        'sick' => 'Sick',
                                        'injured' => 'Injured',
                                        'other' => 'Other',
                                    ])
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('health_status_other')
                                    ->label('Specify Health Status')
                                    ->placeholder('Enter the specific health status')
                                    ->visible(fn(Forms\Get $get) => $get('health_status') === 'other')
                                    ->required(fn(Forms\Get $get) => $get('health_status') === 'other'),
                                Forms\Components\Select::make('breeding_status')
                                    ->label('Breeding Status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'other' => 'Other',
                                    ])
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('breeding_status_other')
                                    ->label('Specify Breeding Status')
                                    ->placeholder('Enter the specific breeding status')
                                    ->visible(fn(Forms\Get $get) => $get('breeding_status') === 'other')
                                    ->required(fn(Forms\Get $get) => $get('breeding_status') === 'other'),
                                Forms\Components\Toggle::make('is_published')
                                    ->label('Publish to Marketplace')
                                    ->helperText('Enable to make this boar visible to customers in the marketplace.')
                                    ->default(false),
                            ])
                            ->columns(1),

                        Forms\Components\Section::make('Boar Picture')
                            ->schema([
                                Forms\Components\FileUpload::make('boar_picture')
                                    ->hiddenLabel()
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->imageResizeTargetWidth('400')
                                    ->imageResizeTargetHeight('300')
                                    ->imageResizeMode('cover')
                                    ->directory('boar-pictures')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(8192) // 8MB
                                    ->helperText('Max file size: 8MB. Supported formats: JPEG, PNG'),
                            ])
                            ->columns(1),
                    ])
                    ->columnSpan(1),

                // Column 2: Default service fees (GCash QR grows independently)
                Forms\Components\Section::make('Default service fees')
                    ->description('Default price and payment options for reservations. Downpayment is auto-filled as half of the price.')
                    ->schema([
                        Forms\Components\TextInput::make('default_price_money')
                            ->label('Default price (money)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('₱')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $value = (int) ($state ?? 0);
                                $set('default_downpayment', (int) floor($value / 2));
                            })
                            ->helperText('Service fee when customer pays with money (₱).'),
                        Forms\Components\TextInput::make('default_downpayment')
                            ->label('Default downpayment')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('₱')
                            ->helperText('Half of default price. You can edit this if needed.'),
                        Forms\Components\TextInput::make('default_pay_with_pigs')
                            ->label('Default pay with pigs')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->integer()
                            ->helperText('Number of pigs when customer pays with pig(s).'),
                        Forms\Components\FileUpload::make('gcash_qr_image')
                            ->label('GCash QR code')
                            ->image()
                            ->imageEditor()
                            ->directory('gcash-qr')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->helperText('QR image used when customer pays with money.'),
                    ])
                    ->columns(1)
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('boar_picture')
                    ->label('Boar')
                    ->size(72)
                    ->square()
                    ->alignCenter()
                    ->extraAttributes(['class' => 'rounded-lg shadow-sm'])
                    ->defaultImageUrl(url('/img/no-image.svg')),
                Tables\Columns\TextColumn::make('boar_name')
                    ->label('Boar Name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('boar_type')
                    ->label('Boar Type')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('-', ' ', (string) $state)))
                    ->searchable(),
                Tables\Columns\TextColumn::make('default_price_money')
                    ->label('Full Price')
                    ->formatStateUsing(fn ($state) => '₱' . number_format((int) ($state ?? 0)))
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('breeding_maturity_date')
                    ->label('Maturity Date')
                    ->date(format: 'd-m-Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('health_status')
                    ->label('Health')
                    ->badge()
                    ->colors([
                        'success' => 'healthy',
                        'danger' => 'sick',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst((string) $state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('breeding_status')
                    ->label('Breeding')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst((string) $state))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Marketplace')
                    ->icon(function (Boar $record): string {
                        if (! $record->is_published) {
                            return 'heroicon-o-x-circle';
                        }
                        if ($record->marketplace_available_at && $record->marketplace_available_at->isFuture()) {
                            return 'heroicon-o-clock';
                        }
                        return 'heroicon-o-check-circle';
                    })
                    ->color(function (Boar $record): string {
                        if (! $record->is_published) {
                            return 'danger';
                        }
                        if ($record->marketplace_available_at && $record->marketplace_available_at->isFuture()) {
                            return 'warning';
                        }
                        return 'success';
                    })
                    ->tooltip(function (Boar $record): string {
                        if (! $record->is_published) {
                            return 'Hidden from marketplace';
                        }
                        if ($record->marketplace_available_at && $record->marketplace_available_at->isFuture()) {
                            return 'On cooldown — returns to marketplace on ' . $record->marketplace_available_at->format('F j, Y');
                        }
                        return 'Visible in marketplace';
                    }),
            ])
            ->filters([
                //
            ])
            ->actionsPosition(
                auth()->user()?->role === 'admin'
                    ? ActionsPosition::BeforeColumns
                    : ActionsPosition::AfterColumns
            )
            ->recordUrl(function (Boar $record) {
                $user = auth()->user();

                // For admins, disable row click entirely (use explicit actions instead).
                if ($user && $user->role === 'admin') {
                    return null;
                }

                // For others (e.g., boar raisers), keep row clickable unless archived.
                return $record->trashed()
                    ? null
                    : static::getUrl('edit', ['record' => $record]);
            })
            ->actions([
                // Admin action group - with label "Action"
                ActionGroup::make([
                    \Filament\Tables\Actions\Action::make('approvePublish')
                        ->label('Approve publish')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(function (Boar $record): bool {
                            $user = auth()->user();
                            return $user && $user->role === 'admin'
                                && $record->publish_status === 'pending_admin';
                        })
                        ->requiresConfirmation()
                        ->action(function (Boar $record) {
                            $record->update([
                                'is_published' => true,
                                'publish_status' => 'approved',
                            ]);
                        }),
                    \Filament\Tables\Actions\Action::make('rejectPublish')
                        ->label('Reject publish')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(function (Boar $record): bool {
                            $user = auth()->user();
                            return $user && $user->role === 'admin'
                                && $record->publish_status === 'pending_admin';
                        })
                        ->requiresConfirmation()
                        ->action(function (Boar $record) {
                            $record->update([
                                'is_published' => false,
                                'publish_status' => 'rejected',
                            ]);
                        }),
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn(Boar $record) => method_exists($record, 'trashed') ? $record->trashed() : false),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn(Boar $record) => method_exists($record, 'trashed') ? $record->trashed() : false),
                    RestoreAction::make()
                        ->visible(fn(Boar $record) => method_exists($record, 'trashed') ? $record->trashed() : false)
                        ->before(function (RestoreAction $action, Boar $record): void {
                            $record->archived_at = null;
                            $record->save();
                        })
                        ->successNotification(
                            fn(Boar $record) =>
                            Notification::make()
                                ->title('Boar restored')
                                ->body('The boar "' . $record->boar_name . '" has been restored.')
                                ->success()
                                ->color('success'),
                        ),
                ])
                    ->link()
                    ->label('Action')
                    ->tooltip('Actions')
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                // Boar-raiser action group - same actions but without label
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn(Boar $record) => method_exists($record, 'trashed') ? $record->trashed() : false),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn(Boar $record) => method_exists($record, 'trashed') ? $record->trashed() : false),
                    RestoreAction::make()
                        ->visible(fn(Boar $record) => method_exists($record, 'trashed') ? $record->trashed() : false)
                        ->before(function (RestoreAction $action, Boar $record): void {
                            $record->archived_at = null;
                            $record->save();
                        })
                        ->successNotification(
                            fn(Boar $record) =>
                            Notification::make()
                                ->title('Boar restored')
                                ->body('The boar "' . $record->boar_name . '" has been restored.')
                                ->success()
                                ->color('success'),
                        ),
                ])
                    ->link()
                    ->label('') // no text label for boar raisers
                    ->tooltip('Actions')
                    ->visible(fn () => auth()->user()?->role === 'boar-raiser'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoars::route('/'),
            'create' => Pages\CreateBoar::route('/create'),
            'edit' => Pages\EditBoar::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        $role = auth()->user()?->role;

        return $role === 'admin'
            ? 'Boar Approval'
            : 'Boar Management';
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'boar-raiser') {
            return false;
        }

        if (! $user->id_verified_at) {
            return false;
        }

        $unpaidTotal = BoarReservation::unpaidPlatformFeeTotalForBoarRaiser($user->id);

        // Block creating new boars if unpaid platform fees exceed ₱500.
        return $unpaidTotal <= 500;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['admin', 'boar-raiser'], true);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        // Only allow 'boar-raiser' role users to see their own boars
        if ($user && $user->role === 'boar-raiser') {
            $query->where('user_id', $user->id);
        }

        // Customers and others can have different filters or none, adjust as needed

        return $query;
    }
}
