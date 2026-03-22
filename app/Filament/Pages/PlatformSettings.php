<?php

namespace App\Filament\Pages;

use App\Models\PlatformSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PlatformSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Platform settings';

    protected static ?string $title = 'Platform settings';

    protected static string $view = 'filament.pages.platform-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = PlatformSetting::get();
        $this->form->fill([
            'gcash_qr_image' => $settings->gcash_qr_image,
            'platform_fee_percentage' => $settings->platform_fee_percentage,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Platform fee')
                    ->description('Percentage of each confirmed money payment that the platform keeps. Boar raisers remit this amount to the platform.')
                    ->schema([
                        TextInput::make('platform_fee_percentage')
                            ->label('Platform fee (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->default(10)
                            ->required(),
                    ])
                    ->columnSpan(1),
                Section::make('Platform GCash QR')
                    ->description('Upload the platform GCash QR code. Boar raisers will use this to send the platform fee.')
                    ->schema([
                        FileUpload::make('gcash_qr_image')
                            ->label('GCash QR code')
                            ->image()
                            ->disk(\App\Support\StorageHelper::uploadDisk())
                            ->directory('platform-gcash-qr')
                            ->visibility('public')
                            ->maxSize(2048),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = PlatformSetting::get();
        $image = $data['gcash_qr_image'] ?? $settings->gcash_qr_image;
        if (is_array($image)) {
            $image = $image[0] ?? $settings->gcash_qr_image;
        }
        $settings->update([
            'platform_fee_percentage' => $data['platform_fee_percentage'],
            'gcash_qr_image' => $image,
        ]);
        Notification::make()
            ->title('Platform settings saved')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         \Filament\Actions\Action::make('save')
    //             ->label('Save settings')
    //             ->action('save'),
    //     ];
    // }
}
