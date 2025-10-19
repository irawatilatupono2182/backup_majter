<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        Forms\Components\TextInput::make('product_code')
                            ->label('Kode Produk')
                            ->required()
                            ->maxLength(50),
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
            ->modifyQueryUsing(fn($query) => $query->where('company_id', session('selected_company_id')))
            ->columns([
                Tables\Columns\TextColumn::make('product_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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