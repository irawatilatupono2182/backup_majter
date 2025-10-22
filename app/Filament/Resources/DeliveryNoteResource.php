<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryNoteResource\Pages;
use App\Models\DeliveryNote;
use App\Models\Customer;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Surat Jalan (SJ)';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Surat Jalan')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->id()),
                        Forms\Components\TextInput::make('sj_number')
                            ->label('Nomor SJ')
                            ->disabled()
                            ->dehydrated(false)
                            ->default('(Auto Generated)')
                            ->helperText('Nomor SJ akan di-generate otomatis saat disimpan')
                            ->maxLength(50),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn() => Customer::where('company_id', session('selected_company_id'))
                                ->where('is_active', true)
                                ->pluck('name', 'customer_id'))
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    $set('type', $customer && $customer->is_ppn ? 'PPN' : 'Non-PPN');
                                }
                            }),
                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'PPN' => 'PPN',
                                'Non-PPN' => 'Non-PPN',
                                'Supplier' => 'Supplier',
                            ])
                            ->required()
                            ->default('PPN'),
                        Forms\Components\DatePicker::make('delivery_date')
                            ->label('Tanggal Kirim')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Draft' => 'Draft',
                                'Sent' => 'Sent',
                                'Completed' => 'Completed',
                            ])
                            ->required()
                            ->default('Draft')
                            ->helperText('⚠️ Mengubah status ke Sent/Completed akan otomatis membuat stock movement dan mengurangi stock. Mengubah kembali ke Draft akan mengembalikan stock.')
                            ->live(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ]),
                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->options(function () {
                                        $companyId = session('selected_company_id');
                                        
                                        // Get products with available stock or CATALOG products
                                        return Product::where('company_id', $companyId)
                                            ->where('is_active', true)
                                            ->where(function ($query) use ($companyId) {
                                                // CATALOG products (always available)
                                                $query->where('product_type', 'CATALOG')
                                                    // OR STOCK products with available quantity > 0
                                                    ->orWhereHas('stock', function ($q) use ($companyId) {
                                                        $q->where('company_id', $companyId)
                                                          ->where('available_quantity', '>', 0);
                                                    });
                                            })
                                            ->pluck('name', 'product_id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            $set('unit', $product ? $product->unit : '');
                                            $set('unit_price', $product ? $product->base_price : 0);
                                        }
                                    })
                                    ->helperText('💡 Hanya menampilkan produk CATALOG atau produk STOCK yang tersedia'),
                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => 
                                        $set('subtotal', ((float)$get('qty') * (float)$get('unit_price')) * (1 - (float)$get('discount_percent') / 100)))
                                    ->helperText(function (Forms\Get $get) {
                                        $productId = $get('product_id');
                                        
                                        if (!$productId) {
                                            return null;
                                        }
                                        
                                        $product = Product::with('stock')->find($productId);
                                        
                                        if (!$product) {
                                            return null;
                                        }
                                        
                                        // CATALOG products don't have stock limitation
                                        if ($product->product_type === 'CATALOG') {
                                            return '📦 Produk CATALOG (tidak ada batasan stock)';
                                        }
                                        
                                        // STOCK products - show available quantity
                                        $companyId = session('selected_company_id');
                                        $stock = $product->stock()->where('company_id', $companyId)->first();
                                        
                                        if (!$stock) {
                                            return '⚠️ Stock tidak tersedia';
                                        }
                                        
                                        $available = $stock->available_quantity;
                                        
                                        if ($available <= 0) {
                                            return '⚠️ Stock habis';
                                        }
                                        
                                        return "✅ Stock tersedia: {$available} unit";
                                    })
                                    ->rules([
                                        function (Forms\Get $get) {
                                            return function ($attribute, $value, $fail) use ($get) {
                                                $productId = $get('product_id');
                                                
                                                if (!$productId || !$value) {
                                                    return;
                                                }
                                                
                                                $product = Product::find($productId);
                                                
                                                if (!$product) {
                                                    return;
                                                }
                                                
                                                // Skip validation for CATALOG products
                                                if ($product->product_type === 'CATALOG') {
                                                    return;
                                                }
                                                
                                                // Validate for STOCK products
                                                $companyId = session('selected_company_id');
                                                $stock = \App\Models\Stock::where('company_id', $companyId)
                                                    ->where('product_id', $productId)
                                                    ->first();
                                                
                                                if (!$stock) {
                                                    $fail('Stock tidak tersedia untuk produk ini.');
                                                    return;
                                                }
                                                
                                                if ($value > $stock->available_quantity) {
                                                    $kekurangan = $value - $stock->available_quantity;
                                                    $fail("Stock tidak mencukupi! Stock tersedia: {$stock->available_quantity} unit. Kekurangan: {$kekurangan} unit.");
                                                }
                                            };
                                        }
                                    ]),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->required(),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => 
                                        $set('subtotal', ((float)$get('qty') * (float)$get('unit_price')) * (1 - (float)$get('discount_percent') / 100))),
                                Forms\Components\TextInput::make('discount_percent')
                                    ->label('Diskon (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => 
                                        $set('subtotal', ((float)$get('qty') * (float)$get('unit_price')) * (1 - (float)$get('discount_percent') / 100))),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Item')
                            ->reorderable(false)
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('company_id', session('selected_company_id')))
            ->columns([
                Tables\Columns\TextColumn::make('sj_number')
                    ->label('Nomor SJ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'PPN',
                        'secondary' => 'Non-PPN',
                        'warning' => 'Supplier',
                    ]),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Tanggal Kirim')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'Draft',
                        'primary' => 'Sent',
                        'success' => 'Completed',
                    ]),
                Tables\Columns\TextColumn::make('stock_movement_status')
                    ->label('Stock Movement')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $movements = \App\Models\StockMovement::where('reference_type', 'delivery_note')
                            ->where('reference_id', $record->sj_id)
                            ->count();
                        
                        if ($movements > 0) {
                            return "✅ {$movements} records";
                        }
                        
                        if (in_array($record->status, ['Sent', 'Completed'])) {
                            return "⚠️ Belum ada";
                        }
                        
                        return "-";
                    })
                    ->color(function ($record) {
                        $movements = \App\Models\StockMovement::where('reference_type', 'delivery_note')
                            ->where('reference_id', $record->sj_id)
                            ->count();
                        
                        if ($movements > 0) {
                            return 'success';
                        }
                        
                        if (in_array($record->status, ['Sent', 'Completed'])) {
                            return 'warning';
                        }
                        
                        return 'gray';
                    })
                    ->tooltip(function ($record) {
                        $movements = \App\Models\StockMovement::where('reference_type', 'delivery_note')
                            ->where('reference_id', $record->sj_id)
                            ->count();
                        
                        if ($movements > 0) {
                            return "Stock movement telah dibuat ({$movements} movement records)";
                        }
                        
                        if (in_array($record->status, ['Sent', 'Completed'])) {
                            return "Stock movement belum dibuat (seharusnya sudah ada)";
                        }
                        
                        return "Status masih Draft";
                    }),
                Tables\Columns\IconColumn::make('has_invoice')
                    ->label('Invoice')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->hasInvoice()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Sent' => 'Sent',
                        'Completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'PPN' => 'PPN',
                        'Non-PPN' => 'Non-PPN',
                        'Supplier' => 'Supplier',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('create_invoice')
                    ->label('Buat Invoice')
                    ->icon('heroicon-o-document-plus')
                    ->visible(fn($record) => !$record->hasInvoice() && $record->status === 'Completed')
                    ->url(fn($record) => route('filament.admin.resources.invoices.create', ['sj_id' => $record->sj_id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    protected static function generateSJNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $companyId = session('selected_company_id');
        
        // Use database transaction with lock to prevent race condition
        return \DB::transaction(function () use ($year, $month, $companyId) {
            // Get last number from existing records with lock
            $lastRecord = DeliveryNote::where('company_id', $companyId)
                ->where('sj_number', 'like', "SJ/{$year}/{$month}/%")
                ->lockForUpdate() // Lock the row to prevent concurrent reads
                ->orderBy('sj_number', 'desc')
                ->first();
            
            if ($lastRecord) {
                // Extract number from last record (e.g., "SJ/2025/10/001" -> 1)
                $parts = explode('/', $lastRecord->sj_number);
                $lastNumber = isset($parts[3]) ? (int)$parts[3] : 0;
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            return sprintf('SJ/%s/%s/%03d', $year, $month, $nextNumber);
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryNotes::route('/'),
            'create' => Pages\CreateDeliveryNote::route('/create'),
            'view' => Pages\ViewDeliveryNote::route('/{record}'),
            'edit' => Pages\EditDeliveryNote::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'items.product', 'createdBy']);
    }
}