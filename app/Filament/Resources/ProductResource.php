<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = '📦 Master Data';
    
    protected static bool $shouldRegisterNavigation = false; // Hidden per user request

    protected static ?int $navigationSort = 3;

    public static function getNavigationTooltip(): ?string
    {
        return 'Katalog produk/barang yang dijual';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        Forms\Components\TextInput::make('product_code')
                            ->label('Kode Produk (Internal)')
                            ->required()
                            ->maxLength(50)
                            ->helperText('Kode produk untuk kebutuhan internal sistem'),
                        Forms\Components\TextInput::make('original_product_code')
                            ->label('Kode Produk (Asal)')
                            ->maxLength(50)
                            ->helperText('Kode produk asli dari supplier/pabrik'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->placeholder('pcs, kg, liter, dll')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('category')
                            ->label('Kategori')
                            ->maxLength(100),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
                Forms\Components\Section::make('Tipe & Harga')
                    ->schema([
                        Forms\Components\Select::make('product_type')
                            ->label('Tipe Produk')
                            ->options([
                                'STOCK' => 'STOCK (Ada di gudang)',
                                'CATALOG' => 'CATALOG (Hanya referensi)',
                            ])
                            ->required()
                            ->default('STOCK')
                            ->live()
                            ->helperText('STOCK: Produk fisik yang ada di gudang. CATALOG: Hanya untuk penawaran, tidak ada stok fisik.'),
                        Forms\Components\TextInput::make('base_price')
                            ->label('Harga Dasar')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\TextInput::make('default_discount_percent')
                            ->label('Diskon Default (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0),
                        Forms\Components\TextInput::make('min_stock_alert')
                            ->label('Minimum Stok Alert')
                            ->numeric()
                            ->default(5)
                            ->visible(fn(Forms\Get $get) => $get('product_type') === 'STOCK')
                            ->helperText('Notifikasi akan muncul jika stok kurang dari nilai ini'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query
                ->where('company_id', session('selected_company_id'))
                ->with('stock')) // Eager load stock relation
            ->columns([
                Tables\Columns\TextColumn::make('product_code')
                    ->label('Kode (Internal)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('original_product_code')
                    ->label('Kode (Asal)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('product_type')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'STOCK',
                        'warning' => 'CATALOG',
                    ]),
                
                // Stock Status Column (NEW!)
                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Status Stok')
                    ->getStateUsing(function ($record) {
                        // For CATALOG products
                        if ($record->product_type === 'CATALOG') {
                            return 'ðŸ“¦ Catalog';
                        }
                        
                        // For STOCK products
                        if ($record->product_type === 'STOCK') {
                            // Check if stock record exists
                            if (!$record->stock) {
                                return 'âš ï¸ Belum ada record';
                            }
                            
                            $qty = $record->stock->quantity;
                            $available = $record->stock->available_quantity;
                            
                            // Check stock quantity
                            if ($qty == 0 && $available == 0) {
                                return 'âŒ Kosong (0)';
                            } elseif ($available == 0 && $qty > 0) {
                                return 'âš ï¸ Habis (reserved: ' . $qty . ')';
                            } elseif ($available > 0 && $available <= $record->min_stock_alert) {
                                return 'ðŸ”” Low Stock (' . $available . ')';
                            } else {
                                return 'âœ… Tersedia (' . $available . ')';
                            }
                        }
                        
                        return '-';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->product_type === 'CATALOG') {
                            return 'warning';
                        }
                        
                        if ($record->product_type === 'STOCK') {
                            if (!$record->stock) {
                                return 'danger'; // No stock record
                            }
                            
                            $available = $record->stock->available_quantity;
                            
                            if ($available == 0) {
                                return 'danger'; // Empty
                            } elseif ($available <= $record->min_stock_alert) {
                                return 'warning'; // Low stock
                            } else {
                                return 'success'; // Available
                            }
                        }
                        
                        return 'gray';
                    })
                    ->sortable()
                    ->tooltip(function ($record) {
                        if ($record->product_type === 'CATALOG') {
                            return 'Produk catalog tidak memerlukan stock fisik';
                        }
                        
                        if ($record->product_type === 'STOCK') {
                            if (!$record->stock) {
                                return 'PERHATIAN: Produk ini belum memiliki record di tabel stocks. Klik Edit â†’ Save untuk auto-create.';
                            }
                            
                            $stock = $record->stock;
                            return "Total: {$stock->quantity} | Tersedia: {$stock->available_quantity} | Reserved: {$stock->reserved_quantity}";
                        }
                        
                        return null;
                    }),
                
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('Harga Dasar')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('Tipe Produk')
                    ->options([
                        'STOCK' => 'STOCK',
                        'CATALOG' => 'CATALOG',
                    ]),
                
                // Filter by stock status (NEW!)
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Status Stok')
                    ->options([
                        'no_record' => 'âš ï¸ Belum ada record',
                        'empty' => 'âŒ Kosong (0)',
                        'low_stock' => 'ðŸ”” Low Stock',
                        'available' => 'âœ… Tersedia',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }
                        
                        $status = $data['value'];
                        
                        // Only apply to STOCK products
                        $query->where('product_type', 'STOCK');
                        
                        switch ($status) {
                            case 'no_record':
                                // Products without stock record
                                return $query->whereDoesntHave('stock');
                                
                            case 'empty':
                                // Products with stock = 0
                                return $query->whereHas('stock', function ($q) {
                                    $q->where('available_quantity', 0)
                                      ->where('quantity', 0);
                                });
                                
                            case 'low_stock':
                                // Products with stock <= min_stock_alert
                                return $query->whereHas('stock', function ($q) {
                                    $q->whereRaw('available_quantity <= (SELECT min_stock_alert FROM products WHERE products.product_id = stocks.product_id)')
                                      ->where('available_quantity', '>', 0);
                                });
                                
                            case 'available':
                                // Products with good stock
                                return $query->whereHas('stock', function ($q) {
                                    $q->whereRaw('available_quantity > (SELECT min_stock_alert FROM products WHERE products.product_id = stocks.product_id)');
                                });
                                
                            default:
                                return $query;
                        }
                    }),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
                Tables\Filters\Filter::make('category')
                    ->form([
                        Forms\Components\TextInput::make('category')
                            ->label('Kategori'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['category'],
                            fn($query, $category) => $query->where('category', 'like', "%{$category}%")
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                // Fix stock record action (NEW!)
                Tables\Actions\Action::make('fix_stock')
                    ->label('Fix Stock')
                    ->icon('heroicon-o-wrench')
                    ->color('warning')
                    ->visible(fn($record) => $record->product_type === 'STOCK' && !$record->stock)
                    ->requiresConfirmation()
                    ->modalHeading('Create Stock Record')
                    ->modalDescription('Produk ini belum memiliki record di tabel stocks. Buat record dengan quantity 0?')
                    ->action(function ($record) {
                        Stock::create([
                            'company_id' => $record->company_id,
                            'product_id' => $record->product_id,
                            'quantity' => 0,
                            'available_quantity' => 0,
                            'reserved_quantity' => 0,
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Stock Record Created')
                            ->body("Stock record untuk {$record->name} telah dibuat.")
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    
                    // Bulk fix stock records (NEW!)
                    Tables\Actions\BulkAction::make('bulk_fix_stock')
                        ->label('Fix Stock Records')
                        ->icon('heroicon-o-wrench')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Create Stock Records')
                        ->modalDescription(fn($records) => 
                            'Buat stock records untuk ' . $records->count() . ' produk yang belum memiliki record?'
                        )
                        ->action(function ($records) {
                            $created = 0;
                            $skipped = 0;
                            
                            foreach ($records as $record) {
                                // Only for STOCK products without stock record
                                if ($record->product_type === 'STOCK' && !$record->stock) {
                                    Stock::create([
                                        'company_id' => $record->company_id,
                                        'product_id' => $record->product_id,
                                        'quantity' => 0,
                                        'available_quantity' => 0,
                                        'reserved_quantity' => 0,
                                    ]);
                                    $created++;
                                } else {
                                    $skipped++;
                                }
                            }
                            
                            Notification::make()
                                ->success()
                                ->title('Stock Records Created')
                                ->body("Created: {$created} records. Skipped: {$skipped}.")
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
