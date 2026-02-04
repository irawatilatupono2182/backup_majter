<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    public static function sendAllNotifications(): void
    {
        // Get all active companies
        $companies = \App\Models\Company::all();
        
        foreach ($companies as $company) {
            self::sendNotificationsForCompany($company->company_id);
        }
    }
    
    public static function sendNotificationsForCompany(string $companyId): void
    {
        self::sendLowStockNotificationsFor($companyId);
        self::sendExpiredStockNotificationsFor($companyId);
        self::sendOverdueInvoiceNotificationsFor($companyId);
        self::sendDueSoonInvoiceNotificationsFor($companyId);
        self::sendOverduePayableNotificationsFor($companyId);
        self::sendDueSoonPayableNotificationsFor($companyId);
    }
    
    private static function sendLowStockNotificationsFor(string $companyId): void
    {
        $lowStocks = Stock::where('company_id', $companyId)
            ->whereColumn('available_quantity', '<', 'minimum_stock')
            ->with('product')
            ->get();

        if ($lowStocks->isEmpty()) return;

        $users = User::whereHas('companies', function ($q) use ($companyId) {
            $q->where('user_company_roles.company_id', $companyId);
        })->get();

        foreach ($lowStocks as $stock) {
            foreach ($users as $user) {
                FilamentNotification::make()
                    ->warning()
                    ->title('Stock Rendah: ' . $stock->product->name)
                    ->body("Stock tersisa {$stock->available_quantity} {$stock->product->unit}. Minimum: {$stock->minimum_stock}")
                    ->icon('heroicon-o-exclamation-triangle')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.stocks.edit', $stock->stock_id))
                            ->label('Lihat Stock'),
                    ])
                    ->sendToDatabase($user);
            }
        }
    }

    private static function sendExpiredStockNotificationsFor(string $companyId): void
    {
        $expiredStocks = Stock::where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with('product')
            ->get();

        if ($expiredStocks->isEmpty()) return;

        $users = User::whereHas('companies', function ($q) use ($companyId) {
            $q->where('user_company_roles.company_id', $companyId);
        })->get();

        foreach ($expiredStocks as $stock) {
            foreach ($users as $user) {
                FilamentNotification::make()
                    ->danger()
                    ->title('Produk Kadaluarsa: ' . $stock->product->name)
                    ->body("Kadaluarsa sejak {$stock->expiry_date->format('d/m/Y')}")
                    ->icon('heroicon-o-x-circle')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.stocks.edit', $stock->stock_id))
                            ->label('Lihat Stock'),
                    ])
                    ->sendToDatabase($user);
            }
        }
    }

    private static function sendOverdueInvoiceNotificationsFor(string $companyId): void
    {
        $overdueInvoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->where('due_date', '<', now())
            ->with('customer')
            ->get();

        if ($overdueInvoices->isEmpty()) return;

        $users = User::whereHas('companies', function ($q) use ($companyId) {
            $q->where('user_company_roles.company_id', $companyId);
        })->get();

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date);

            foreach ($users as $user) {
                FilamentNotification::make()
                    ->danger()
                    ->title('Invoice Jatuh Tempo: ' . $invoice->invoice_number)
                    ->body("{$invoice->customer->name} - Terlambat {$daysOverdue} hari. Sisa: Rp " . number_format($invoice->getRemainingAmount(), 0, ',', '.'))
                    ->icon('heroicon-o-exclamation-circle')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.invoices.view', $invoice->invoice_id))
                            ->label('Lihat Invoice'),
                        \Filament\Notifications\Actions\Action::make('payment')
                            ->button()
                            ->url(route('filament.admin.resources.payments.create', ['invoice_id' => $invoice->invoice_id]))
                            ->label('Catat Pembayaran')
                            ->color('success'),
                    ])
                    ->sendToDatabase($user);
            }
        }
    }

    private static function sendDueSoonInvoiceNotificationsFor(string $companyId): void
    {
        $dueSoonInvoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->whereBetween('due_date', [now(), now()->addDays(3)])
            ->with('customer')
            ->get();

        if ($dueSoonInvoices->isEmpty()) return;

        $users = User::whereHas('companies', function ($q) use ($companyId) {
            $q->where('user_company_roles.company_id', $companyId);
        })->get();

        foreach ($dueSoonInvoices as $invoice) {
            $daysUntilDue = now()->diffInDays($invoice->due_date, false);

            foreach ($users as $user) {
                FilamentNotification::make()
                    ->warning()
                    ->title('Invoice akan Jatuh Tempo: ' . $invoice->invoice_number)
                    ->body("{$invoice->customer->name} - Jatuh tempo dalam {$daysUntilDue} hari. Sisa: Rp " . number_format($invoice->getRemainingAmount(), 0, ',', '.'))
                    ->icon('heroicon-o-clock')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.invoices.view', $invoice->invoice_id))
                            ->label('Lihat Invoice'),
                    ])
                    ->sendToDatabase($user);
            }
        }
    }

    private static function sendOverduePayableNotificationsFor(string $companyId): void
    {
        $overduePOs = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with('supplier')
            ->get();

        if ($overduePOs->isEmpty()) return;

        $users = User::whereHas('companies', function ($q) use ($companyId) {
            $q->where('user_company_roles.company_id', $companyId);
        })->get();

        foreach ($overduePOs as $po) {
            $daysOverdue = now()->diffInDays($po->due_date);

            foreach ($users as $user) {
                FilamentNotification::make()
                    ->danger()
                    ->title('Hutang Jatuh Tempo: ' . $po->po_number)
                    ->body("{$po->supplier->name} - Terlambat {$daysOverdue} hari. Sisa: Rp " . number_format($po->getRemainingAmount(), 0, ',', '.'))
                    ->icon('heroicon-o-exclamation-circle')
                    ->sendToDatabase($user);
            }
        }
    }

    private static function sendDueSoonPayableNotificationsFor(string $companyId): void
    {
        $dueSoonPOs = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays(3)])
            ->with('supplier')
            ->get();

        if ($dueSoonPOs->isEmpty()) return;

        $users = User::whereHas('companies', function ($q) use ($companyId) {
            $q->where('user_company_roles.company_id', $companyId);
        })->get();

        foreach ($dueSoonPOs as $po) {
            $daysUntilDue = now()->diffInDays($po->due_date, false);

            foreach ($users as $user) {
                FilamentNotification::make()
                    ->warning()
                    ->title('Hutang akan Jatuh Tempo: ' . $po->po_number)
                    ->body("{$po->supplier->name} - Jatuh tempo dalam {$daysUntilDue} hari. Sisa: Rp " . number_format($po->getRemainingAmount(), 0, ',', '.'))
                    ->icon('heroicon-o-clock')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.purchase-orders.view', $po->po_id))
                            ->label('Lihat PO'),
                    ])
                    ->sendToDatabase($user);
            }
        }
    }
}
