<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\PriceQuotation;
use App\Models\Supplier;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pembelian Barang (PO)';

    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        return static::getModel()::where('company_id', $companyId)
            ->where('status', 'Pending')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationTooltip(): ?string
    {
        return 'Order pembelian barang dari supplier';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Purchase Order')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->id()),
                        Forms\Components\TextInput::make('po_number')
                            ->label('Nomor PO')
                            ->disabled()
                            ->dehydrated(false)
                            ->default('(Auto Generated)')
                            ->helperText('Nomor PO akan di-generate otomatis saat disimpan')
                            ->maxLength(50),
                        Forms\Components\Select::make('ph_id')
                            ->label('Berdasarkan PH (Opsional)')
                            ->options(function () {
                                $companyId = session('selected_company_id');
                                if (!$companyId) {
                                    return [];
                                }
                                
                                return PriceQuotation::where('company_id', $companyId)
                                    ->whereIn('status', ['Sent', 'Accepted'])
                                    ->with('supplier')
                                    ->get()
                                    ->mapWithKeys(function ($ph) {
                                        return [
                                            $ph->ph_id => sprintf(
                                                '%s - %s (%s)',
                                                $ph->quotation_number,
                                                $ph->supplier->name ?? 'N/A',
                                                $ph->status
                                            )
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $ph = PriceQuotation::with('supplier', 'items.product')->find($state);
                                    if ($ph) {
                                        $set('supplier_id', $ph->supplier_id);
                                        $set('type', $ph->type);
                                        
                                        // Auto-populate items from PH
                                        $items = $ph->items->map(function ($item) {
                                            return [
                                                'product_id' => $item->product_id,
                                                'qty_ordered' => $item->qty,
                                                'qty_received' => 0,
                                                'unit' => $item->unit,
                                                'unit_price' => $item->unit_price,
                                                'discount_percent' => $item->discount_percent,
                                                'subtotal' => $item->subtotal,
                                            ];
                                        })->toArray();
                                        
                                        $set('items', $items);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn() => Supplier::where('company_id', session('selected_company_id'))
                                ->where('is_active', true)
                                ->pluck('name', 'supplier_id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'PPN' => 'PPN',
                                'Non-PPN' => 'Non-PPN',
                            ])
                            ->required()
                            ->default('PPN'),
                        Forms\Components\DatePicker::make('order_date')
                            ->label('Tanggal Order')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('expected_delivery')
                            ->label('Estimasi Pengiriman')
                            ->after('order_date'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Pending' => 'Pending',
                                'Confirmed' => 'Confirmed',
                                'Partial' => 'Partial',
                                'Completed' => 'Completed',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('Pending'),
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
                                    ->options(fn() => Product::where('company_id', session('selected_company_id'))
                                        ->where('is_active', true)
                                        ->pluck('name', 'product_id'))
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            $set('unit', $product ? $product->unit : '');
                                            $set('unit_price', $product ? $product->base_price : 0);
                                        }
                                    }),
                                Forms\Components\TextInput::make('qty_ordered')
                                    ->label('Qty Order')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => 
                                        $set('subtotal', ($get('qty_ordered') * $get('unit_price')) * (1 - $get('discount_percent') / 100))),
                                Forms\Components\TextInput::make('qty_received')
                                    ->label('Qty Diterima')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(),
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
                                        $set('subtotal', ($get('qty_ordered') * $get('unit_price')) * (1 - $get('discount_percent') / 100))),
                                Forms\Components\TextInput::make('discount_percent')
                                    ->label('Diskon (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => 
                                        $set('subtotal', ($get('qty_ordered') * $get('unit_price')) * (1 - $get('discount_percent') / 100))),
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
                Tables\Columns\TextColumn::make('po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('priceQuotation.quotation_number')
                    ->label('Dari PH')
                    ->placeholder('-'),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'PPN',
                        'secondary' => 'Non-PPN',
                    ]),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'Pending',
                        'primary' => 'Confirmed',
                        'warning' => 'Partial',
                        'success' => 'Completed',
                        'danger' => 'Cancelled',
                    ]),
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
                        'Pending' => 'Pending',
                        'Confirmed' => 'Confirmed',
                        'Partial' => 'Partial',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
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

    protected static function generatePONumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $companyId = session('selected_company_id');
        
        // Get last number from existing records
        $lastRecord = PurchaseOrder::where('company_id', $companyId)
            ->where('po_number', 'like', "PO/{$year}/{$month}/%")
            ->orderBy('po_number', 'desc')
            ->first();
        
        if ($lastRecord) {
            // Extract number from last record (e.g., "PO/2025/10/001" -> 1)
            $parts = explode('/', $lastRecord->po_number);
            $lastNumber = isset($parts[3]) ? (int)$parts[3] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('PO/%s/%s/%03d', $year, $month, $nextNumber);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}