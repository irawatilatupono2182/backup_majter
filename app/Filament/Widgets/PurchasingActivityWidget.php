<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PurchasingActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Aktivitas Purchasing';
    protected static ?int $sort = 5;
    protected static ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        $companyId = session('selected_company_id');

        return $table
            ->query(
                PurchaseOrder::where('company_id', $companyId)
                    ->with(['supplier'])
                    ->latest('order_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('order_date')
                    ->label('Tgl Order')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('expected_delivery')
                    ->label('Exp. Delivery')
                    ->date('d/m/Y')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Confirmed',
                        'info' => 'Partial',
                        'success' => 'Completed',
                        'danger' => 'Cancelled',
                    ]),
            ])
            ->paginated(false);
    }
}
