<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $companyId = session('selected_company_id');
        
        if (!$companyId) {
            return [];
        }

        return [
            // 📈 PIUTANG - Total Receivables
            Stat::make('📈 Total Piutang', 'Rp ' . number_format($this->getTotalPiutang($companyId), 0, ',', '.'))
                ->description('Belum dibayar oleh customer')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning')
                ->chart($this->getPiutangTrend($companyId))
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.receivables.index')),

            // 🔴 OVERDUE - Piutang Jatuh Tempo
            Stat::make('🔴 Piutang Jatuh Tempo', 'Rp ' . number_format($this->getOverduePiutang($companyId), 0, ',', '.'))
                ->description($this->getOverdueCount($companyId) . ' invoice terlambat → URGENT!')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.receivables.index')),

            // 💵 CASH IN - Pembayaran Bulan Ini
            Stat::make('💵 Pembayaran Diterima', 'Rp ' . number_format($this->getMonthlyPayment($companyId), 0, ',', '.'))
                ->description('Total cash in bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getPaymentTrend($companyId)),
        ];
    }
    
    public function getHeading(): ?string
    {
        return '💰 KEUANGAN - Cash Flow & Piutang';
    }

    private function getTotalPiutang($companyId): float
    {
        $unpaid = Invoice::where('company_id', $companyId)
            ->where('status', 'Unpaid')
            ->sum('grand_total') ?? 0;

        $partial = Invoice::where('company_id', $companyId)
            ->where('status', 'Partial')
            ->get()
            ->sum(function ($invoice) {
                $paid = $invoice->payments()->sum('amount');
                return $invoice->grand_total - $paid;
            });

        return $unpaid + $partial;
    }

    private function getOverduePiutang($companyId): float
    {
        return Invoice::where('company_id', $companyId)
            ->where('status', 'Overdue')
            ->sum('grand_total') ?? 0;
    }

    private function getOverdueCount($companyId): int
    {
        return Invoice::where('company_id', $companyId)
            ->where('status', 'Overdue')
            ->count();
    }

    private function getMonthlyPayment($companyId): float
    {
        return Payment::where('company_id', $companyId)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount') ?? 0;
    }

    private function getPiutangTrend($companyId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $piutang = Invoice::where('company_id', $companyId)
                ->whereIn('status', ['Unpaid', 'Partial', 'Overdue'])
                ->whereDate('created_at', '<=', $date)
                ->sum('grand_total') ?? 0;
            $data[] = round($piutang / 1000000, 2);
        }
        return $data;
    }

    private function getPaymentTrend($companyId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $payment = Payment::where('company_id', $companyId)
                ->whereDate('payment_date', $date)
                ->sum('amount') ?? 0;
            $data[] = round($payment / 1000000, 2);
        }
        return $data;
    }
}
