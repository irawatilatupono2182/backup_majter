<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotaMenyusulResource\Pages;
use App\Models\NotaMenyusul;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotaMenyusulResource extends Resource
{
    protected static ?string $model = NotaMenyusul::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Nota Menyusul';
    protected static ?string $navigationGroup = '💼 Penjualan';
    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        return static::getModel()::where('company_id', $companyId)
            ->where('status', 'Draft')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationTooltip(): ?string
    {
        return 'Nota penjualan dengan pembayaran menyusul';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Nota Menyusul')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->id()),

                        Forms\Components\Hidden::make('type')
                            ->default(function () {
                                $type = session('nota_menyusul_type_create');
                                return $type ?: 'PPN';
                            }),

                        Forms\Components\TextInput::make('nota_number')
                            ->label('Nomor Nota')
                            ->disabled()
                            ->dehydrated(false)
                            ->default('(Auto Generated)')
                            ->helperText('Nomor akan di-generate otomatis'),

                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn() => Customer::where('company_id', session('selected_company_id'))
                                ->where('is_active', true)
                                ->pluck('name', 'customer_id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('sj_id')
                            ->label('Referensi Surat Jalan (Opsional)')
                            ->options(fn() => DeliveryNote::where('company_id', session('selected_company_id'))
                                ->orderBy('sj_number', 'desc')
                                ->pluck('sj_number', 'sj_id'))
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('nota_date')
                            ->label('Tanggal Nota')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('estimated_payment_date')
                            ->label('Estimasi Tanggal Pembayaran')
                            ->after('nota_date')
                            ->helperText('Perkiraan kapan pembayaran akan dilakukan'),

                        Forms\Components\TextInput::make('po_number')
                            ->label('PO Number')
                            ->placeholder('Nomor PO dari Customer')
                            ->maxLength(100),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Draft' => 'Draft',
                                'Approved' => 'Approved',
                                'Converted' => 'Converted to Invoice',
                            ])
                            ->required()
                            ->default('Draft'),

                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Catatan Pembayaran')
                            ->rows(2)
                            ->placeholder('Informasi tambahan tentang pembayaran menyusul'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2),
                    ])->columns(2),

                Forms\Components\Section::make('Item Nota')
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
                                            if ($product) {
                                                $set('unit', $product->unit);
                                                $set('unit_price', $product->base_price);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => 
                                        $set('subtotal', (float)$state * (float)$get('unit_price'))),

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
                                        $set('subtotal', (float)$get('qty') * (float)$state)),

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
                Tables\Columns\TextColumn::make('nota_number')
                    ->label('Nomor Nota')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nota_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_payment_date')
                    ->label('Est. Bayar')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'PPN',
                        'gray' => 'Non-PPN',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'Draft',
                        'success' => 'Approved',
                        'info' => 'Converted',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Approved' => 'Approved',
                        'Converted' => 'Converted',
                    ]),
                Tables\Filters\SelectFilter::make('type')
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
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotaMenyusuls::route('/'),
            'create' => Pages\CreateNotaMenyusul::route('/create'),
            'view' => Pages\ViewNotaMenyusul::route('/{record}'),
            'edit' => Pages\EditNotaMenyusul::route('/{record}/edit'),
        ];
    }
}
