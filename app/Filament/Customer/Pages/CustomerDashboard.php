<?php

namespace App\Filament\Customer\Pages;

use App\Models\Boar;
use App\Models\BoarRating;
use App\Models\BoarReservation;
use App\Models\User;
use App\Notifications\BoarReservationRequested;
use App\Filament\Customer\Resources\BoarReservationResource as CustomerBoarReservationResource;
use App\Filament\Resources\ReservationRequestResource as AdminReservationRequestResource;
use App\Rules\NoBadWords;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;

class CustomerDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-m-shopping-bag';

    protected static ?string $navigationLabel = 'MarketPlace';

    protected static ?string $title = '';

    protected static string $view = 'filament.customer.pages.customer-dashboard';

    public ?int $boarId = null;
    public ?string $boarName = null;
    public ?string $boarType = null;
    public ?string $breederName = null;
    public ?string $breederPhone = null;
    public ?string $breederAvatarUrl = null;
    public ?string $boarAddress = null;
    public ?string $boarImage = null;

    /** Default pricing from the selected boar (set when opening reservation modal). */
    public int $defaultPriceMoney = 0;
    public int $defaultDownpayment = 0;
    public int $defaultPayWithPigs = 1;

    public string $search = '';
    public string $type = '';
    /** Minimum star rating filter (1–5). Empty = all. */
    public string $stars = '';

    /** Reviews modal state */
    public bool $showReviewsModal = false;
    public ?int $reviewsModalBoarId = null;
    public string $reviewsModalBoarName = '';
    /** @var \Illuminate\Database\Eloquent\Collection<int, BoarRating> */
    public $reviewsModalReviews;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'customer';
    }

    #[Computed]
    public function boars()
    {
        $query = Boar::with('user')
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->whereHas('user', function ($query) {
                $query->where('role', 'boar-raiser');
            })
            ->marketplaceAvailable();

        if (!empty($this->search)) {
            $search = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('boar_name', 'like', $search)
                    ->orWhere('boar_type', 'like', $search)
                    ->orWhere('boar_type_other', 'like', $search)
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', $search);
                    });
            });
        }

        if (!empty($this->type)) {
            $query->where('boar_type', $this->type);
        }

        if ($this->stars !== '') {
            $bucket = (int) $this->stars;
            if ($bucket >= 1 && $bucket <= 5) {
                $min = (float) $bucket;
                $max = $bucket < 5 ? $bucket + 0.9 : 5.0;
                $query->having('ratings_avg_rating', '>=', $min)
                    ->having('ratings_avg_rating', '<=', $max);
            }
        }

        return $query->latest()->get();
    }

    public function openReviewsModal(int $boarId): void
    {
        $boar = Boar::find($boarId);
        if (!$boar) {
            return;
        }
        $this->reviewsModalBoarId = $boarId;
        $this->reviewsModalBoarName = $boar->boar_name;
        $this->reviewsModalReviews = BoarRating::query()
            ->where('boar_id', $boarId)
            ->with('customer:id,name')
            ->orderByDesc('created_at')
            ->get();
        $this->showReviewsModal = true;
    }

    public function closeReviewsModal(): void
    {
        $this->showReviewsModal = false;
        $this->reviewsModalBoarId = null;
        $this->reviewsModalBoarName = '';
        $this->reviewsModalReviews = collect();
    }

    #[Computed]
    public function overdueBirths()
    {
        return BoarReservation::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('expected_due_date')
            ->whereNull('birth_confirmed_at')
            ->whereDate('expected_due_date', '<=', now()->toDateString())
            ->with('boar')
            ->get();
    }

    public function requestStudServiceAction(): Action
    {
        return Action::make('requestStudService')
            ->label('Request Reservation')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->form([
                Hidden::make('boar_id')->default(fn() => $this->boarId),

                Wizard::make([
                    Wizard\Step::make('Boar Information')
                        ->description('Review selected boar')
                        ->schema([
                            \Filament\Forms\Components\Grid::make(['default' => 1, 'md' => 2])
                                ->schema([
                                    ViewField::make('selected_boar_info')
                                        ->view('filament.forms.components.selected-boar-info')
                                        ->viewData([
                                            'boarName' => $this->boarName,
                                            'boarType' => $this->boarType,
                                            'breederName' => $this->breederName,
                                            'breederPhone' => $this->breederPhone,
                                            'breederAvatarUrl' => $this->breederAvatarUrl,
                                            'boarAddress' => $this->boarAddress,
                                            'boarImage' => $this->boarImage,
                                        ])
                                        ->columnSpan(['default' => 1, 'md' => 1]),

                                    Section::make('Boar Information')
                                        ->schema([
                                            Placeholder::make('boar_name_display')
                                                ->label('Boar Name')
                                                ->content(fn() => $this->boarName ?? 'N/A'),
                                            Placeholder::make('boar_type_display')
                                                ->label('Boar Type')
                                                ->content(fn() => $this->boarType ?? 'N/A'),
                                            Placeholder::make('boar_location_display')
                                                ->label('Boar Location')
                                                ->content(fn() => $this->boarAddress ?? 'N/A'),
                                        ])
                                        ->columns(1)
                                        ->columnSpan(['default' => 1, 'md' => 1]),
                                ])
                                ->columns(['default' => 1, 'md' => 2]),
                        ]),
                    Wizard\Step::make('Service Details')
                        ->description('Set up your reservation request')
                        ->schema([
                            \Filament\Forms\Components\Grid::make(['default' => 1, 'md' => 3])
                                ->schema([
                                    DatePicker::make('service_date')
                                        ->label('Preferred Service Date')
                                        ->required()
                                        ->minDate(now())
                                        ->columnSpan(['default' => 1, 'md' => 1]),

                                    Radio::make('service_fee_type')
                                        ->label('Service Fee Type')
                                        ->options([
                                            'pig' => 'Pay with Pig (' . $this->defaultPayWithPigs . ' ' . ($this->defaultPayWithPigs === 1 ? 'pig' : 'pigs') . ')',
                                            'money' => 'Pay with Money (₱' . number_format($this->defaultPriceMoney) . ' full price)',
                                        ])
                                        ->required()
                                        ->inline()
                                        ->live()
                                        ->default('money')
                                        ->afterStateUpdated(function ($state, Set $set): void {
                                            $set('service_fee_amount', $state === 'pig' ? $this->defaultPayWithPigs : $this->defaultDownpayment);
                                        })
                                        ->columnSpan(['default' => 1, 'md' => 1]),

                                    TextInput::make('service_fee_amount')
                                        ->label(fn (Get $get) => $get('service_fee_type') === 'money' ? 'Downpayment 50% of the full price' : 'Service Fee Amount')
                                        ->required()
                                        ->default(fn () => $this->defaultDownpayment)
                                        ->disabled()
                                        ->dehydrated()
                                        ->prefix(fn(Get $get) => $get('service_fee_type') === 'money' ? '₱' : null)
                                        ->suffix(fn(Get $get) => $get('service_fee_type') === 'pig' ? 'pig(s)' : null)
                                        ->columnSpan(['default' => 1, 'md' => 1]),

                                    Placeholder::make('downpayment_info')
                                        ->label('')
                                        ->content(new HtmlString('<p class="text-sm text-gray-500 dark:text-gray-400">You will be notified once the boar owner approves your reservation. After approval, the QR code for the down payment will be displayed, allowing you to proceed with the payment.</p>'))
                                        ->visible(fn (Get $get) => $get('service_fee_type') === 'money')
                                        ->columnSpan(['default' => 1, 'md' => 3]),

                                    \Filament\Forms\Components\Grid::make(['default' => 1, 'md' => 3])
                                        ->schema([
                                            TextInput::make('contact_number')
                                                ->label('Your contact number')
                                                ->default(fn () => auth()->user()?->phone_number ?? '')
                                                ->placeholder('e.g. 09xxxxxxxxx')
                                                ->columnSpan(['default' => 1, 'md' => 2]),
                                            \Filament\Forms\Components\Actions::make([
                                                \Filament\Forms\Components\Actions\Action::make('saveContactNumber')
                                                    ->label('Update contact number')
                                                    ->icon('heroicon-o-check')
                                                    ->color('success')
                                                    ->action(function (Get $get) {
                                                        auth()->user()->update(['phone_number' => $get('contact_number')]);
                                                        Notification::make()
                                                            ->title('Contact number updated')
                                                            ->success()
                                                            ->send();
                                                    }),
                                            ])
                                            ->verticallyAlignEnd()
                                            ->columnSpan(['default' => 1, 'md' => 1]),
                                        ])
                                        ->columnSpan(['default' => 1, 'md' => 3]),
                                ]),

                            \Filament\Forms\Components\Grid::make(['default' => 1, 'md' => 2])
                                ->schema([
                                    FileUpload::make('female_pig_photo')
                                        ->label('Photo of Your Female Pig')
                                        ->image()
                                        ->imageEditor()
                                        ->directory('female-pig-photos')
                                        ->visibility('private')
                                        ->helperText('Upload a clear photo of the female pig you want to breed')
                                        ->columnSpan(['default' => 1, 'md' => 1]),

                                    Textarea::make('notes')
                                        ->label('Additional Notes')
                                        ->rows(3)
                                        ->placeholder('Any additional information or special requirements')
                                        ->rule(new NoBadWords())
                                        ->validationMessages([
                                            'notes' => 'The additional notes contain inappropriate language. Please use respectful language.',
                                        ])
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state === null || $state === '') {
                                                return;
                                            }

                                            $sanitized = NoBadWords::sanitize($state);

                                            if ($sanitized !== $state) {
                                                $set('notes', $sanitized);
                                            }
                                        })
                                        ->columnSpan(['default' => 1, 'md' => 1]),
                                ])
                                ->columns(['default' => 1, 'md' => 2]),
                        ]),
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button
                        type="submit"
                        size="sm"
                        color="success"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2"
                        x-data="{ loading: false }"
                        x-on:click="loading = true"
                    >
                        <span x-show="loading" class="inline-flex items-center gap-2" x-cloak>
                            <x-filament::loading-indicator class="h-4 w-4" />
                            <span>Submitting...</span>
                        </span>

                        <span x-show="!loading">
                            Submit Request
                        </span>
                    </x-filament::button>
                BLADE)))
            ])
            ->modalHeading('Request Reservation')
            ->modalWidth('4xl')
            ->modalSubmitActionLabel('Submit Request')
            ->modalCancelActionLabel('Cancel')
            ->modalFooterActions(fn(Action $action): array => [])
            ->action(function (array $data): void {
                $femalePigPhotoPath = null;
                if (isset($data['female_pig_photo']) && $data['female_pig_photo']) {
                    $femalePigPhotoPath = $data['female_pig_photo'];
                }

                $boarReservation = BoarReservation::create([
                    'boar_id' => $data['boar_id'],
                    'user_id' => auth()->id(),
                    'address' => auth()->user()->address,
                    'service_date' => $data['service_date'],
                    'service_fee_type' => $data['service_fee_type'],
                    'service_fee_amount' => $data['service_fee_amount'],
                    'female_pig_photo' => $femalePigPhotoPath,
                    'notes' => $data['notes'] ?? null,
                    'reservation_status' => 'pending',
                    'service_status' => 'pending',
                ]);

                $boarReservation->load(['boar', 'user']);
                $boarName = $boarReservation->boar->boar_name ?? 'Boar';
                $customerName = $boarReservation->user->name ?? 'Customer';

                // Notify admins (shows in bell icon on admin panel)
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Notification::make()
                        ->title('New reservation request')
                        ->body("{$customerName} requested a reservation for {$boarName}. Go to Reservation Requests to approve or reject.")
                        ->icon('heroicon-o-document-text')
                        ->sendToDatabase($admin);
                }

                Notification::make()
                    ->title('Reservation Request Submitted')
                    ->body('Your reservation request has been submitted successfully!')
                    ->success()
                    ->send();
            });
    }

    public function startRequest(int $boarId): void
    {
        if (! auth()->user()?->id_verified_at) {
            Notification::make()
                ->title('Identity verification required')
                ->body('Your account has not been verified yet. Please wait for an admin to review your ID, or re-upload your ID from your profile settings.')
                ->warning()
                ->send();

            return;
        }

        $boar = Boar::with('user')->findOrFail($boarId);

        $this->boarId = $boar->id;
        $this->boarName = $boar->boar_name;
        $this->boarType = ucfirst(str_replace('-', ' ', $boar->boar_type));
        $this->breederName = $boar->user->name;
        $this->breederPhone = $boar->user->phone_number ?? null;
        $user = $boar->user;
        $this->breederAvatarUrl = null;
        if (!empty($user->profile_picture)) {
            $this->breederAvatarUrl = $user->getFilamentAvatarUrl();
        }
        $this->boarAddress = $user->address;
        $this->boarImage = $boar->boar_picture
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($boar->boar_picture)
            : asset('img/default_pfp.svg');

        $this->defaultPriceMoney = (int) ($boar->default_price_money ?? 0);
        $this->defaultDownpayment = (int) ($boar->default_downpayment ?? 0);
        $this->defaultPayWithPigs = (int) ($boar->default_pay_with_pigs ?? 1);

        $this->dispatch('open-modal', id: 'requestStudService');
        // Fallback to action mount in case modal system differs
        $this->mountAction('requestStudService');
    }
}
