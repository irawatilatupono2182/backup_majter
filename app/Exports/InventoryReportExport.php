<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function query()
    {
        return Stock::query()
            ->where('company_id', $this->companyId)
            ->with(['product'])
            ->orderBy('product_id');
    }

    public function headings(): array
    {
        return [
            'Produk',
            'SKU',
            'Batch',
            'Qty Total',
            'Qty Reserved',
            'Qty Tersedia',
            'Min Stock',
            'Harga Beli',
            'Total Value',
            'Kadaluarsa',
            'Lokasi',
            'Status',
        ];
    }

    public function map($stock): array
    {
        return [
            $stock->product->name,
            $stock->product->sku,
            $stock->batch_number ?? '-',
            $stock->quantity,
            $stock->reserved_quantity,
            $stock->available_quantity,
            $stock->minimum_stock,
            $stock->unit_cost,
            $stock->quantity * ($stock->unit_cost ?? 0),
            $stock->expiry_date ? $stock->expiry_date->format('d/m/Y') : '-',
            $stock->location ?? '-',
            $this->getStatusLabel($stock),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getStatusLabel($stock): string
    {
        if ($stock->isExpired()) {
            return 'Expired';
        }
        if ($stock->isNearExpiry()) {
            return 'Near Expiry';
        }
        if ($stock->isBelowMinimum()) {
            return 'Low Stock';
        }
        return 'Normal';
    }
}