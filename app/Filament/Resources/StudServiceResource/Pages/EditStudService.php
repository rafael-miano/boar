<?php

namespace App\Filament\Resources\StudServiceResource\Pages;

use App\Filament\Resources\StudServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudService extends EditRecord
{
    protected static string $resource = StudServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
