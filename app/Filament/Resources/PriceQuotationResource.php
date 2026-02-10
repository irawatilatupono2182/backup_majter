<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceQuotationResource\Pages;
use App\Models\PriceQuotation;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PriceQuotationResource extends Resource
{
    protected static ?string $model = PriceQuotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = '💼 Penjualan';
    
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Surat Penawaran';


    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        return static::getModel()::where('company_id', $companyId)
            ->where('status', 'Sent')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationTooltip(): ?string
    {
        return 'Kelola penawaran harga untuk customer (sales) atau dari supplier (purchasing)';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penawaran')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->id()),
                        Forms\Components\TextInput::make('quotation_number')
                            ->label('Nomor PH')
                            ->required()
                            ->default(fn() => self::generateQuotationNumber())
                            ->maxLength(50),
                        
                        // Entity Type Selection (Customer or Supplier)
                        Forms\Components\Select::make('entity_type')
                            ->label('Tipe Penawaran')
                            ->options([
                                'customer' => '📤 Untuk Customer (Sales - Penawaran Jual)',
                                'supplier' => '📥 Untuk Supplier (Purchasing - Minta Penawaran Beli)',
                            ])
                            ->required()
                            ->default('customer')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Clear both when switching
                                $set('customer_id', null);
                                $set('supplier_id', null);
                                $set('entity_id', null);
                            })
                            ->helperText('Pilih apakah PH ini untuk customer (kita menawarkan) atau supplier (kita minta penawaran)'),
                        
                        // Dynamic Customer/Supplier Selection
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn() => Customer::where('company_id', session('selected_company_id'))
                                ->where('is_active', true)
                                ->pluck('name', 'customer_id'))
                            ->searchable()
                            ->reactive()
                            ->required(fn(Forms\Get $get) => $get('entity_type') === 'customer')
                            ->visible(fn(Forms\Get $get) => $get('entity_type') === 'customer')
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && $get('entity_type') === 'customer') {
                                    $set('entity_id', $state);
                                    $set('supplier_id', null);
                                }
                            }),
                        
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn() => Supplier::where('company_id', session('selected_company_id'))
                                ->where('is_active', true)
                                ->pluck('name', 'supplier_id'))
                            ->searchable()
                            ->reactive()
                            ->required(fn(Forms\Get $get) => $get('entity_type') === 'supplier')
                            ->visible(fn(Forms\Get $get) => $get('entity_type') === 'supplier')
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && $get('entity_type') === 'supplier') {
                                    $set('entity_id', $state);
                                    $set('customer_id', null);
                                }
                            }),
                        
                        Forms\Components\Hidden::make('entity_id'),
                        
                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'PPN' => 'PPN',
                                'Non-PPN' => 'Non-PPN',
                            ])
                            ->required()
                            ->default('PPN'),
                        Forms\Components\DatePicker::make('quotation_date')
                            ->label('Tanggal PH')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Berlaku Hingga')
                            ->after('quotation_date'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Draft' => 'Draft',
                                'Sent' => 'Sent',
                                'Accepted' => 'Accepted',
                                'Rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('Draft'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])->columns(2),
                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->options(function (Forms\Get $get) {
                                        $companyId = session('selected_company_id');
                                        $entityType = $get('../../entity_type'); // Get from parent form
                                        
                                        // For SUPPLIER: Show all active products
                                        if ($entityType === 'supplier') {
                                            return Product::where('company_id', $companyId)
                                                ->where('is_active', true)
                                                ->orderBy('name')
                                                ->pluck('name', 'product_id');
                                        }
                                        
                                        // For CUSTOMER: Show only products with available stock > 0
                                        if ($entityType === 'customer') {
                                            return Product::where('products.company_id', $companyId)
                                                ->where('products.is_active', true)
                                                ->where(function (Builder $query) {
                                                    // Include CATALOG products (always available)
                                                    $query->where('products.product_type', 'CATALOG')
                                                        // Or STOCK products with available quantity
                                                        ->orWhere(function (Builder $q) {
                                                            $q->where('products.product_type', 'STOCK')
                                                                ->whereHas('stock', function (Builder $stockQuery) {
                                                                    $stockQuery->where('available_quantity', '>', 0);
                                                                });
                                                        });
                                                })
                                                ->orderBy('products.name')
                                                ->pluck('products.name', 'products.product_id');
                                        }
                                        
                                        // Default: show all
                                        return Product::where('company_id', $companyId)
                                            ->where('is_active', true)
                                            ->orderBy('name')
                                            ->pluck('name', 'product_id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if ($state) {
                                            $product = Product::with('stock')->find($state);
                                            $set('unit', $product ? $product->unit : '');
                                            $set('unit_price', $product ? $product->base_price : 0);
                                            
                                            // Show available stock for customer quotations
                                            $entityType = $get('../../entity_type');
                                            if ($entityType === 'customer' && $product) {
                                                if ($product->product_type === 'STOCK' && $product->stock) {
                                                    $availableQty = $product->stock->available_quantity;
                                                    $set('_stock_info', "Stok tersedia: {$availableQty} {$product->unit}");
                                                } elseif ($product->product_type === 'CATALOG') {
                                                    $set('_stock_info', "Produk CATALOG (tidak perlu stok)");
                                                } else {
                                                    $set('_stock_info', "Tidak ada stok");
                                                }
                                            } else {
                                                $set('_stock_info', null);
                                            }
                                        }
                                    })
                                    ->helperText(fn(Forms\Get $get) => 
                                        $get('../../entity_type') === 'customer' 
                                            ? '✅ Hanya menampilkan produk dengan stok tersedia' 
                                            : '📦 Menampilkan semua produk aktif'
                                    ),
                                
                                // Stock info placeholder (visible for customer only)
                                Forms\Components\Placeholder::make('_stock_info')
                                    ->label('Info Stok')
                                    ->content(fn(Forms\Get $get) => $get('_stock_info') ?? '-')
                                    ->visible(fn(Forms\Get $get) => $get('../../entity_type') === 'customer' && $get('_stock_info')),
                                
                                // Hidden field to store stock info (not saved to DB)
                                Forms\Components\Hidden::make('_stock_info')
                                    ->dehydrated(false),
                                
                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => 
                                        $set('subtotal', ((float)$get('qty') * (float)$get('unit_price')) * (1 - (float)$get('discount_percent') / 100)))
                                    ->rules([
                                        function (Forms\Get $get) {
                                            return function (string $attribute, $value, $fail) use ($get) {
                                                $entityType = $get('../../entity_type');
                                                $productId = $get('product_id');
                                                
                                                // Validate stock for customer quotations
                                                if ($entityType === 'customer' && $productId) {
                                                    $product = Product::with('stock')->find($productId);
                                                    
                                                    if ($product && $product->product_type === 'STOCK') {
                                                        if (!$product->stock) {
                                                            $fail('Produk ini belum memiliki record stock.');
                                                            return;
                                                        }
                                                        
                                                        $availableQty = $product->stock->available_quantity;
                                                        
                                                        if ($value > $availableQty) {
                                                            $fail("Qty melebihi stok tersedia ({$availableQty} {$product->unit}).");
                                                        }
                                                    }
                                                }
                                            };
                                        },
                                    ])
                                    ->helperText(fn(Forms\Get $get) => 
                                        $get('../../entity_type') === 'customer' 
                                            ? '⚠️ Qty tidak boleh melebihi stok tersedia' 
                                            : null
                                    ),
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
                Tables\Columns\TextColumn::make('quotation_number')
                    ->label('Nomor PH')
                    ->searchable()
                    ->sortable(),
                
                // Show entity type badge
                Tables\Columns\BadgeColumn::make('entity_type')
                    ->label('Tipe')
                    ->formatStateUsing(function ($state) {
                        return $state === 'customer' ? '📤 Customer' : ($state === 'supplier' ? '📥 Supplier' : '-');
                    })
                    ->colors([
                        'success' => 'customer',
                        'primary' => 'supplier',
                    ])
                    ->sortable(),
                
                // Dynamic entity name column
                Tables\Columns\TextColumn::make('entity_name')
                    ->label('Customer / Supplier')
                    ->getStateUsing(function (PriceQuotation $record): string {
                        if ($record->entity_type === 'customer' && $record->customer) {
                            return '👤 ' . $record->customer->name;
                        }
                        if ($record->entity_type === 'supplier' && $record->supplier) {
                            return '🏢 ' . $record->supplier->name;
                        }
                        return '-';
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'PPN',
                        'secondary' => 'Non-PPN',
                    ]),
                Tables\Columns\TextColumn::make('quotation_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'Draft',
                        'primary' => 'Sent',
                        'success' => 'Accepted',
                        'danger' => 'Rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('entity_type')
                    ->label('Tipe Penawaran')
                    ->options([
                        'customer' => '📤 Untuk Customer (Sales)',
                        'supplier' => '📥 Untuk Supplier (Purchasing)',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Sent' => 'Sent',
                        'Accepted' => 'Accepted',
                        'Rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'PPN' => 'PPN',
                        'Non-PPN' => 'Non-PPN',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    protected static function generateQuotationNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $companyId = session('selected_company_id');
        
        // Get last number from existing records
        $lastRecord = PriceQuotation::where('company_id', $companyId)
            ->where('quotation_number', 'like', "PH/{$year}/{$month}/%")
            ->orderBy('quotation_number', 'desc')
            ->first();
        
        if ($lastRecord) {
            // Extract number from last record (e.g., "PH/2025/10/001" -> 1)
            $parts = explode('/', $lastRecord->quotation_number);
            $lastNumber = isset($parts[3]) ? (int)$parts[3] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('PH/%s/%s/%03d', $year, $month, $nextNumber);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceQuotations::route('/'),
            'create' => Pages\CreatePriceQuotation::route('/create'),
            'view' => Pages\ViewPriceQuotation::route('/{record}'),
            'edit' => Pages\EditPriceQuotation::route('/{record}/edit'),
        ];
    }
}