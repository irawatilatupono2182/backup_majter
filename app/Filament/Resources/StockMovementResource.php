<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\Product;
use App\Models\StockMovement;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Mutasi Stok';
    protected static ?string $navigationGroup = '🏭 Inventori';
    
    protected static ?int $navigationSort = 2;

    public static function getNavigationTooltip(): ?string
    {
        return 'History pergerakan stock masuk dan keluar';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Stock Movement')
                    ->schema([
                        Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),

                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name', function (Builder $query) {
                                $companyId = session('selected_company_id');
                                return $query->where('company_id', $companyId)
                                    ->where('product_type', 'STOCK');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset quantity when product changes
                                $set('quantity', null);
                            }),

                        Select::make('movement_type')
                            ->label('Tipe Movement')
                            ->options([
                                'in' => 'Stock Masuk',
                                'out' => 'Stock Keluar',
                                'adjustment' => 'Adjustment',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('quantity')
                            ->label('Qty')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText(function ($get) {
                                $productId = $get('product_id');
                                $movementType = $get('movement_type');
                                
                                if (!$productId || $movementType !== 'out') {
                                    return null;
                                }
                                
                                $companyId = session('selected_company_id');
                                $totalStock = \App\Models\Stock::where('company_id', $companyId)
                                    ->where('product_id', $productId)
                                    ->sum('available_quantity');
                                
                                if ($totalStock <= 0) {
                                    return '⚠️ Stok tidak tersedia!';
                                }
                                
                                return "✅ Stok tersedia: {$totalStock} unit";
                            })
                            ->rules(function ($get) {
                                return [
                                    function ($attribute, $value, $fail) use ($get) {
                                        $movementType = $get('movement_type');
                                        $productId = $get('product_id');
                                        
                                        if ($movementType === 'out' && $productId && $value) {
                                            $companyId = session('selected_company_id');
                                            $totalStock = \App\Models\Stock::where('company_id', $companyId)
                                                ->where('product_id', $productId)
                                                ->sum('available_quantity');
                                            
                                            if ($value > $totalStock) {
                                                $kekurangan = $value - $totalStock;
                                                $fail("Stok tidak mencukupi! Stok tersedia: {$totalStock} unit. Kekurangan: {$kekurangan} unit.");
                                            }
                                        }
                                    }
                                ];
                            }),

                        TextInput::make('unit_cost')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp'),

                        Select::make('reference_type')
                            ->label('Tipe Referensi')
                            ->options([
                                'purchase_order' => 'Purchase Order',
                                'delivery_note' => 'Surat Jalan',
                                'adjustment' => 'Stock Adjustment',
                                'initial' => 'Stock Awal',
                                'manual' => 'Manual',
                            ]),

                        TextInput::make('batch_number')
                            ->label('Nomor Batch'),

                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kadaluarsa'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $companyId = session('selected_company_id');
                return $query->where('company_id', $companyId);
            })
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('movement_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'in') {
                            return 'success';
                        }
                        if ($state === 'out') {
                            return 'danger';
                        }
                        return 'warning';
                    })
                    ->formatStateUsing(function (string $state): string {
                        if ($state === 'in') {
                            return 'Masuk';
                        }
                        if ($state === 'out') {
                            return 'Keluar';
                        }
                        return 'Adjustment';
                    }),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit_cost')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('reference_type')
                    ->label('Referensi')
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === 'purchase_order') {
                            return 'Purchase Order';
                        }
                        if ($state === 'delivery_note') {
                            return 'Surat Jalan';
                        }
                        if ($state === 'adjustment') {
                            return 'Stock Adjustment';
                        }
                        if ($state === 'initial') {
                            return 'Stock Awal';
                        }
                        return 'Manual';
                    }),

                TextColumn::make('batch_number')
                    ->label('Batch')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->placeholder('-'),

                TextColumn::make('anomaly_status')
                    ->label('Status Data')
                    ->badge()
                    ->getStateUsing(function (StockMovement $record): string {
                        // Check 1: Product tidak ada
                        if (!$record->product) {
                            return 'orphaned_product';
                        }

                        // Check 2: Product bukan STOCK
                        if ($record->product->product_type !== 'STOCK') {
                            return 'not_stock_type';
                        }

                        // Check 3: Quantity negative
                        if ($record->quantity < 0) {
                            return 'negative_quantity';
                        }

                        // Check 4: Reference ID ada tapi reference type tidak
                        if ($record->reference_id && !$record->reference_type) {
                            return 'missing_reference_type';
                        }

                        // Check 5: Reference type tertentu tapi tidak ada reference_id
                        if (in_array($record->reference_type, ['purchase_order', 'delivery_note']) && !$record->reference_id) {
                            return 'missing_reference_id';
                        }

                        // Check 6: Movement IN tapi reference_type adalah delivery_note (harusnya PO)
                        if ($record->movement_type === 'in' && $record->reference_type === 'delivery_note') {
                            return 'wrong_reference_in';
                        }

                        // Check 7: Movement OUT tapi reference_type adalah purchase_order (harusnya SJ)
                        if ($record->movement_type === 'out' && $record->reference_type === 'purchase_order') {
                            return 'wrong_reference_out';
                        }

                        // Check 8: Batch number ada tapi tidak ada expiry_date
                        if ($record->batch_number && !$record->expiry_date) {
                            return 'batch_no_expiry';
                        }

                        // Check 9: Sudah kadaluarsa
                        if ($record->expiry_date && $record->expiry_date->isPast()) {
                            return 'expired';
                        }

                        // Check 10: Movement OUT tapi stock tidak cukup (cek stock saat ini)
                        if ($record->movement_type === 'out') {
                            $currentStock = \App\Models\Stock::where('company_id', $record->company_id)
                                ->where('product_id', $record->product_id)
                                ->first();
                            
                            if (!$currentStock) {
                                return 'no_stock_record';
                            }

                            if ($currentStock->available_quantity < 0) {
                                return 'negative_stock_result';
                            }
                        }

                        // Check 11: Reference dokumen tidak ditemukan
                        if ($record->reference_type === 'purchase_order' && $record->reference_id) {
                            $po = \App\Models\PurchaseOrder::find($record->reference_id);
                            if (!$po) {
                                return 'po_not_found';
                            }
                        }

                        if ($record->reference_type === 'delivery_note' && $record->reference_id) {
                            $sj = \App\Models\DeliveryNote::find($record->reference_id);
                            if (!$sj) {
                                return 'sj_not_found';
                            }
                        }

                        return 'normal';
                    })
                    ->color(function (string $state): string {
                        if (in_array($state, ['orphaned_product', 'negative_quantity', 'no_stock_record', 
                            'negative_stock_result', 'po_not_found', 'sj_not_found'])) {
                            return 'danger';
                        }
                        
                        if (in_array($state, ['not_stock_type', 'missing_reference_type', 'missing_reference_id',
                            'wrong_reference_in', 'wrong_reference_out', 'batch_no_expiry', 'expired'])) {
                            return 'warning';
                        }
                        
                        if ($state === 'normal') {
                            return 'success';
                        }
                        
                        return 'gray';
                    })
                    ->formatStateUsing(function (string $state): string {
                        $labels = [
                            'orphaned_product' => '🚨 Produk tidak ada',
                            'not_stock_type' => '⚠️ Produk bukan STOCK',
                            'negative_quantity' => '❌ Qty Negative',
                            'missing_reference_type' => '⚠️ Ref Type kosong',
                            'missing_reference_id' => '⚠️ Ref ID kosong',
                            'wrong_reference_in' => '⚠️ Ref IN salah',
                            'wrong_reference_out' => '⚠️ Ref OUT salah',
                            'batch_no_expiry' => '⚠️ Batch tanpa expired',
                            'expired' => '🕐 Kadaluarsa',
                            'no_stock_record' => '🚨 Stock record tidak ada',
                            'negative_stock_result' => '❌ Stock jadi negative',
                            'po_not_found' => '🚨 PO tidak ditemukan',
                            'sj_not_found' => '🚨 SJ tidak ditemukan',
                            'normal' => '✅ Normal',
                        ];
                        
                        return $labels[$state] ?? '❓ Unknown';
                    })
                    ->tooltip(function (string $state): ?string {
                        $tooltips = [
                            'orphaned_product' => 'Movement ini merujuk ke produk yang sudah tidak ada di database. Data ini perlu diperbaiki.',
                            'not_stock_type' => 'Produk ini bertipe CATALOG, seharusnya tidak ada stock movement. Periksa tipe produk.',
                            'negative_quantity' => 'Quantity movement tidak boleh negative. Data perlu dikoreksi.',
                            'missing_reference_type' => 'Reference ID diisi tapi Reference Type kosong. Lengkapi data referensi.',
                            'missing_reference_id' => 'Movement memiliki reference type PO/SJ tapi tidak ada reference ID. Lengkapi data.',
                            'wrong_reference_in' => 'Movement IN (masuk) seharusnya reference ke Purchase Order, bukan Surat Jalan.',
                            'wrong_reference_out' => 'Movement OUT (keluar) seharusnya reference ke Surat Jalan, bukan Purchase Order.',
                            'batch_no_expiry' => 'Produk memiliki batch number tapi tidak ada tanggal kadaluarsa. Tambahkan expiry date.',
                            'expired' => 'Produk sudah melewati tanggal kadaluarsa. Periksa stock fisik.',
                            'no_stock_record' => 'Movement OUT tapi tidak ada record stock untuk produk ini. Buat stock record terlebih dahulu.',
                            'negative_stock_result' => 'Movement OUT menyebabkan stock available menjadi negative. Periksa transaksi.',
                            'po_not_found' => 'Reference Purchase Order tidak ditemukan di database. Data referensi tidak valid.',
                            'sj_not_found' => 'Reference Surat Jalan tidak ditemukan di database. Data referensi tidak valid.',
                            'normal' => 'Data movement valid dan sesuai aturan bisnis.',
                        ];
                        
                        return $tooltips[$state] ?? null;
                    })
                    ->sortable(false)
                    ->searchable(false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('movement_type')
                    ->label('Tipe Movement')
                    ->options([
                        'in' => 'Stock Masuk',
                        'out' => 'Stock Keluar',
                        'adjustment' => 'Adjustment',
                    ]),

                SelectFilter::make('reference_type')
                    ->label('Tipe Referensi')
                    ->options([
                        'purchase_order' => 'Purchase Order',
                        'delivery_note' => 'Surat Jalan',
                        'adjustment' => 'Stock Adjustment',
                        'initial' => 'Stock Awal',
                        'manual' => 'Manual',
                    ]),

                SelectFilter::make('anomaly')
                    ->label('Status Anomali')
                    ->options([
                        'orphaned_product' => '🚨 Produk tidak ada',
                        'not_stock_type' => '⚠️ Produk bukan STOCK',
                        'negative_quantity' => '❌ Qty Negative',
                        'missing_reference_type' => '⚠️ Ref Type kosong',
                        'missing_reference_id' => '⚠️ Ref ID kosong',
                        'wrong_reference_in' => '⚠️ Ref IN salah',
                        'wrong_reference_out' => '⚠️ Ref OUT salah',
                        'batch_no_expiry' => '⚠️ Batch tanpa expired',
                        'expired' => '🕐 Kadaluarsa',
                        'no_stock_record' => '🚨 Stock record tidak ada',
                        'negative_stock_result' => '❌ Stock jadi negative',
                        'po_not_found' => '🚨 PO tidak ditemukan',
                        'sj_not_found' => '🚨 SJ tidak ditemukan',
                        'normal' => '✅ Normal',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        $anomalyType = $data['value'];

                        switch ($anomalyType) {
                            case 'orphaned_product':
                                return $query->whereDoesntHave('product');
                            
                            case 'not_stock_type':
                                return $query->whereHas('product', function ($q) {
                                    $q->where('product_type', '!=', 'STOCK');
                                });
                            
                            case 'negative_quantity':
                                return $query->where('quantity', '<', 0);
                            
                            case 'missing_reference_type':
                                return $query->whereNotNull('reference_id')
                                            ->whereNull('reference_type');
                            
                            case 'missing_reference_id':
                                return $query->whereIn('reference_type', ['purchase_order', 'delivery_note'])
                                            ->whereNull('reference_id');
                            
                            case 'wrong_reference_in':
                                return $query->where('movement_type', 'in')
                                            ->where('reference_type', 'delivery_note');
                            
                            case 'wrong_reference_out':
                                return $query->where('movement_type', 'out')
                                            ->where('reference_type', 'purchase_order');
                            
                            case 'batch_no_expiry':
                                return $query->whereNotNull('batch_number')
                                            ->whereNull('expiry_date');
                            
                            case 'expired':
                                return $query->where('expiry_date', '<', now())
                                            ->whereNotNull('expiry_date');
                            
                            case 'no_stock_record':
                                $companyId = session('selected_company_id');
                                return $query->where('movement_type', 'out')
                                    ->whereNotExists(function ($q) use ($companyId) {
                                        $q->select(\DB::raw(1))
                                          ->from('stocks')
                                          ->whereColumn('stocks.product_id', 'stock_movements.product_id')
                                          ->where('stocks.company_id', $companyId);
                                    });
                            
                            case 'negative_stock_result':
                                $companyId = session('selected_company_id');
                                return $query->where('movement_type', 'out')
                                    ->whereExists(function ($q) use ($companyId) {
                                        $q->select(\DB::raw(1))
                                          ->from('stocks')
                                          ->whereColumn('stocks.product_id', 'stock_movements.product_id')
                                          ->where('stocks.company_id', $companyId)
                                          ->where('stocks.available_quantity', '<', 0);
                                    });
                            
                            case 'po_not_found':
                                return $query->where('reference_type', 'purchase_order')
                                    ->whereNotNull('reference_id')
                                    ->whereNotExists(function ($q) {
                                        $q->select(\DB::raw(1))
                                          ->from('purchase_orders')
                                          ->whereColumn('purchase_orders.po_id', 'stock_movements.reference_id');
                                    });
                            
                            case 'sj_not_found':
                                return $query->where('reference_type', 'delivery_note')
                                    ->whereNotNull('reference_id')
                                    ->whereNotExists(function ($q) {
                                        $q->select(\DB::raw(1))
                                          ->from('delivery_notes')
                                          ->whereColumn('delivery_notes.delivery_note_id', 'stock_movements.reference_id');
                                    });
                            
                            case 'normal':
                                $companyId = session('selected_company_id');
                                return $query->whereHas('product', function ($q) {
                                    $q->where('product_type', 'STOCK');
                                })
                                ->where('quantity', '>=', 0)
                                ->where(function ($q) {
                                    $q->whereNull('reference_id')
                                      ->orWhere(function ($q2) {
                                          $q2->whereNotNull('reference_id')
                                             ->whereNotNull('reference_type');
                                      });
                                })
                                ->where(function ($q) {
                                    $q->whereNotIn('reference_type', ['purchase_order', 'delivery_note'])
                                      ->orWhere(function ($q2) {
                                          $q2->whereIn('reference_type', ['purchase_order', 'delivery_note'])
                                             ->whereNotNull('reference_id');
                                      });
                                })
                                ->where(function ($q) {
                                    $q->where('movement_type', '!=', 'in')
                                      ->orWhere('reference_type', '!=', 'delivery_note')
                                      ->orWhereNull('reference_type');
                                })
                                ->where(function ($q) {
                                    $q->where('movement_type', '!=', 'out')
                                      ->orWhere('reference_type', '!=', 'purchase_order')
                                      ->orWhereNull('reference_type');
                                })
                                ->where(function ($q) {
                                    $q->whereNull('batch_number')
                                      ->orWhereNotNull('expiry_date');
                                })
                                ->where(function ($q) {
                                    $q->whereNull('expiry_date')
                                      ->orWhere('expiry_date', '>=', now());
                                });
                            
                            default:
                                return $query;
                        }
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
            'edit' => Pages\EditStockMovement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        return parent::getEloquentQuery()
            ->where('company_id', $companyId);
    }
}