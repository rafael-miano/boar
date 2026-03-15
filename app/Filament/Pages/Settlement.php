<?php

namespace App\Filament\Pages;

use App\Models\BoarReservation;
use App\Models\PlatformFeeSettlement;
use App\Models\PlatformSetting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;

class Settlement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pay Platform Fee';

    protected static ?string $title = 'Settlement - Pay Platform Fee';

    protected static string $view = 'filament.pages.settlement';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'boar-raiser';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('receipt_image')
                    ->label('GCash Payment Receipt')
                    ->validationAttribute('GCash Payment Receipt')
                    ->helperText('Upload a screenshot of your GCash payment confirmation. Accepted: JPEG, PNG. Max 5MB.')
                    ->image()
                    ->disk('public')
                    ->directory('platform-fee-receipts')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submitReceipt(): void
    {
        $data = $this->form->getState();

        PlatformFeeSettlement::create([
            'boar_raiser_id' => auth()->id(),
            'amount'         => $this->totalPlatformFeeOwed,
            'receipt_image'  => $data['receipt_image'],
            'status'         => 'pending',
            'submitted_at'   => now(),
        ]);

        // Notify all admins
        $boarRaiserName = auth()->user()->name;
        $amount         = number_format($this->totalPlatformFeeOwed, 2);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            FilamentNotification::make()
                ->title('Platform fee receipt submitted')
                ->body("{$boarRaiserName} submitted a GCash receipt for ₱{$amount} in platform fees. Please review and verify.")
                ->icon('heroicon-o-banknotes')
                ->actions([
                    NotificationAction::make('review')
                        ->label('Review settlement')
                        ->button()
                        ->url(url('/admin/platform-fee-settlements')),
                ])
                ->sendToDatabase($admin);
        }

        $this->form->fill();

        FilamentNotification::make()
            ->title('Receipt submitted')
            ->body('Your payment receipt has been submitted. The admin will review and confirm your payment shortly.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    #[Computed]
    public function totalPlatformFeeOwed(): float
    {
        return BoarReservation::unpaidPlatformFeeTotalForBoarRaiser(auth()->id());
    }

    #[Computed]
    public function platformSettings(): PlatformSetting
    {
        return PlatformSetting::get();
    }

    #[Computed]
    public function pendingSettlement(): ?PlatformFeeSettlement
    {
        return PlatformFeeSettlement::where('boar_raiser_id', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    #[Computed]
    public function lastRejectedSettlement(): ?PlatformFeeSettlement
    {
        return PlatformFeeSettlement::where('boar_raiser_id', auth()->id())
            ->where('status', 'rejected')
            ->latest()
            ->first();
    }
}
