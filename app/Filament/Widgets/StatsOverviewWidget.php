<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total users', User::count())
                ->description('All registered users')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Active rooms', Room::where('status', 'active')->count())
                ->description('Live right now')
                ->icon('heroicon-o-musical-note')
                ->color('success'),

            Stat::make('Rooms today', Room::whereDate('created_at', today())->count())
                ->description('Created today')
                ->icon('heroicon-o-calendar-days')
                ->color('info'),

            Stat::make('Rooms this week', Room::where('created_at', '>=', now()->startOfWeek())->count())
                ->description('Since Monday')
                ->icon('heroicon-o-chart-bar')
                ->color('warning'),
        ];
    }
}
