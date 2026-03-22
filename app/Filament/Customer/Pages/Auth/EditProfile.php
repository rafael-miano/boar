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
    protected function afterSave(): void
    {
        // Full page redirect so the top-bar avatar reloads with the new profile picture.
        $this->redirect(request()->header('Referer') ?: filament()->getUrl());
    }

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
                            ->imageEditor()
                            ->orientImagesFromExif(true)
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('400')
                            ->disk(\App\Support\StorageHelper::uploadDisk())
                            ->directory('profile-pictures')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->helperText('Max 5MB.'),
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
