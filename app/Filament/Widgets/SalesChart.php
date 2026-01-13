<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\LineChartWidget;

class SalesChart extends LineChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan 30 Hari Terakhir';

    protected function getData(): array
    {
        $data = Sale::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $data->pluck('total'),
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($d) => date('d M', strtotime($d))),
        ];
    }

    protected int|string|array $columnSpan = 'full';
}
