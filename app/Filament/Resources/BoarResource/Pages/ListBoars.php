<?php

namespace App\Filament\Resources\BoarResource\Pages;

use App\Filament\Resources\BoarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBoars extends ListRecords
{
    protected static string $resource = BoarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Boar Record')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        // Hide tabs entirely for admins; they see a flat list.
        if (auth()->user()?->role === 'admin') {
            return [];
        }

        return [
            'all' => Tab::make('All')
                ->badge(function () {
                    $query = static::getResource()::getModel()::query();
                    $user = auth()->user();
                    if ($user && $user->role === 'boar-raiser') {
                        $query->where('user_id', $user->id);
                    }
                    return $query->count();
                }),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('breeding_status', 'active')),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('breeding_status', 'inactive')),
            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }

}
