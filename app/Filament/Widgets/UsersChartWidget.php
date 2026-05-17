<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class UsersChartWidget extends LineChartWidget
{
    protected ?string $heading = 'New users (30 days)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $rows = User::query()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M j');
            $data[] = (int) ($rows[$date] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'New users',
                'data' => $data,
                'borderColor' => '#fb923c',
                'backgroundColor' => 'rgba(251,146,60,0.15)',
                'fill' => true,
                'tension' => 0.4,
            ]],
            'labels' => $labels,
        ];
    }
}
