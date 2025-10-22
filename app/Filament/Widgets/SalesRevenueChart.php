<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Penjualan (30 Hari Terakhir)';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $data = Trend::model(Invoice::class)
            ->between(
                now()->subDays(30),
                now()
            )
            ->perDay()
            ->sum('grand_total');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
