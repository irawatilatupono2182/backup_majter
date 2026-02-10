<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Master Barang/Stock';
    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        $lowStock = static::getModel()::where('company_id', $companyId)
            ->whereColumn('available_quantity', '<', 'minimum_stock')
            ->count();
        
        return $lowStock > 0 ? (string) $lowStock : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationTooltip(): ?string
    {
        return 'Kelola stok barang di gudang';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pilih atau Tambah Barang')
                    ->description('Pilih barang yang sudah ada atau masukkan barang baru')
                    ->schema([
                        Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),

                        Select::make('existing_product')
                            ->label('Pilih Barang yang Sudah Ada (Opsional)')
                            ->options(function () {
                                $companyId = session('selected_company_id');
                                $productType = session('stock_type_create');
                                
                                $query = Stock::where('company_id', $companyId)
                                    ->select('product_code', 'product_name', 'product_type', 'unit', 'category', 'base_price')
                                    ->groupBy('product_code', 'product_name', 'product_type', 'unit', 'category', 'base_price');
                                
                                if ($productType) {
                                    $query->where('product_type', $productType);
                                }
                                
                                return $query->get()
                                    ->mapWithKeys(function ($item) {
                                        $key = $item->product_code . '|' . $item->product_name;
                                        $label = $item->product_code . ' - ' . $item->product_name . ' (' . $item->product_type . ')';
                                        return [$key => $label];
                                    });
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    [$code, $name] = explode('|', $state);
                                    
                                    // Get latest stock data for this product
                                    $latestStock = Stock::where('company_id', session('selected_company_id'))
                                        ->where('product_code', $code)
                                        ->where('product_name', $name)
                                        ->latest()
                                        ->first();
                                    
                                    if ($latestStock) {
                                        $set('product_code', $latestStock->product_code);
                                        $set('product_name', $latestStock->product_name);
                                        $set('product_type', $latestStock->product_type);
                                        $set('unit', $latestStock->unit);
                                        $set('category', $latestStock->category);
                                        $set('base_price', $latestStock->base_price);
                                        $set('minimum_stock', $latestStock->minimum_stock);
                                        $set('quantity', $latestStock->quantity);
                                        $set('unit_cost', $latestStock->unit_cost);
                                    }
                                } else {
                                    // Clear fields when deselected
                                    $set('product_code', null);
                                    $set('product_name', null);
                                    $set('product_type', session('stock_type_create', 'Local'));
                                    $set('unit', null);
                                    $set('category', null);
                                    $set('base_price', null);
                                }
                            })
                            ->helperText(function () {
                                $type = session('stock_type_create');
                                if ($type === 'Local') {
                                    return '✅ Menampilkan barang LOKAL yang sudah ada';
                                } elseif ($type === 'Import') {
                                    return '📘 Menampilkan barang IMPORT yang sudah ada';
                                }
                                return '💡 Kosongkan jika ingin input barang baru';
                            }),
                    ])
                    ->columns(1),

                Section::make('Informasi Barang')
                    ->schema([
                        TextInput::make('product_code')
                            ->label('Kode Barang')
                            ->maxLength(100)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Kode unik untuk identifikasi barang'),

                        TextInput::make('product_name')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255),

                        Select::make('product_type')
                            ->label('Jenis Barang')
                            ->options([
                                'Local' => '🏭 Lokal',
                                'Import' => '🌍 Import',
                            ])
                            ->default(fn() => session('stock_type_create', 'Local'))
                            ->required()
                            ->disabled(fn ($get) => $get('existing_product') !== null)
                            ->dehydrated(),

                        TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->default('pcs')
                            ->helperText('Contoh: pcs, box, kg, meter, dll'),

                        TextInput::make('category')
                            ->label('Kategori')
                            ->maxLength(100)
                            ->helperText('Contoh: Elektronik, Furniture, dll'),

                        TextInput::make('base_price')
                            ->label('Harga Dasar')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->helperText('Harga jual dasar'),
                    ])
                    ->columns(2),

                Section::make('Informasi Stok')
                    ->schema([
                        TextInput::make('batch_number')
                            ->label('Nomor Batch')
                            ->helperText('Opsional, untuk tracking batch produk'),

                        TextInput::make('quantity')
                            ->label('Jumlah Stok')
                            ->numeric()
                            ->required()
                            ->default(0),

                        TextInput::make('minimum_stock')
                            ->label('Stok Minimum')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->helperText('Sistem akan memberikan notifikasi jika stok di bawah nilai ini'),

                        TextInput::make('unit_cost')
                            ->label('Harga Pokok')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Harga pokok per unit'),

                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kadaluarsa')
                            ->helperText('Opsional, untuk produk yang memiliki tanggal kadaluarsa'),

                        TextInput::make('location')
                            ->label('Lokasi Penyimpanan')
                            ->helperText('Contoh: Gudang A, Rak B2, dll'),

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
                TextColumn::make('product_code')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('product_name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                TextColumn::make('product_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Local' => 'success',
                        'Import' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Local' => '🏭 Lokal',
                        'Import' => '🌍 Import',
                        default => $state,
                    }),
                
                TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                
                // Anomaly Detection Column (NEW!)
                TextColumn::make('anomaly_status')
                    ->label('Status Anomali')
                    ->getStateUsing(function ($record) {
                        $anomalies = [];
                        
                        // 1. Negative quantities
                        if ($record->quantity < 0 || $record->available_quantity < 0 || $record->reserved_quantity < 0) {
                            return '❌ Qty Negative';
                        }
                        
                        // 2. Available > Total (impossible)
                        if ($record->available_quantity > $record->quantity) {
                            return '⚠️ Available > Total';
                        }
                        
                        // 3. Reserved > Total (impossible)
                        if ($record->reserved_quantity > $record->quantity) {
                            return '⚠️ Reserved > Total';
                        }
                        
                        // 4. Total != Available + Reserved
                        $calculated = $record->available_quantity + $record->reserved_quantity;
                        if ($record->quantity != $calculated) {
                            return '⚠️ Qty tidak balance';
                        }
                        
                        // 5. Expired product
                        if ($record->isExpired()) {
                            return '🕐 Kadaluarsa';
                        }
                        
                        // 6. Below minimum
                        if ($record->isBelowMinimum()) {
                            return '🔔 Low Stock';
                        }
                        
                        return '✅ Normal';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->quantity < 0 || $record->available_quantity < 0 || $record->reserved_quantity < 0) {
                            return 'danger';
                        }
                        
                        if ($record->available_quantity > $record->quantity || $record->reserved_quantity > $record->quantity) {
                            return 'danger';
                        }
                        
                        $calculated = $record->available_quantity + $record->reserved_quantity;
                        if ($record->quantity != $calculated) {
                            return 'warning';
                        }
                        
                        if ($record->isExpired()) {
                            return 'danger';
                        }
                        
                        if ($record->isBelowMinimum()) {
                            return 'warning';
                        }
                        
                        return 'success';
                    })
                    ->sortable()
                    ->tooltip(function ($record) {
                        if ($record->quantity < 0 || $record->available_quantity < 0 || $record->reserved_quantity < 0) {
                            return 'CRITICAL: Ada quantity yang negative. Data error, perlu dikoreksi.';
                        }
                        
                        if ($record->available_quantity > $record->quantity) {
                            return "WARNING: Available ({$record->available_quantity}) > Total ({$record->quantity}). Tidak mungkin terjadi.";
                        }
                        
                        if ($record->reserved_quantity > $record->quantity) {
                            return "WARNING: Reserved ({$record->reserved_quantity}) > Total ({$record->quantity}). Tidak mungkin terjadi.";
                        }
                        
                        $calculated = $record->available_quantity + $record->reserved_quantity;
                        if ($record->quantity != $calculated) {
                            return "WARNING: Total ({$record->quantity}) ≠ Available ({$record->available_quantity}) + Reserved ({$record->reserved_quantity}). Harus: {$calculated}";
                        }
                        
                        if ($record->isExpired()) {
                            return 'Product sudah kadaluarsa sejak ' . $record->expiry_date->format('d/m/Y');
                        }
                        
                        if ($record->isBelowMinimum()) {
                            return "Stock di bawah minimum ({$record->minimum_stock}). Perlu restock.";
                        }
                        
                        return 'Stock dalam kondisi normal';
                    }),

                TextColumn::make('batch_number')
                    ->label('Batch')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Total Stok')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reserved_quantity')
                    ->label('Direservasi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('available_quantity')
                    ->label('Tersedia')
                    ->numeric()
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->isBelowMinimum()) {
                            return 'danger';
                        }
                        return 'success';
                    }),

                TextColumn::make('minimum_stock')
                    ->label('Min. Stok')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_below_minimum')
                    ->label('Alert')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return $record->isBelowMinimum();
                    })
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('unit_cost')
                    ->label('Harga Pokok')
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
                        if ($record->isExpiringSoon()) {
                            return 'warning';
                        }
                        return null;
                    }),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),

                TextColumn::make('delivery_note_sync_status')
                    ->label('Sinkronisasi SJ')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $companyId = session('selected_company_id');
                        
                        // Get delivery notes with this product that are Sent/Completed
                        $unsyncedDeliveryNotes = \DB::table('delivery_notes as dn')
                            ->join('delivery_note_items as dni', 'dn.sj_id', '=', 'dni.sj_id')
                            ->where('dn.company_id', $companyId)
                            ->where('dni.product_id', $record->product_id)
                            ->whereIn('dn.status', ['Sent', 'Completed'])
                            ->whereNotExists(function ($query) {
                                $query->select(\DB::raw(1))
                                    ->from('stock_movements as sm')
                                    ->whereColumn('sm.reference_id', 'dn.sj_id')
                                    ->where('sm.reference_type', 'delivery_note')
                                    ->whereColumn('sm.product_id', 'dni.product_id')
                                    ->where('sm.movement_type', 'out');
                            })
                            ->count();
                        
                        if ($unsyncedDeliveryNotes > 0) {
                            return "⚠️ {$unsyncedDeliveryNotes} SJ perlu sync";
                        }
                        
                        return "✅ Sync";
                    })
                    ->color(function ($record) {
                        $companyId = session('selected_company_id');
                        
                        $unsyncedDeliveryNotes = \DB::table('delivery_notes as dn')
                            ->join('delivery_note_items as dni', 'dn.sj_id', '=', 'dni.sj_id')
                            ->where('dn.company_id', $companyId)
                            ->where('dni.product_id', $record->product_id)
                            ->whereIn('dn.status', ['Sent', 'Completed'])
                            ->whereNotExists(function ($query) {
                                $query->select(\DB::raw(1))
                                    ->from('stock_movements as sm')
                                    ->whereColumn('sm.reference_id', 'dn.sj_id')
                                    ->where('sm.reference_type', 'delivery_note')
                                    ->whereColumn('sm.product_id', 'dni.product_id')
                                    ->where('sm.movement_type', 'out');
                            })
                            ->count();
                        
                        return $unsyncedDeliveryNotes > 0 ? 'warning' : 'success';
                    })
                    ->tooltip(function ($record) {
                        $companyId = session('selected_company_id');
                        
                        $unsyncedSJ = \DB::table('delivery_notes as dn')
                            ->join('delivery_note_items as dni', 'dn.sj_id', '=', 'dni.sj_id')
                            ->select('dn.sj_number', 'dni.qty', 'dn.status')
                            ->where('dn.company_id', $companyId)
                            ->where('dni.product_id', $record->product_id)
                            ->whereIn('dn.status', ['Sent', 'Completed'])
                            ->whereNotExists(function ($query) {
                                $query->select(\DB::raw(1))
                                    ->from('stock_movements as sm')
                                    ->whereColumn('sm.reference_id', 'dn.sj_id')
                                    ->where('sm.reference_type', 'delivery_note')
                                    ->whereColumn('sm.product_id', 'dni.product_id')
                                    ->where('sm.movement_type', 'out');
                            })
                            ->limit(5)
                            ->get();
                        
                        if ($unsyncedSJ->isEmpty()) {
                            return 'Semua Surat Jalan sudah tersinkronisasi dengan Stock Movement';
                        }
                        
                        $tooltip = "Surat Jalan yang perlu disinkronkan:\n\n";
                        foreach ($unsyncedSJ as $sj) {
                            $tooltip .= "• {$sj->sj_number} ({$sj->status}): {$sj->qty} unit\n";
                        }
                        
                        return $tooltip;
                    })
                    ->searchable(false)
                    ->sortable(false),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('low_stock')
                    ->label('Stok Rendah')
                    ->query(function (Builder $query) {
                        return $query->whereRaw('available_quantity < minimum_stock');
                    }),

                Filter::make('expiring_soon')
                    ->label('Akan Kadaluarsa')
                    ->query(function (Builder $query) {
                        return $query->where('expiry_date', '<=', now()->addDays(30))
                            ->whereNotNull('expiry_date');
                    }),

                Filter::make('expired')
                    ->label('Sudah Kadaluarsa')
                    ->query(function (Builder $query) {
                        return $query->where('expiry_date', '<', now())
                            ->whereNotNull('expiry_date');
                    }),

                SelectFilter::make('product_type')
                    ->label('Jenis Barang')
                    ->options([
                        'Local' => '🏭 Lokal',
                        'Import' => '🌍 Import',
                    ])
                    ->multiple(),
                
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(function () {
                        $companyId = session('selected_company_id');
                        return \App\Models\Stock::where('company_id', $companyId)
                            ->whereNotNull('category')
                            ->distinct()
                            ->pluck('category', 'category')
                            ->toArray();
                    })
                    ->multiple(),

                SelectFilter::make('location')
                    ->label('Lokasi')
                    ->options(function () {
                        $companyId = session('selected_company_id');
                        return Stock::where('company_id', $companyId)
                            ->whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location')
                            ->toArray();
                    }),

                SelectFilter::make('anomaly')
                    ->label('Status Anomali')
                    ->options([
                        'negative' => '❌ Qty Negative',
                        'available_exceeds' => '⚠️ Available > Total',
                        'reserved_exceeds' => '⚠️ Reserved > Total',
                        'imbalance' => '⚠️ Qty tidak balance',
                        'expired' => '🕐 Kadaluarsa',
                        'low_stock' => '🔔 Low Stock',
                        'normal' => '✅ Normal',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        $anomalyType = $data['value'];

                        switch ($anomalyType) {
                            case 'negative':
                                return $query->where(function ($q) {
                                    $q->where('quantity', '<', 0)
                                      ->orWhere('available_quantity', '<', 0)
                                      ->orWhere('reserved_quantity', '<', 0);
                                });
                            
                            case 'available_exceeds':
                                return $query->whereColumn('available_quantity', '>', 'quantity');
                            
                            case 'reserved_exceeds':
                                return $query->whereColumn('reserved_quantity', '>', 'quantity');
                            
                            case 'imbalance':
                                return $query->whereRaw('quantity != (available_quantity + reserved_quantity)');
                            
                            case 'expired':
                                return $query->where('expiry_date', '<', now())
                                            ->whereNotNull('expiry_date');
                            
                            case 'low_stock':
                                return $query->whereColumn('available_quantity', '<=', 'minimum_stock')
                                            ->where('minimum_stock', '>', 0);
                            
                            case 'normal':
                                return $query->where('quantity', '>=', 0)
                                ->where('available_quantity', '>=', 0)
                                ->where('reserved_quantity', '>=', 0)
                                ->whereColumn('available_quantity', '<=', 'quantity')
                                ->whereColumn('reserved_quantity', '<=', 'quantity')
                                ->whereRaw('quantity = (available_quantity + reserved_quantity)')
                                ->where(function ($q) {
                                    $q->whereNull('expiry_date')
                                      ->orWhere('expiry_date', '>=', now());
                                })
                                ->where(function ($q) {
                                    $q->where('minimum_stock', '=', 0)
                                      ->orWhereColumn('available_quantity', '>', 'minimum_stock');
                                });
                            
                            default:
                                return $query;
                        }
                    }),

                Filter::make('needs_sj_sync')
                    ->label('Perlu Sync Surat Jalan')
                    ->query(function (Builder $query) {
                        $companyId = session('selected_company_id');
                        
                        return $query->whereExists(function ($q) use ($companyId) {
                            $q->select(\DB::raw(1))
                                ->from('delivery_note_items as dni')
                                ->join('delivery_notes as dn', 'dni.sj_id', '=', 'dn.sj_id')
                                ->whereColumn('dni.product_id', 'stocks.product_id')
                                ->where('dn.company_id', $companyId)
                                ->whereIn('dn.status', ['Sent', 'Completed'])
                                ->whereNotExists(function ($q2) {
                                    $q2->select(\DB::raw(1))
                                        ->from('stock_movements as sm')
                                        ->whereColumn('sm.reference_id', 'dn.sj_id')
                                        ->where('sm.reference_type', 'delivery_note')
                                        ->whereColumn('sm.product_id', 'dni.product_id')
                                        ->where('sm.movement_type', 'out');
                                });
                        });
                    })
                    ->indicateUsing(function () {
                        return 'Menampilkan stock yang perlu sinkronisasi dengan Surat Jalan';
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                
                Action::make('sync_delivery_notes')
                    ->label('Sync Surat Jalan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronisasi Stock Movement dari Surat Jalan')
                    ->modalDescription(function ($record) {
                        $companyId = session('selected_company_id');
                        
                        $unsyncedSJ = \DB::table('delivery_notes as dn')
                            ->join('delivery_note_items as dni', 'dn.sj_id', '=', 'dni.sj_id')
                            ->select('dn.sj_number', 'dn.sj_id', 'dni.qty', 'dn.status', 'dni.unit_price')
                            ->where('dn.company_id', $companyId)
                            ->where('dni.product_id', $record->product_id)
                            ->whereIn('dn.status', ['Sent', 'Completed'])
                            ->whereNotExists(function ($query) {
                                $query->select(\DB::raw(1))
                                    ->from('stock_movements as sm')
                                    ->whereColumn('sm.reference_id', 'dn.sj_id')
                                    ->where('sm.reference_type', 'delivery_note')
                                    ->whereColumn('sm.product_id', 'dni.product_id')
                                    ->where('sm.movement_type', 'out');
                            })
                            ->get();
                        
                        if ($unsyncedSJ->isEmpty()) {
                            return 'Tidak ada Surat Jalan yang perlu disinkronkan.';
                        }
                        
                        $description = "Akan membuat Stock Movement untuk Surat Jalan berikut:\n\n";
                        foreach ($unsyncedSJ as $sj) {
                            $description .= "• {$sj->sj_number} ({$sj->status}): {$sj->qty} unit\n";
                        }
                        $description .= "\nStock akan dikurangi sesuai dengan qty yang terkirim.";
                        
                        return $description;
                    })
                    ->modalSubmitActionLabel('Ya, Sinkronkan')
                    ->action(function ($record) {
                        $companyId = session('selected_company_id');
                        
                        try {
                            \DB::beginTransaction();
                            
                            // Get unsynced delivery notes for this product
                            $unsyncedSJ = \DB::table('delivery_notes as dn')
                                ->join('delivery_note_items as dni', 'dn.sj_id', '=', 'dni.sj_id')
                                ->join('customers as c', 'dn.customer_id', '=', 'c.customer_id')
                                ->select('dn.sj_number', 'dn.sj_id', 'dni.qty', 'dni.unit_price', 'dn.created_by', 'c.name as customer_name')
                                ->where('dn.company_id', $companyId)
                                ->where('dni.product_id', $record->product_id)
                                ->whereIn('dn.status', ['Sent', 'Completed'])
                                ->whereNotExists(function ($query) {
                                    $query->select(\DB::raw(1))
                                        ->from('stock_movements as sm')
                                        ->whereColumn('sm.reference_id', 'dn.sj_id')
                                        ->where('sm.reference_type', 'delivery_note')
                                        ->whereColumn('sm.product_id', 'dni.product_id')
                                        ->where('sm.movement_type', 'out');
                                })
                                ->get();
                            
                            if ($unsyncedSJ->isEmpty()) {
                                \DB::rollBack();
                                Notification::make()
                                    ->title('Tidak Ada Yang Perlu Disinkronkan')
                                    ->body('Semua Surat Jalan sudah tersinkronisasi.')
                                    ->info()
                                    ->send();
                                return;
                            }
                            
                            $totalReduced = 0;
                            $createdMovements = 0;
                            
                            foreach ($unsyncedSJ as $sj) {
                                // Check if stock is sufficient
                                if ($record->available_quantity < $sj->qty) {
                                    \DB::rollBack();
                                    Notification::make()
                                        ->title('Stock Tidak Mencukupi')
                                        ->body("Stock tidak cukup untuk sync SJ {$sj->sj_number}. Tersedia: {$record->available_quantity}, Dibutuhkan: {$sj->qty}")
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                    return;
                                }
                                
                                // Create stock movement
                                \App\Models\StockMovement::create([
                                    'company_id' => $companyId,
                                    'product_id' => $record->product_id,
                                    'movement_type' => 'out',
                                    'quantity' => $sj->qty,
                                    'unit_cost' => $sj->unit_price,
                                    'reference_type' => 'delivery_note',
                                    'reference_id' => $sj->sj_id,
                                    'notes' => "Manual sync - Pengiriman via Surat Jalan {$sj->sj_number} - {$sj->customer_name}",
                                    'created_by' => auth()->id() ?? $sj->created_by,
                                ]);
                                
                                // Reduce stock
                                $record->available_quantity -= $sj->qty;
                                $record->quantity -= $sj->qty;
                                
                                $totalReduced += $sj->qty;
                                $createdMovements++;
                            }
                            
                            $record->save();
                            
                            \DB::commit();
                            
                            Notification::make()
                                ->title('Sinkronisasi Berhasil')
                                ->body("{$createdMovements} Stock Movement dibuat. Total stock dikurangi: {$totalReduced} unit.")
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            \DB::rollBack();
                            
                            Notification::make()
                                ->title('Error Sinkronisasi')
                                ->body("Gagal sinkronisasi: {$e->getMessage()}")
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    })
                    ->visible(function ($record) {
                        $companyId = session('selected_company_id');
                        
                        // Only show if there are unsynced delivery notes
                        $unsyncedCount = \DB::table('delivery_notes as dn')
                            ->join('delivery_note_items as dni', 'dn.sj_id', '=', 'dni.sj_id')
                            ->where('dn.company_id', $companyId)
                            ->where('dni.product_id', $record->product_id)
                            ->whereIn('dn.status', ['Sent', 'Completed'])
                            ->whereNotExists(function ($query) {
                                $query->select(\DB::raw(1))
                                    ->from('stock_movements')
                                    ->whereColumn('stock_movements.reference_id', 'dn.sj_id')
                                    ->where('stock_movements.reference_type', 'delivery_note')
                                    ->whereColumn('stock_movements.product_id', 'dni.product_id');
                            })
                            ->count();
                        
                        return $unsyncedCount > 0;
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    \Filament\Tables\Actions\BulkAction::make('bulk_sync_delivery_notes')
                        ->label('Sync Surat Jalan (Bulk)')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Sinkronisasi Stock Movement dari Surat Jalan (Bulk)')
                        ->modalDescription('Akan membuat Stock Movement untuk semua Surat Jalan yang belum tersinkronisasi dari produk yang dipilih.')
                        ->modalSubmitActionLabel('Ya, Sinkronkan Semua')
                        ->action(function ($records) {
                            $companyId = session('selected_company_id');
                            $totalMovements = 0;
                            $totalReduced = 0;
                            $errors = [];
                            
                            try {
                                \DB::beginTransaction();
                                
                                foreach ($records as $record) {
                                    // Get unsynced delivery notes for this product
                                    $unsyncedSJ = \DB::table('delivery_notes as dn')
                                        ->join('delivery_note_items as dni', 'dn.sj_id', '=', 'dni.sj_id')
                                        ->join('customers as c', 'dn.customer_id', '=', 'c.customer_id')
                                        ->select('dn.sj_number', 'dn.sj_id', 'dni.qty', 'dni.unit_price', 'dn.created_by', 'c.name as customer_name')
                                        ->where('dn.company_id', $companyId)
                                        ->where('dni.product_id', $record->product_id)
                                        ->whereIn('dn.status', ['Sent', 'Completed'])
                                        ->whereNotExists(function ($query) {
                                            $query->select(\DB::raw(1))
                                                ->from('stock_movements as sm')
                                                ->whereColumn('sm.reference_id', 'dn.sj_id')
                                                ->where('sm.reference_type', 'delivery_note')
                                                ->whereColumn('sm.product_id', 'dni.product_id')
                                                ->where('sm.movement_type', 'out');
                                        })
                                        ->get();
                                    
                                    if ($unsyncedSJ->isEmpty()) {
                                        continue;
                                    }
                                    
                                    foreach ($unsyncedSJ as $sj) {
                                        // Check if stock is sufficient
                                        if ($record->available_quantity < $sj->qty) {
                                            $errors[] = "{$record->product->name} - SJ {$sj->sj_number}: Stock tidak cukup (Tersedia: {$record->available_quantity}, Dibutuhkan: {$sj->qty})";
                                            continue;
                                        }
                                        
                                        // Create stock movement
                                        \App\Models\StockMovement::create([
                                            'company_id' => $companyId,
                                            'product_id' => $record->product_id,
                                            'movement_type' => 'out',
                                            'quantity' => $sj->qty,
                                            'unit_cost' => $sj->unit_price,
                                            'reference_type' => 'delivery_note',
                                            'reference_id' => $sj->sj_id,
                                            'notes' => "Bulk sync - Pengiriman via Surat Jalan {$sj->sj_number} - {$sj->customer_name}",
                                            'created_by' => auth()->id() ?? $sj->created_by,
                                        ]);
                                        
                                        // Reduce stock
                                        $record->available_quantity -= $sj->qty;
                                        $record->quantity -= $sj->qty;
                                        
                                        $totalReduced += $sj->qty;
                                        $totalMovements++;
                                    }
                                    
                                    $record->save();
                                }
                                
                                \DB::commit();
                                
                                $message = "{$totalMovements} Stock Movement dibuat. Total stock dikurangi: {$totalReduced} unit.";
                                
                                if (!empty($errors)) {
                                    $message .= "\n\nError:\n" . implode("\n", $errors);
                                }
                                
                                Notification::make()
                                    ->title('Sinkronisasi Selesai')
                                    ->body($message)
                                    ->success()
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                \DB::rollBack();
                                
                                Notification::make()
                                    ->title('Error Sinkronisasi')
                                    ->body("Gagal sinkronisasi: {$e->getMessage()}")
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'view' => Pages\ViewStock::route('/{record}'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        return parent::getEloquentQuery()
            ->where('company_id', $companyId);
    }
}

