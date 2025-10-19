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
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Stok Barang';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Stok')
                    ->schema([
                        Hidden::make('company_id')
                            ->default(function () {
                                return auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
                            }),

                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name', function (Builder $query) {
                                $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
                                return $query->where('company_id', $companyId)
                                    ->where('product_type', 'STOCK');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

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
                $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
                return $query->where('company_id', $companyId);
            })
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

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

                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('location')
                    ->label('Lokasi')
                    ->options(function () {
                        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
                        return Stock::where('company_id', $companyId)
                            ->whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location')
                            ->toArray();
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'view' => Pages\ViewStock::route('/{record}'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
        return parent::getEloquentQuery()
            ->where('company_id', $companyId);
    }
}