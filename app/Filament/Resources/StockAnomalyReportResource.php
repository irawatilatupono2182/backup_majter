<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAnomalyReportResource\Pages;
use App\Models\DeliveryNote;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockAnomalyReportResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Anomali Stok';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'stock-anomaly-report';

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
            ->heading('Laporan Anomali & Selisih Stok')
            ->description('⚠️ Menampilkan surat jalan yang sudah selesai. Filter untuk melihat yang belum tercatat di stock movement (lupa dicatat oleh gudang)')
            ->columns([
                TextColumn::make('delivery_date')
                    ->label('Tanggal Kirim')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('delivery_number')
                    ->label('No. Surat Jalan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description('Klik untuk copy')
                    ->getStateUsing(fn($record) => $record->sj_number),

                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $invoice = $record->invoice()->first();
                        return $invoice ? $invoice->invoice_number : '-';
                    })
                    ->url(function($record) {
                        $invoice = $record->invoice()->first();
                        return $invoice 
                            ? route('filament.admin.resources.invoices.view', $invoice)
                            : null;
                    })
                    ->color('primary'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->getStateUsing(function ($record) {
                        return $record->items()->count();
                    })
                    ->badge()
                    ->color('warning'),

                TextColumn::make('missing_movements')
                    ->label('Item Belum Tercatat')
                    ->getStateUsing(function ($record) {
                        $totalItems = $record->items()->count();
                        $recordedItems = $record->items()
                            ->whereHas('stockMovements')
                            ->count();
                        return $totalItems - $recordedItems;
                    })
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->icon(fn($state) => $state > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),

                TextColumn::make('total_quantity_not_recorded')
                    ->label('Total Qty Belum Tercatat')
                    ->getStateUsing(function ($record) {
                        // Hitung total quantity dari items yang belum punya stock movement
                        return $record->items()
                            ->whereDoesntHave('stockMovements')
                            ->sum('qty'); // Gunakan 'qty' bukan 'quantity'
                    })
                    ->numeric()
                    ->color('danger')
                    ->badge(),

                TextColumn::make('status_anomali')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        $missing = $record->items()
                            ->whereDoesntHave('stockMovements')
                            ->count();
                        
                        if ($missing === 0) {
                            return 'Lengkap';
                        } elseif ($missing === $record->items()->count()) {
                            return 'Belum Tercatat Sama Sekali';
                        } else {
                            return 'Sebagian Belum Tercatat';
                        }
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state === 'Lengkap') return 'success';
                        if ($state === 'Belum Tercatat Sama Sekali') return 'danger';
                        return 'warning';
                    }),

                TextColumn::make('details')
                    ->label('Detail Item Bermasalah')
                    ->getStateUsing(function ($record) {
                        $missingItems = $record->items()
                            ->with('product')
                            ->whereDoesntHave('stockMovements')
                            ->get();
                        
                        if ($missingItems->isEmpty()) {
                            return '✅ Semua item sudah tercatat';
                        }

                        $details = [];
                        foreach ($missingItems as $item) {
                            $productName = $item->product ? $item->product->name : 'Unknown';
                            // Format qty: hilangkan desimal jika bulat, atau tampilkan max 2 desimal
                            $qty = $item->qty;
                            $qtyFormatted = $qty == intval($qty) ? intval($qty) : number_format($qty, 2);
                            $details[] = "• {$productName} ({$qtyFormatted} unit)";
                        }
                        
                        return implode("\n", $details);
                    })
                    ->wrap()
                    ->html()
                    ->formatStateUsing(fn($state) => nl2br($state)),
            ])
            ->defaultSort('delivery_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Anomali')
                    ->options([
                        'all' => 'Semua Surat Jalan',
                        'complete' => 'Lengkap (Tidak Ada Anomali)',
                        'partial' => 'Sebagian Belum Tercatat',
                        'all_missing' => 'Belum Tercatat Sama Sekali',
                    ])
                    ->default('all_missing') // Default tampilkan yang bermasalah
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value']) || $data['value'] === 'all') {
                            return $query;
                        }

                        if ($data['value'] === 'complete') {
                            // Surat jalan yang semua itemnya sudah tercatat
                            return $query->whereDoesntHave('items', function ($q) {
                                $q->whereDoesntHave('stockMovements');
                            });
                        } elseif ($data['value'] === 'all_missing') {
                            // Surat jalan yang SEMUA itemnya belum tercatat
                            return $query->whereHas('items')
                                ->whereDoesntHave('items', function ($q) {
                                    $q->whereHas('stockMovements');
                                });
                        } elseif ($data['value'] === 'partial') {
                            // Surat jalan yang sebagian itemnya belum tercatat
                            return $query->whereHas('items', function ($q) {
                                    $q->whereDoesntHave('stockMovements');
                                })
                                ->whereHas('items', function ($q) {
                                    $q->whereHas('stockMovements');
                                });
                        }

                        return $query;
                    }),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading('Tidak Ada Anomali Stok')
            ->emptyStateDescription('Semua surat jalan sudah tercatat dengan baik di stock movement. ✅')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAnomalyReports::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->where('company_id', $companyId)
            ->whereIn('status', ['Sent', 'Completed']) // Yang sudah dikirim atau selesai
            ->with(['customer', 'items.product', 'items.stockMovements']);
            // Tidak filter anomali di sini, biar user bisa lihat semua dan filter sendiri
    }

    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        
        // Hitung jumlah delivery notes yang punya item belum tercatat
        $count = DeliveryNote::where('company_id', $companyId)
            ->whereIn('status', ['Sent', 'Completed'])
            ->whereHas('items', function ($query) {
                $query->whereDoesntHave('stockMovements');
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
