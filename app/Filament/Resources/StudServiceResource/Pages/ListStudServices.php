<?php

namespace App\Filament\Resources\StudServiceResource\Pages;

use App\Filament\Resources\StudServiceResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListStudServices extends ListRecords
{
    protected static string $resource = StudServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Create Stud Service')
            ->icon('heroicon-o-plus')
            ,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn () => static::getResource()::getModel()::query()->count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('service_status', 'pending')),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('service_status', 'completed')),
        ];
    }
}
