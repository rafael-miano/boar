<?php

namespace App\Filament\Widgets;

use App\Models\BoarReservation;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

class RecentSuccessfulBirthsWidget extends Widget
{
    protected static ?string $heading = 'Recent Successful Births';

    protected static ?int $sort = 100;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    #[Computed]
    public function recentBirths()
    {
        return BoarReservation::query()
            ->whereHas('boar', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereNotNull('birth_confirmed_at')
            ->with(['boar', 'user'])
            ->latest('birth_confirmed_at')
            ->limit(10)
            ->get();
    }

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === 'boar-raiser';
    }

    protected function getView(): string
    {
        return 'filament.widgets.recent-successful-births-widget';
    }

    protected function getViewData(): array
    {
        return [
            'recentBirths' => $this->recentBirths,
        ];
    }
}
