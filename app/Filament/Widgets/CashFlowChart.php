<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CashFlowChart extends ChartWidget
{
    protected static ?string $heading = 'Trend Cash Flow';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '60s';
    
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $companyId = session('selected_company_id');
        
        // Payments received (cash in)
        $paymentsData = Trend::model(Payment::class)
            ->between(
                now()->subMonths(6),
                now()
            )
            ->perMonth()
            ->sum('amount');

        // Invoices created (expected revenue)
        $invoicesData = Trend::model(Invoice::class)
            ->between(
                now()->subMonths(6),
                now()
            )
            ->perMonth()
            ->sum('grand_total');

        return [
            'datasets' => [
                [
                    'label' => 'Pembayaran Diterima',
                    'data' => $paymentsData
                        ->filter(fn (TrendValue $value) => Invoice::where('company_id', $companyId)->exists())
                        ->map(fn (TrendValue $value) => round($value->aggregate / 1000000, 2)),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Invoice Dibuat',
                    'data' => $invoicesData
                        ->filter(fn (TrendValue $value) => Invoice::where('company_id', $companyId)->exists())
                        ->map(fn (TrendValue $value) => round($value->aggregate / 1000000, 2)),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $paymentsData->map(fn (TrendValue $value) => date('M Y', strtotime($value->date))),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => true,
            'aspectRatio' => 2,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value + " Jt"; }',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": Rp " + context.parsed.y + " Juta"; }',
                    ],
                ],
            ],
        ];
    }
}
