<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryReportResource\Pages;
use App\Models\Stock;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class InventoryReportResource extends Resource
{
    protected static ?string $model = Stock::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Laporan Inventory';
    protected static ?string $navigationGroup = '📈 Laporan';
    

    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'inventory-report';

    public static function getNavigationTooltip(): ?string
    {
        return 'Laporan stok dan inventory';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        $companyId = session('selected_company_id');
        
        // Calculate summary statistics
        $totalStockIn = \App\Models\StockMovement::where('company_id', $companyId)
            ->where('movement_type', 'in')
            ->sum('quantity') ?? 0;
            
        $totalStockOut = \App\Models\StockMovement::where('company_id', $companyId)
            ->where('movement_type', 'out')
            ->sum('quantity') ?? 0;
        
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($companyId) {
                return $query->where('stocks.company_id', $companyId);
            })
            ->description(function () use ($totalStockIn, $totalStockOut, $companyId) {
                $totalStock = \App\Models\Stock::where('company_id', $companyId)->sum('quantity') ?? 0;
                $totalAvailable = \App\Models\Stock::where('company_id', $companyId)->sum('available_quantity') ?? 0;
                $totalUnitCost = \App\Models\Stock::where('company_id', $companyId)->sum('unit_cost') ?? 0;
                $totalValue = \App\Models\Stock::where('company_id', $companyId)
                    ->get()
                    ->sum(function ($stock) {
                        return $stock->quantity * ($stock->unit_cost ?? 0);
                    });
                
                return "📊 **SUMMARY INVENTORY** | " .
                       "Total Stok: **" . number_format($totalStock, 0) . "** unit | " .
                       "Total Tersedia: **" . number_format($totalAvailable, 0) . "** unit | " .
                       "Total Barang Masuk: **" . number_format($totalStockIn, 0) . "** unit | " .
                       "Total Barang Keluar: **" . number_format($totalStockOut, 0) . "** unit | " .
                       "Total Harga Satuan: **Rp " . number_format($totalUnitCost, 0) . "** | " .
                       "Total Nilai Inventory: **Rp " . number_format($totalValue, 0) . "**";
            })
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->sortable()
                    ->searchable()
                    ->description(function ($record) {
                        $batches = Stock::where('company_id', $record->company_id)
                            ->where('product_id', $record->product_id)
                            ->get()
                            ->pluck('batch_number')
                            ->filter()
                            ->unique()
                            ->values();
                        
                        if ($batches->isEmpty()) {
                            return 'Semua batch digabung';
                        }
                        
                        return 'Batches: ' . $batches->join(', ');
                    }),

                TextColumn::make('product.product_code')
                    ->label('Kode Produk')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('product.category')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('batch_number')
                    ->label('Batch')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('quantity')
                    ->label('Stok Total')
                    ->numeric()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Stok')
                            ->numeric()
                    )
                    ->description('Stok saat ini di sistem'),

                TextColumn::make('reserved_quantity')
                    ->label('Direservasi')
                    ->numeric()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Reserved')
                            ->numeric()
                    )
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('available_quantity')
                    ->label('Stok Tersedia')
                    ->numeric()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Tersedia')
                            ->numeric()
                    )
                    ->color(function ($record) {
                        return $record->isBelowMinimum() ? 'danger' : 'success';
                    })
                    ->description('Stok yang bisa digunakan'),

                TextColumn::make('stock_in_count')
                    ->label('Total Masuk')
                    ->getStateUsing(function ($record) {
                        return \App\Models\StockMovement::where('product_id', $record->product_id)
                            ->where('company_id', $record->company_id)
                            ->where('movement_type', 'in')
                            ->sum('quantity') ?? 0;
                    })
                    ->numeric()
                    ->color('success'),

                TextColumn::make('stock_out_count')
                    ->label('Total Keluar')
                    ->getStateUsing(function ($record) {
                        return \App\Models\StockMovement::where('product_id', $record->product_id)
                            ->where('company_id', $record->company_id)
                            ->where('movement_type', 'out')
                            ->sum('quantity') ?? 0;
                    })
                    ->numeric()
                    ->color('danger'),

                TextColumn::make('minimum_stock')
                    ->label('Min Stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('unit_cost')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_value')
                    ->label('Nilai Total')
                    ->getStateUsing(function ($record) {
                        return $record->quantity * ($record->unit_cost ?? 0);
                    })
                    ->money('IDR')
                    ->sortable()
                    ->description(fn ($record) => number_format($record->quantity, 0) . ' × Rp ' . number_format($record->unit_cost ?? 0, 0)),

                TextColumn::make('expiry_date')
                    ->label('Kadaluarsa')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->isExpired()) {
                            return 'danger';
                        }
                        if ($record->isNearExpiry()) {
                            return 'warning';
                        }
                        return null;
                    })
                    ->placeholder('-'),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        if ($record->isExpired()) {
                            return 'Expired';
                        }
                        if ($record->isNearExpiry()) {
                            return 'Near Expiry';
                        }
                        if ($record->isBelowMinimum()) {
                            return 'Low Stock';
                        }
                        return 'Normal';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->isExpired()) {
                            return 'danger';
                        }
                        if ($record->isNearExpiry()) {
                            return 'warning';
                        }
                        if ($record->isBelowMinimum()) {
                            return 'danger';
                        }
                        return 'success';
                    }),
            ])
            ->defaultSort('product.name', 'asc')
            ->emptyStateHeading('Tidak Ada Data Inventory')
            ->emptyStateDescription('Belum ada stok barang yang terdaftar. Silakan tambahkan stock melalui menu Stok Barang atau Stock Movement.')
            ->emptyStateIcon('heroicon-o-archive-box-x-mark')
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->options(function () {
                        $companyId = session('selected_company_id');
                        return \App\Models\Product::where('company_id', $companyId)
                            ->orderBy('name')
                            ->pluck('name', 'product_id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            return $query->where('stocks.product_id', $data['value']);
                        }
                        return $query;
                    }),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'normal' => 'Normal',
                        'low_stock' => 'Low Stock',
                        'near_expiry' => 'Near Expiry',
                        'expired' => 'Expired',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'low_stock') {
                            return $query->whereColumn('stocks.available_quantity', '<', 'stocks.minimum_stock');
                        }
                        if ($data['value'] === 'expired') {
                            return $query->where('stocks.expiry_date', '<', now());
                        }
                        if ($data['value'] === 'near_expiry') {
                            return $query->whereBetween('stocks.expiry_date', [now(), now()->addDays(30)]);
                        }
                        if ($data['value'] === 'normal') {
                            return $query->whereColumn('stocks.available_quantity', '>=', 'stocks.minimum_stock')
                                ->where(function ($q) {
                                    $q->whereNull('stocks.expiry_date')
                                      ->orWhere('stocks.expiry_date', '>', now()->addDays(30));
                                });
                        }
                        return $query;
                    }),

                SelectFilter::make('location')
                    ->options(function () {
                        $companyId = session('selected_company_id');
                        return Stock::where('company_id', $companyId)
                            ->whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location')
                            ->toArray();
                    })
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            return $query->where('stocks.location', $data['value']);
                        }
                        return $query;
                    }),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $companyId = session('selected_company_id');
                        return Excel::download(
                            new \App\Exports\InventoryReportExport($companyId),
                            'laporan-inventory-' . date('Y-m-d') . '.xlsx'
                        );
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryReports::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        // Use a subquery to group stocks by product
        return parent::getEloquentQuery()
            ->selectRaw('
                stocks.product_id,
                stocks.company_id,
                SUM(stocks.quantity) as quantity,
                SUM(stocks.reserved_quantity) as reserved_quantity,
                SUM(stocks.available_quantity) as available_quantity,
                AVG(stocks.minimum_stock) as minimum_stock,
                AVG(stocks.unit_cost) as unit_cost,
                MAX(stocks.expiry_date) as expiry_date,
                MAX(stocks.location) as location,
                MAX(stocks.created_at) as created_at,
                MIN(stocks.stock_id) as stock_id,
                MAX(stocks.batch_number) as batch_number,
                MAX(stocks.created_by) as created_by,
                MAX(stocks.notes) as notes,
                products.name as product_name,
                products.product_code as product_code,
                products.category as product_category
            ')
            ->from('stocks')
            ->where('stocks.company_id', $companyId)
            ->join('products', 'stocks.product_id', '=', 'products.product_id')
            ->with(['product'])
            ->groupBy('stocks.product_id', 'stocks.company_id', 'products.name', 'products.product_code', 'products.category');
    }
}