<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $companyId = session('selected_company_id');

        return [
            // Revenue Today
            Stat::make('Revenue Hari Ini', 'Rp ' . number_format($this->getTodayRevenue($companyId), 0, ',', '.'))
                ->description('Total invoice hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($this->getLast7DaysRevenue($companyId))
                ->color('success'),

            // Pending Invoices
            Stat::make('Invoice Belum Lunas', $this->getPendingInvoicesCount($companyId))
                ->description('Unpaid & Partial')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(route('filament.admin.resources.invoices.index', ['tableFilters' => ['status' => ['values' => ['Unpaid', 'Partial']]]])),

            // Low Stock Items
            Stat::make('Produk Stok Rendah', $this->getLowStockCount($companyId))
                ->description('Di bawah minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->url(route('filament.admin.resources.stocks.index', ['tableFilters' => ['below_minimum' => true]])),

            // Active Customers
            Stat::make('Customer Aktif', Customer::where('company_id', $companyId)->where('is_active', true)->count())
                ->description('Total customer')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            // Pending PO
            Stat::make('PO Pending', PurchaseOrder::where('company_id', $companyId)->where('status', 'Pending')->count())
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning')
                ->url(route('filament.admin.resources.purchase-orders.index', ['tableFilters' => ['status' => ['values' => ['Pending']]]])),

            // Products Total
            Stat::make('Total Produk', Product::where('company_id', $companyId)->where('is_active', true)->count())
                ->description('Produk aktif')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
        ];
    }

    private function getTodayRevenue($companyId): float
    {
        return Invoice::where('company_id', $companyId)
            ->whereDate('invoice_date', today())
            ->whereIn('status', ['Paid', 'Partial'])
            ->sum('grand_total') ?? 0;
    }

    private function getLast7DaysRevenue($companyId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $revenue = Invoice::where('company_id', $companyId)
                ->whereDate('invoice_date', $date)
                ->whereIn('status', ['Paid', 'Partial'])
                ->sum('grand_total') ?? 0;
            $data[] = round($revenue / 1000000, 2); // In millions
        }
        return $data;
    }

    private function getPendingInvoicesCount($companyId): int
    {
        return Invoice::where('company_id', $companyId)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->count();
    }

    private function getLowStockCount($companyId): int
    {
        return Stock::where('company_id', $companyId)
            ->whereColumn('available_quantity', '<', 'minimum_stock')
            ->count();
    }
}
