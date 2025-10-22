<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AgingAnalysisChart extends ChartWidget
{
    protected static ?string $heading = 'Aging Analisis Piutang';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $companyId = session('selected_company_id');

        $agingData = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->select([
                DB::raw('DATEDIFF(NOW(), due_date) as days_overdue'),
                DB::raw('SUM(grand_total) as total')
            ])
            ->groupBy('days_overdue')
            ->get();

        // Bucket data: 0-30, 31-60, 61-90, 90+
        $buckets = [
            'Current (0-30)' => 0,
            'Aging 31-60' => 0,
            'Aging 61-90' => 0,
            'Overdue 90+' => 0,
        ];

        foreach ($agingData as $item) {
            $days = $item->days_overdue;
            if ($days <= 30) {
                $buckets['Current (0-30)'] += $item->total;
            } elseif ($days <= 60) {
                $buckets['Aging 31-60'] += $item->total;
            } elseif ($days <= 90) {
                $buckets['Aging 61-90'] += $item->total;
            } else {
                $buckets['Overdue 90+'] += $item->total;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nilai Piutang',
                    'data' => array_values($buckets),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(234, 179, 8, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(234, 179, 8)',
                        'rgb(249, 115, 22)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_keys($buckets),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "Rp " + context.parsed.y.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
        ];
    }
}
