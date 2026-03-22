<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Forms\Form;
use Illuminate\Support\HtmlString;

class EditProfile extends BaseEditProfile
{
    protected ?string $originalIdPhoto = null;

    public function mount(): void
    {
        parent::mount();
        $this->originalIdPhoto = $this->getUser()->id_photo;
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getProfileFormSchema());
    }

    protected function getProfileFormSchema(): array
    {
        $user = $this->getUser();

        return [
            Grid::make(['default' => 1, 'md' => 3])
                ->schema([
                    Section::make('Profile Photo')
                        ->schema([
                            FileUpload::make('profile_picture')
                                ->label('')
                                ->image()
                                ->avatar()
                                ->disk('public')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('1:1')
                                ->imageResizeTargetWidth('400')
                                ->imageResizeTargetHeight('400')
                                ->directory('profile-pictures')
                                ->visibility('public')
                                ->maxSize(2048)
                                ->helperText('Max 2MB. Square image recommended.')
                                ->extraAttributes(['class' => 'flex justify-center']),
                        ])
                        ->columnSpan(['default' => 1, 'md' => 1]),

                    Section::make('Personal Information')
                        ->schema([
                            $this->getNameFormComponent(),
                            $this->getEmailFormComponent(),
                            TextInput::make('phone_number')
                                ->label('Phone Number')
                                ->tel()
                                ->maxLength(255),
                            Textarea::make('address')
                                ->label('Address')
                                ->rows(3)
                                ->maxLength(65535),
                        ])
                        ->columnSpan(['default' => 1, 'md' => 1]),

                    Section::make('Change Password')
                        ->schema([
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ])
                        ->columnSpan(['default' => 1, 'md' => 1]),

                    Section::make('Identity Verification')
                        ->visible(fn () => auth()->user()?->role !== 'admin')
                        ->schema([
                            Placeholder::make('id_status_badge')
                                ->label('Current Status')
                                ->content(function () {
                                    $user = $this->getUser()->fresh();

                                    if ($user->id_verified_at) {
                                        return new HtmlString(
                                            '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">'
                                            . '✔ Verified on ' . \Carbon\Carbon::parse($user->id_verified_at)->format('F j, Y')
                                            . '</span>'
                                        );
                                    }

                                    if ($user->id_photo) {
                                        return new HtmlString(
                                            '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">'
                                            . '⏳ Pending Review — an admin will verify your ID shortly.'
                                            . '</span>'
                                        );
                                    }

                                    if ($user->id_rejection_reason) {
                                        return new HtmlString(
                                            '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">'
                                            . '✖ Rejected'
                                            . '</span>'
                                        );
                                    }

                                    return new HtmlString(
                                        '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">'
                                        . '— No ID uploaded'
                                        . '</span>'
                                    );
                                }),

                            Placeholder::make('id_rejection_notice')
                                ->label('')
                                ->content(function () use ($user) {
                                    return new HtmlString(
                                        '<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 p-4 text-sm text-red-800 dark:text-red-200">'
                                        . '<strong>Rejection reason:</strong> ' . e($user->id_rejection_reason)
                                        . '<br><span class="mt-1 block text-red-600 dark:text-red-400">Please upload a new valid government-issued ID below.</span>'
                                        . '</div>'
                                    );
                                })
                                ->visible(fn () => ! empty($user->id_rejection_reason) && ! $user->id_photo),

                            FileUpload::make('id_photo')
                                ->label(fn () => $this->getUser()->fresh()->id_verified_at ? 'Re-upload ID (will require re-verification)' : 'Upload Government-Issued ID')
                                ->image()
                                ->disk('public')
                                ->directory('id-photos')
                                ->visibility('public')
                                ->maxSize(5120)
                                ->helperText('Accepted: JPEG, PNG. Max 5MB. Make sure the ID is clearly readable.')
                                ->visible(fn () => ! $this->getUser()->fresh()->id_photo || $this->getUser()->fresh()->id_verified_at),
                        ])
                        ->columns(1)
                        ->columnSpan(['default' => 1, 'md' => 3]),
                ]),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $newPhoto = $data['id_photo'] ?? null;

        if ($newPhoto && $newPhoto !== $this->originalIdPhoto) {
            $data['id_verified_at']      = null;
            $data['id_rejection_reason'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $user = $this->getUser()->fresh();

        if ($user->id_photo && $user->id_photo !== $this->originalIdPhoto) {
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                FilamentNotification::make()
                    ->title('ID re-submitted for verification')
                    ->body($user->name . ' has uploaded a new government ID and is awaiting review.')
                    ->icon('heroicon-o-identification')
                    ->actions([
                        NotificationAction::make('review')
                            ->label('Review ID')
                            ->button()
                            ->url(\App\Filament\Resources\UserResource::getUrl('index', panel: 'admin')),
                    ])
                    ->sendToDatabase($admin);
            }
        }

        // Full page redirect so the top-bar avatar reloads with the new profile picture.
        $this->redirect(request()->header('Referer') ?: filament()->getUrl());
    }

    protected function getPasswordFormComponent(): \Filament\Forms\Components\TextInput
    {
        return \Filament\Forms\Components\TextInput::make('password')
            ->label('New Password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->nullable()
            ->dehydrated(fn ($state) => filled($state))
            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state));
    }

    protected function getPasswordConfirmationFormComponent(): \Filament\Forms\Components\TextInput
    {
        return \Filament\Forms\Components\TextInput::make('passwordConfirmation')
            ->label('Confirm Password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->nullable()
            ->same('password')
            ->dehydrated(false);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()
                ->schema($this->getProfileFormSchema())
                ->operation('edit')
                ->model($this->getUser())
                ->statePath('data')
                ->inlineLabel(false),
        ];
    }
}

