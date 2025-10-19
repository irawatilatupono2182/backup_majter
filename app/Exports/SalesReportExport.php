<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function query()
    {
        return Invoice::query()
            ->where('company_id', $this->companyId)
            ->with(['customer', 'payments'])
            ->orderBy('invoice_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Tanggal Invoice',
            'Nomor Invoice',
            'Customer',
            'Subtotal',
            'PPN',
            'Total',
            'Terbayar',
            'Sisa',
            'Status',
            'Jatuh Tempo',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_date->format('d/m/Y'),
            $invoice->invoice_number,
            $invoice->customer->name,
            $invoice->subtotal_amount,
            $invoice->ppn_amount,
            $invoice->total_amount,
            $invoice->getTotalPaid(),
            $invoice->getRemainingAmount(),
            $this->getStatusLabel($invoice->status),
            $invoice->due_date->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getStatusLabel(string $status): string
    {
        if ($status === 'paid') {
            return 'Lunas';
        }
        if ($status === 'partial') {
            return 'Sebagian';
        }
        if ($status === 'overdue') {
            return 'Jatuh Tempo';
        }
        return 'Belum Lunas';
    }
}