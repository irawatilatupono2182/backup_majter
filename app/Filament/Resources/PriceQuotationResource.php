<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceQuotationResource\Pages;
use App\Models\PriceQuotation;
use App\Models\Supplier;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PriceQuotationResource extends Resource
{
    protected static ?string $model = PriceQuotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Purchasing';

    protected static ?string $navigationLabel = 'Penawaran Harga (PH)';

    protected static ?int $navigationSort = 1;

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
                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => 
                                        $set('subtotal', ((float)$get('qty') * (float)$get('unit_price')) * (1 - (float)$get('discount_percent') / 100))),
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
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),
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