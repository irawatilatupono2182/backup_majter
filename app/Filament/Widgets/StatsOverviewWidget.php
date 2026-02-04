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
        
        if (!$companyId) {
            return [
                Stat::make('⚠️ Perhatian', 'Pilih Company')
                    ->description('Silakan pilih company terlebih dahulu')
                    ->color('warning'),
            ];
        }

        return [
            // 💰 KEUANGAN - Revenue Today
            Stat::make('💰 Penjualan Hari Ini', 'Rp ' . number_format($this->getTodayRevenue($companyId), 0, ',', '.'))
                ->description('Total invoice lunas hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($this->getLast7DaysRevenue($companyId))
                ->color('success')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.invoices.index')),

            // 📋 PIUTANG - Pending Invoices
            Stat::make('📋 Invoice Belum Dibayar', $this->getPendingInvoicesCount($companyId) . ' Invoice')
                ->description($this->getPendingInvoicesAmount($companyId))
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.receivables.index')),

            // ⚠️ STOCK ALERT - Low Stock Items
            Stat::make('⚠️ Stock Perlu Restock', $this->getLowStockCount($companyId) . ' Produk')
                ->description('Stock di bawah minimum → Segera order!')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.stocks.index')),

            // 👥 CUSTOMER - Active Customers
            Stat::make('👥 Total Customer', number_format(Customer::where('company_id', $companyId)->where('is_active', true)->count()))
                ->description('Customer aktif dalam sistem')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.customers.index')),

            // 🛒 PURCHASING - Pending PO
            Stat::make('🛒 PO Belum Dikonfirmasi', PurchaseOrder::where('company_id', $companyId)->where('status', 'Pending')->count() . ' Order')
                ->description('Menunggu konfirmasi supplier')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.purchase-orders.index')),

            // 📦 PRODUK - Products Total
            Stat::make('📦 Katalog Produk', number_format(Product::where('company_id', $companyId)->where('is_active', true)->count()))
                ->description('Total produk aktif')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.products.index')),
        ];
    }
    
    private function getPendingInvoicesAmount($companyId): string
    {
        $amount = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->sum('grand_total') ?? 0;
        
        return 'Total: Rp ' . number_format($amount, 0, ',', '.');
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
