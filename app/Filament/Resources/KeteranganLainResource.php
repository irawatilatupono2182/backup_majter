<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeteranganLainResource\Pages;
use App\Models\KeteranganLain;
use App\Models\Customer;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class KeteranganLainResource extends Resource
{
    protected static ?string $model = KeteranganLain::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Keterangan Lain';
    protected static ?string $navigationGroup = '💼 Penjualan';
    protected static ?int $navigationSort = 6;

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
        return 'info';
    }

    public static function getNavigationTooltip(): ?string
    {
        return 'Dokumen keterangan lain untuk penjualan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->id()),

                        Forms\Components\Hidden::make('type')
                            ->default(function () {
                                $type = session('keterangan_lain_type_create');
                                return $type ?: 'PPN';
                            }),

                        Forms\Components\TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->disabled()
                            ->dehydrated(false)
                            ->default('(Auto Generated)')
                            ->helperText('Nomor akan di-generate otomatis'),

                        Forms\Components\Select::make('document_category')
                            ->label('Kategori Dokumen')
                            ->options([
                                'Surat Jalan Tambahan' => 'Surat Jalan Tambahan',
                                'Nota Pengganti' => 'Nota Pengganti',
                                'Dokumen Koreksi' => 'Dokumen Koreksi',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->default('Lainnya'),

                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn() => Customer::where('company_id', session('selected_company_id'))
                                ->where('is_active', true)
                                ->pluck('name', 'customer_id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('reference_document')
                            ->label('Referensi Dokumen')
                            ->placeholder('Nomor dokumen yang direferensikan')
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('document_date')
                            ->label('Tanggal Dokumen')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Draft' => 'Draft',
                                'Approved' => 'Approved',
                                'Closed' => 'Closed',
                            ])
                            ->required()
                            ->default('Draft'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Jelaskan tujuan dan isi dokumen ini')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Item Dokumen')
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

                                Forms\Components\Textarea::make('notes')
                                    ->label('Keterangan Item')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Item')
                            ->reorderable(false)
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Infolists\Components\TextEntry::make('document_number')
                            ->label('Nomor Dokumen')
                            ->copyable(),
                        
                        Infolists\Components\TextEntry::make('document_category')
                            ->label('Kategori')
                            ->badge()
                            ->color('info'),
                        
                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('Customer'),
                        
                        Infolists\Components\TextEntry::make('type')
                            ->label('Jenis')
                            ->badge()
                            ->color(fn($state) => $state === 'PPN' ? 'success' : 'gray'),
                        
                        Infolists\Components\TextEntry::make('document_date')
                            ->label('Tanggal Dokumen')
                            ->date('d/m/Y'),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn($state) => match($state) {
                                'Draft' => 'warning',
                                'Approved' => 'success',
                                'Closed' => 'danger',
                                default => 'gray'
                            }),
                        
                        Infolists\Components\TextEntry::make('reference_document')
                            ->label('Referensi Dokumen')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('reference_info')
                            ->label('Dokumen Terkait')
                            ->formatStateUsing(function ($record) {
                                if (!$record->reference_type || !$record->reference_id) {
                                    return '-';
                                }
                                
                                $doc = $record->getReferenceDocument();
                                if (!$doc) return 'Dokumen tidak ditemukan';
                                
                                $type = match($record->reference_type) {
                                    'App\\Models\\Invoice' => '📄 Invoice',
                                    'App\\Models\\NotaMenyusul' => '📝 Nota Menyusul',
                                    'App\\Models\\DeliveryNote' => '🚚 Surat Jalan',
                                    default => 'Dokumen'
                                };
                                
                                $number = $doc->invoice_number ?? $doc->nota_number ?? $doc->sj_number ?? 'N/A';
                                return "{$type}: {$number}";
                            })
                            ->badge()
                            ->color('primary')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->placeholder('-'),
                        
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Item Dokumen')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Produk'),
                                Infolists\Components\TextEntry::make('qty')
                                    ->label('Qty'),
                                Infolists\Components\TextEntry::make('unit')
                                    ->label('Satuan'),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('Harga')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Keterangan')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(5),
                    ]),

                Infolists\Components\Section::make('Total')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('ppn_amount')
                            ->label('PPN (11%)')
                            ->money('IDR')
                            ->visible(fn($record) => $record->type === 'PPN'),
                        Infolists\Components\TextEntry::make('grand_total')
                            ->label('Grand Total')
                            ->money('IDR')
                            ->weight('bold')
                            ->size('lg'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('company_id', session('selected_company_id')))
            ->columns([
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Nomor Dokumen')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('document_category')
                    ->label('Kategori')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference_document')
                    ->label('Referensi')
                    ->searchable()
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
                        'danger' => 'Closed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Approved' => 'Approved',
                        'Closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('document_category')
                    ->options([
                        'Surat Jalan Tambahan' => 'Surat Jalan Tambahan',
                        'Nota Pengganti' => 'Nota Pengganti',
                        'Dokumen Koreksi' => 'Dokumen Koreksi',
                        'Lainnya' => 'Lainnya',
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
            'index' => Pages\ListKeteranganLains::route('/'),
            'create' => Pages\CreateKeteranganLain::route('/create'),
            'view' => Pages\ViewKeteranganLain::route('/{record}'),
            'edit' => Pages\EditKeteranganLain::route('/{record}/edit'),
        ];
    }
}
