<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class InvoiceStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Invoice';
    protected static ?int $sort = 3;
    protected static string $color = 'info';
    
    protected static ?string $maxHeight = '280px';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $companyId = session('selected_company_id');

        $unpaid = Invoice::where('company_id', $companyId)->where('status', 'Unpaid')->count();
        $partial = Invoice::where('company_id', $companyId)->where('status', 'Partial')->count();
        $paid = Invoice::where('company_id', $companyId)->where('status', 'Paid')->count();
        $overdue = Invoice::where('company_id', $companyId)->where('status', 'Overdue')->count();
        $cancelled = Invoice::where('company_id', $companyId)->where('status', 'Cancelled')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Invoice',
                    'data' => [$unpaid, $partial, $paid, $overdue, $cancelled],
                    'backgroundColor' => [
                        'rgb(234, 179, 8)',   // Unpaid - yellow
                        'rgb(249, 115, 22)',  // Partial - orange
                        'rgb(34, 197, 94)',   // Paid - green
                        'rgb(239, 68, 68)',   // Overdue - red
                        'rgb(156, 163, 175)', // Cancelled - gray
                    ],
                ],
            ],
            'labels' => ['Unpaid', 'Partial', 'Paid', 'Overdue', 'Cancelled'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
