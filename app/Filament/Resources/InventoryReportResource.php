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
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'inventory-report';

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
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
                return $query->where('company_id', $companyId);
            })
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('batch_number')
                    ->label('Batch')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('quantity')
                    ->label('Qty Total')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()),

                TextColumn::make('reserved_quantity')
                    ->label('Qty Reserved')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()),

                TextColumn::make('available_quantity')
                    ->label('Qty Tersedia')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make())
                    ->color(function ($record) {
                        return $record->isBelowMinimum() ? 'danger' : 'success';
                    }),

                TextColumn::make('minimum_stock')
                    ->label('Min Stock')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit_cost')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_value')
                    ->label('Total Value')
                    ->getStateUsing(function ($record) {
                        return $record->quantity * ($record->unit_cost ?? 0);
                    })
                    ->money('IDR')
                    ->sortable(),

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
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

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
                            return $query->whereColumn('available_quantity', '<', 'minimum_stock');
                        }
                        if ($data['value'] === 'expired') {
                            return $query->where('expiry_date', '<', now());
                        }
                        if ($data['value'] === 'near_expiry') {
                            return $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
                        }
                        if ($data['value'] === 'normal') {
                            return $query->whereColumn('available_quantity', '>=', 'minimum_stock')
                                ->where(function ($q) {
                                    $q->whereNull('expiry_date')
                                      ->orWhere('expiry_date', '>', now()->addDays(30));
                                });
                        }
                        return $query;
                    }),

                SelectFilter::make('location')
                    ->options(function () {
                        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
                        return Stock::where('company_id', $companyId)
                            ->whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location')
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
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
        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
        return parent::getEloquentQuery()
            ->where('company_id', $companyId)
            ->with(['product']);
    }
}