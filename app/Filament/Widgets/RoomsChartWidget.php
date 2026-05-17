<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class RoomsChartWidget extends LineChartWidget
{
    protected ?string $heading = 'Rooms created (30 days)';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $rows = Room::query()
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
                'label' => 'Rooms created',
                'data' => $data,
                'borderColor' => '#a78bfa',
                'backgroundColor' => 'rgba(167,139,250,0.15)',
                'fill' => true,
                'tension' => 0.4,
            ]],
            'labels' => $labels,
        ];
    }
}
