<?php

namespace App\Filament\Customer\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Forms\Form;

/**
 * Customer panel profile page. Uses the same form as the shared EditProfile
 * so customers can upload/update their profile image.
 */
class EditProfile extends BaseEditProfile
{
    // Form (including profile image upload) is inherited from BaseEditProfile.
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->hiddenLabel()
                    ->schema([
                        FileUpload::make('profile_picture')
                            ->label('Profile image')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->getUploadedFileUrlUsing(function (?string $file): ?string {
                                if (! $file) {
                                    return null;
                                }

                                $file = preg_replace('#^https?://[^/]+#', '', $file);
                                $file = preg_replace('#^/?storage/#', '', $file);
                                $file = ltrim($file, '/');

                                return route('media.public', ['path' => $file]);
                            })
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('400')
                            ->directory('profile-pictures')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Upload a photo for your profile. Max 2MB.'),
                    ])
                    ->columns(1)
                    ->extraAttributes(['class' => 'flex flex-col items-center md:items-start']),

                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
