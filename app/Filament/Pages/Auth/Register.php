<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class Register extends BaseRegister
{
    protected ?string $maxWidth = '4xl';

    protected function getEmailFormComponent(): TextInput
    {
        $emailLabel = 'email address';

        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->validationAttribute($emailLabel)
            ->rule(static function () use ($emailLabel) {
                return static function (string $attribute, mixed $value, \Closure $fail) use ($emailLabel): void {
                    $email = trim(mb_strtolower((string) $value));

                    $existingUser = User::query()
                        ->where('email', $email)
                        ->first();

                    if (! $existingUser) {
                        return;
                    }

                    if (method_exists($existingUser, 'hasVerifiedEmail') && ! $existingUser->hasVerifiedEmail()) {
                        $fail('This email is already registered. Please check your email to verify your Boar Sync account.');

                        return;
                    }

                    $fail(__('validation.unique', ['attribute' => $emailLabel]));
                };
            });
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make()
                    ->extraAttributes(['class' => 'flex flex-col gap-6'])
                    ->schema([

                        Step::make('Personal Info')->schema([
                            Grid::make([
                                'default' => 4,
                                1 => 1,
                                2 => 3,
                            ])
                                ->schema([
                                    Group::make()
                                        ->schema([
                                            FileUpload::make('profile_picture')
                                                ->label('Upload Profile Picture')
                                                ->helperText('Maximum size: 2MB')
                                                ->avatar()
                                                ->image()
                                                ->imageEditor()
                                                ->imagePreviewHeight('150')
                                                ->disk('public')
                                                ->directory('profile-pictures')
                                                ->visibility('public')
                                                ->extraAttributes(['class' => 'block mx-auto']),
                                        ])
                                        ->columnSpan(1),

                                    Group::make()
                                        ->schema([
                                            $this->getNameFormComponent(),
                                            $this->getEmailFormComponent(),
                                            $this->getRoleFormComponent(),
                                        ])
                                        ->columnSpan(3),
                                ]),
                        ]),

                        Step::make('Contact Details')->schema([
                            Grid::make(4)
                                ->schema([
                                    Group::make()->columnSpan(1),

                                    Group::make()
                                        ->columnSpan(2)
                                        ->schema([
                                            PhoneInput::make('phone_number')
                                                ->label('Phone Number')
                                                ->defaultCountry('PH')
                                                ->separateDialCode(true)
                                                ->validateFor(
                                                    country: 'PH',
                                                    type: \libphonenumber\PhoneNumberType::MOBILE,
                                                    lenient: false
                                                )
                                                ->placeholder('912345678')
                                                ->helperText('Enter the remaining 10 digits after "+63 ". Example: 9123456789')
                                                ->required(),
                                            Textarea::make('address')
                                                ->label('Address')
                                                ->required(),
                                        ]),

                                    Group::make()->columnSpan(1),
                                ]),
                        ]),


                        Step::make('Password')->schema([
                            Grid::make(4)
                                ->schema([
                                    Group::make()->columnSpan(1),

                                    Group::make()
                                        ->columnSpan(2)
                                        ->schema([
                                            $this->getPasswordFormComponent(),
                                            $this->getPasswordConfirmationFormComponent(),
                                        ]),

                                    Group::make()->columnSpan(1),
                                ]),
                        ]),

                        Step::make('ID Verification')
                            ->description('Upload a valid ID')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Group::make()->columnSpan(1),

                                        Group::make()
                                            ->columnSpan(2)
                                            ->schema([
                                                \Filament\Forms\Components\Actions::make([
                                                    \Filament\Forms\Components\Actions\Action::make('viewValidIds')
                                                        ->label('What IDs are accepted?')
                                                        ->icon('heroicon-o-identification')
                                                        ->link()
                                                        ->color('primary')
                                                        ->modalHeading('Accepted Government-Issued IDs')
                                                        ->modalContent(new \Illuminate\Support\HtmlString(<<<'HTML'
                                                            <div class="rounded-lg border border-blue-200 bg-blue-50 dark:bg-blue-950 dark:border-blue-800 p-4 mb-4 text-sm text-blue-800 dark:text-blue-200">
                                                                <strong>Why we need this:</strong> To keep the platform safe, all accounts must be verified using a valid government-issued ID.
                                                                Your ID will be reviewed by an admin. You can still log in, but access to key features will be unlocked after verification.
                                                            </div>
                                                            <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300 px-1 pb-2">
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> PhilSys ID (Philippine Identification System Card)</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> Driver's License</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> Passport</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> UMID (Unified Multi-Purpose ID)</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> Voter's ID / COMELEC ID</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> SSS / GSIS Card</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> PRC ID (Professional Regulation Commission)</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> Postal ID</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> Senior Citizen ID</li>
                                                                <li class="flex items-start gap-2"><span class="text-primary-500 mt-0.5">✔</span> PWD ID</li>
                                                            </ul>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 px-1 pb-2">Make sure the ID is clearly readable and not expired.</p>
                                                        HTML))
                                                        ->modalSubmitAction(false)
                                                        ->modalCancelActionLabel('Close'),
                                                ]),
                                                FileUpload::make('id_photo')
                                                    ->label('Government-Issued ID Photo')
                                                    ->helperText('Accepted: JPEG, PNG. Max 5MB. Make sure the ID is clearly readable.')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('id-photos')
                                                    ->visibility('public')
                                                    ->maxSize(5120)
                                                    ->required(),
                                            ]),

                                        Group::make()->columnSpan(1),
                                    ]),
                            ]),



                    ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button type="submit" size="sm" wire:submit="register">
                            Register
                        </x-filament::button>
                    BLADE))),
            ])
            ->statePath('data');
    }

    protected function getRoleFormComponent(): Radio
    {
        return Radio::make('role')
            ->label('Role')
            ->options([
                'boar-raiser' => 'Boar Raiser',
                'customer' => 'Regular User',
            ])
            ->default('boar-raiser')
            ->inline()
            ->required();
    }

    protected ?User $registeredUser = null;

    protected function handleRegistration(array $data): User
    {
        $this->registeredUser = parent::handleRegistration($data);

        return $this->registeredUser;
    }

    protected function afterRegister(): void
    {
        $newUser = $this->registeredUser;

        if (! $newUser) {
            return;
        }

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            FilamentNotification::make()
                ->title('New user registered — ID verification needed')
                ->body(
                    ($newUser->name ?? 'A new user') . ' registered as ' .
                    ($newUser->role === 'boar-raiser' ? 'Boar Raiser' : 'Customer') .
                    ' and has submitted an ID for review.'
                )
                ->icon('heroicon-o-identification')
                ->actions([
                    NotificationAction::make('review')
                        ->label('Review ID')
                        ->button()
                        ->url(\App\Filament\Resources\UserResource::getUrl('index')),
                ])
                ->sendToDatabase($admin);
        }
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
