<?php

namespace App\Filament\Resources\UnifiedNotificationResource\Pages;

use App\Filament\Resources\UnifiedNotificationResource;
use App\Models\Stock;
use App\Models\Invoice;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;

class ListUnifiedNotifications extends ListRecords
{
    protected static string $resource = UnifiedNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // Override to provide custom data
    public function getTableRecords(): Collection
    {
        $companyId = session('selected_company_id');
        $notifications = collect();

        // Get Stock Notifications
        $stocks = Stock::where('company_id', $companyId)
            ->with('product')
            ->where(function ($q) {
                $q->whereColumn('available_quantity', '<', 'minimum_stock')
                  ->orWhere('expiry_date', '<', now())
                  ->orWhereBetween('expiry_date', [now(), now()->addDays(30)]);
            })
            ->get();

        foreach ($stocks as $stock) {
            $notification = (object)[
                'id' => 'stock_' . $stock->stock_id,
                'type' => $this->getStockNotificationType($stock),
                'title' => $stock->product->name ?? 'Produk',
                'details' => $this->getStockDetails($stock),
                'priority' => $this->getStockPriority($stock),
                'amount' => null,
                'action_url' => route('filament.admin.resources.stocks.edit', $stock->stock_id),
                'category' => 'stock',
            ];
            $notifications->push($notification);
        }

        // Get Invoice Notifications
        $invoices = Invoice::where('company_id', $companyId)
            ->with('customer')
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->where('due_date', '<=', now()->addDays(7))
            ->get();

        foreach ($invoices as $invoice) {
            $daysUntilDue = now()->diffInDays($invoice->due_date, false);
            
            $notification = (object)[
                'id' => 'invoice_' . $invoice->invoice_id,
                'type' => $this->getInvoiceNotificationType($daysUntilDue),
                'title' => $invoice->customer->name . ' - ' . $invoice->invoice_number,
                'details' => $this->getInvoiceDetails($invoice, $daysUntilDue),
                'priority' => $this->getInvoicePriority($daysUntilDue),
                'amount' => $invoice->getRemainingAmount(),
                'action_url' => route('filament.admin.resources.invoices.view', $invoice->invoice_id),
                'category' => 'invoice',
            ];
            $notifications->push($notification);
        }

        // Sort by priority: URGENT > HIGH > NORMAL
        $priorityOrder = ['URGENT' => 0, 'HIGH' => 1, 'NORMAL' => 2];
        return $notifications->sortBy(function ($item) use ($priorityOrder) {
            return $priorityOrder[$item->priority] ?? 999;
        })->values();
    }

    protected function getStockNotificationType(Stock $stock): string
    {
        if ($stock->isExpired()) return 'stock_expired';
        if ($stock->isNearExpiry()) return 'stock_expiring';
        if ($stock->isBelowMinimum()) return 'stock_low';
        return 'stock_other';
    }

    protected function getStockDetails(Stock $stock): string
    {
        if ($stock->isExpired()) {
            return "🔴 Kadaluarsa: {$stock->expiry_date->format('d/m/Y')} | Stock: {$stock->available_quantity} {$stock->product->unit}";
        }
        if ($stock->isNearExpiry()) {
            $days = now()->diffInDays($stock->expiry_date);
            return "🟡 Kadaluarsa dalam {$days} hari | Stock: {$stock->available_quantity} {$stock->product->unit}";
        }
        if ($stock->isBelowMinimum()) {
            return "⚠️ Stock rendah: {$stock->available_quantity} / Min: {$stock->minimum_stock} {$stock->product->unit}";
        }
        return "Stock: {$stock->available_quantity}";
    }

    protected function getStockPriority(Stock $stock): string
    {
        if ($stock->isExpired()) return 'URGENT';
        if ($stock->isNearExpiry() || $stock->isBelowMinimum()) return 'HIGH';
        return 'NORMAL';
    }

    protected function getInvoiceNotificationType(int $daysUntilDue): string
    {
        if ($daysUntilDue < 0) return 'invoice_overdue';
        if ($daysUntilDue == 0) return 'invoice_due_today';
        return 'invoice_due_soon';
    }

    protected function getInvoiceDetails(Invoice $invoice, int $daysUntilDue): string
    {
        $dueDate = $invoice->due_date->format('d/m/Y');
        
        if ($daysUntilDue < 0) {
            $days = abs($daysUntilDue);
            return "🔴 Terlambat {$days} hari | Jatuh tempo: {$dueDate}";
        }
        if ($daysUntilDue == 0) {
            return "🟡 Jatuh tempo HARI INI: {$dueDate}";
        }
        if ($daysUntilDue <= 3) {
            return "🟠 Jatuh tempo dalam {$daysUntilDue} hari: {$dueDate}";
        }
        return "🟢 Jatuh tempo dalam {$daysUntilDue} hari: {$dueDate}";
    }

    protected function getInvoicePriority(int $daysUntilDue): string
    {
        if ($daysUntilDue < 0) return 'URGENT';
        if ($daysUntilDue <= 3) return 'HIGH';
        return 'NORMAL';
    }
}
