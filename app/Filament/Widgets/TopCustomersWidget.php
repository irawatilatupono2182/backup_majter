<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopCustomersWidget extends BaseWidget
{
    protected static ?string $heading = 'Top 10 Customer';
    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        $companyId = session('selected_company_id');

        return $table
            ->query(
                Customer::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->select('customers.*')
                    ->selectSub(function ($query) use ($companyId) {
                        $query->from('invoices')
                            ->whereColumn('invoices.customer_id', 'customers.customer_id')
                            ->where('invoices.company_id', $companyId)
                            ->selectRaw('SUM(grand_total)');
                    }, 'total_revenue')
                    ->selectSub(function ($query) use ($companyId) {
                        $query->from('invoices')
                            ->whereColumn('invoices.customer_id', 'customers.customer_id')
                            ->where('invoices.company_id', $companyId)
                            ->selectRaw('COUNT(*)');
                    }, 'total_invoices')
                    ->havingRaw('total_revenue > 0')
                    ->orderByDesc('total_revenue')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('customer_code')
                    ->label('Kode')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('total_invoices')
                    ->label('Total Transaksi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('is_ppn')
                    ->label('PPN')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'PPN' : 'Non-PPN')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ]),
            ])
            ->paginated(false);
    }
}
