<?php

namespace App\Filament\Widgets;

use App\Models\Stock;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class InventoryAlertsWidget extends BaseWidget
{
    protected static ?string $heading = 'Inventory Alerts';
    protected int | string | array $columnSpan = ['full'];
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Stock::query()
                    ->where('company_id', auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null)
                    ->where(function (Builder $query) {
                        $query->whereColumn('available_quantity', '<', 'minimum_stock')
                            ->orWhere('expiry_date', '<', now())
                            ->orWhereBetween('expiry_date', [now(), now()->addDays(30)]);
                    })
                    ->with(['product'])
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('alert_type')
                    ->label('Alert Type')
                    ->getStateUsing(function ($record) {
                        if ($record->isExpired()) return 'Expired';
                        if ($record->isNearExpiry()) return 'Near Expiry';
                        if ($record->isBelowMinimum()) return 'Low Stock';
                        return 'Normal';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->isExpired()) return 'danger';
                        if ($record->isNearExpiry()) return 'warning';
                        if ($record->isBelowMinimum()) return 'danger';
                        return 'success';
                    }),

                TextColumn::make('available_quantity')
                    ->label('Available')
                    ->numeric(),

                TextColumn::make('minimum_stock')
                    ->label('Minimum')
                    ->numeric(),

                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date('d/m/Y')
                    ->placeholder('-'),
            ])
            ->paginated(false);
    }
}
