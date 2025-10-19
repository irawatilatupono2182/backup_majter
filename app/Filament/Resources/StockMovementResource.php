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
    protected static ?string $navigationLabel = 'Stock Movement';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Stock Movement')
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

                        Select::make('movement_type')
                            ->label('Tipe Movement')
                            ->options([
                                'in' => 'Stock Masuk',
                                'out' => 'Stock Keluar',
                                'adjustment' => 'Adjustment',
                            ])
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Qty')
                            ->numeric()
                            ->required(),

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
                $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
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
        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
        return parent::getEloquentQuery()
            ->where('company_id', $companyId);
    }
}