<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSellingProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top 10 Produk Terlaris';
    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        $companyId = session('selected_company_id');

        return $table
            ->query(
                Product::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->withCount(['invoiceItems as total_sold' => function ($query) {
                        $query->selectRaw('SUM(qty)');
                    }])
                    ->having('total_sold', '>', 0)
                    ->orderByDesc('total_sold')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('product_code')
                    ->label('Kode')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_sold')
                    ->label('Total Terjual')
                    ->numeric()
                    ->sortable()
                    ->suffix(' unit'),

                TextColumn::make('base_price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
