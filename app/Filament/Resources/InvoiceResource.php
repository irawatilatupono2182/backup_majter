<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoice';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Invoice')
                    ->schema([
                        Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        
                        Hidden::make('created_by')
                            ->default(fn() => auth()->id()),

                        TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->disabled()
                            ->dehydrated(false)
                            ->default('(Auto Generated)')
                            ->helperText('Nomor invoice akan di-generate otomatis saat disimpan'),

                        Select::make('delivery_note_id')
                            ->label('Surat Jalan')
                            ->relationship('deliveryNote', 'sj_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state) {
                                    $deliveryNote = DeliveryNote::find($state);
                                    if ($deliveryNote) {
                                        $set('customer_id', $deliveryNote->customer_id);
                                        $set('invoice_date', now()->format('Y-m-d'));
                                        $set('due_date', now()->addDays(30)->format('Y-m-d'));
                                        
                                        // Set PO Number dari surat jalan (kosongkan jika tidak ada)
                                        $set('po_number', $deliveryNote->po_number ?? null);
                                        
                                        // Set Payment Terms dari TOP surat jalan (default 30 jika tidak ada)
                                        $set('payment_terms', $deliveryNote->top ?? 30);
                                        
                                        // Set PPN based on delivery note type
                                        $ppnIncluded = ($deliveryNote->type === 'PPN');
                                        $set('ppn_included', $ppnIncluded);
                                        
                                        // Set items dari delivery note
                                        $items = $deliveryNote->items->map(function ($item) {
                                            return [
                                                'product_id' => $item->product_id,
                                                'qty' => $item->quantity,
                                                'unit' => $item->unit,
                                                'unit_price' => $item->unit_price,
                                                'subtotal' => $item->subtotal,
                                            ];
                                        })->toArray();
                                        
                                        $set('items', $items);
                                        
                                        // Calculate totals
                                        $subtotalAmount = collect($items)->sum('subtotal');
                                        $set('subtotal_amount', $subtotalAmount);
                                        
                                        $ppnAmount = $ppnIncluded ? $subtotalAmount * 0.11 : 0;
                                        $set('ppn_amount', $ppnAmount);
                                        $set('total_amount', $subtotalAmount + $ppnAmount);
                                    }
                                }
                            }),

                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn(Get $get) => !empty($get('delivery_note_id')))
                            ->dehydrated(), // Ensure value is submitted even when disabled

                        DatePicker::make('invoice_date')
                            ->label('Tanggal Invoice')
                            ->required()
                            ->default(now()),

                        DatePicker::make('due_date')
                            ->label('Tanggal Jatuh Tempo')
                            ->required()
                            ->default(fn() => now()->addDays(30)),

                        TextInput::make('po_number')
                            ->label('PO Number')
                            ->placeholder('Nomor PO dari Customer')
                            ->maxLength(100),

                        TextInput::make('payment_terms')
                            ->label('TOP (Hari)')
                            ->numeric()
                            ->default(30)
                            ->suffix('Hari')
                            ->helperText('Terms of Payment dalam hari'),

                        ToggleButtons::make('ppn_included')
                            ->label('PPN')
                            ->inline()
                            ->options([
                                true => 'Ya (11%)',
                                false => 'Tidak',
                            ])
                            ->default(true)
                            ->disabled(fn(Get $get) => !empty($get('delivery_note_id')))
                            ->helperText('💡 PPN otomatis mengikuti jenis Surat Jalan')
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $subtotal = $get('subtotal_amount') ?? 0;
                                $ppnAmount = $state ? $subtotal * 0.11 : 0;
                                $set('ppn_amount', $ppnAmount);
                                $set('total_amount', $subtotal + $ppnAmount);
                            }),

                        TextInput::make('notes')
                            ->label('Catatan'),
                    ])
                    ->columns(2),

                Section::make('Item Invoice')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            if ($product) {
                                                $set('unit', $product->unit);
                                                $set('unit_price', $product->selling_price);
                                                $qty = (float)($get('qty') ?? 1);
                                                $set('subtotal', $product->selling_price * $qty);
                                            }
                                        }
                                    }),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = (float)($get('unit_price') ?? 0);
                                        $qty = (float)($state ?? 0);
                                        $subtotal = $qty * $unitPrice;
                                        $set('subtotal', $subtotal);
                                    }),

                                TextInput::make('unit')
                                    ->label('Satuan')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $qty = (float)($get('qty') ?? 1);
                                        $unitPrice = (float)($state ?? 0);
                                        $subtotal = $qty * $unitPrice;
                                        $set('subtotal', $subtotal);
                                    }),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(5)
                            ->addActionLabel('Tambah Item')
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $subtotal = collect($state)->sum(function ($item) {
                                    return (float)($item['subtotal'] ?? 0);
                                });
                                $set('subtotal_amount', $subtotal);
                                
                                $ppnIncluded = $get('ppn_included') ?? true;
                                $ppnAmount = $ppnIncluded ? $subtotal * 0.11 : 0;
                                $set('ppn_amount', $ppnAmount);
                                $set('total_amount', $subtotal + $ppnAmount);
                            })
                            ->deleteAction(
                                fn ($action) => $action->after(function (Set $set, Get $get) {
                                    $items = $get('items') ?? [];
                                    $subtotal = collect($items)->sum(function ($item) {
                                        return (float)($item['subtotal'] ?? 0);
                                    });
                                    $set('subtotal_amount', $subtotal);
                                    
                                    $ppnIncluded = $get('ppn_included') ?? true;
                                    $ppnAmount = $ppnIncluded ? $subtotal * 0.11 : 0;
                                    $set('ppn_amount', $ppnAmount);
                                    $set('total_amount', $subtotal + $ppnAmount);
                                })
                            ),
                    ]),

                Section::make('Total')
                    ->schema([
                        TextInput::make('subtotal_amount')
                            ->label('Subtotal')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('ppn_amount')
                            ->label('PPN (11%)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('total_amount')
                            ->label('Total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        ToggleButtons::make('status')
                            ->label('Status Pembayaran')
                            ->inline()
                            ->options([
                                'Unpaid' => 'Belum Lunas',
                                'Partial' => 'Sebagian',
                                'Paid' => 'Lunas',
                                'Overdue' => 'Jatuh Tempo',
                            ])
                            ->default('Unpaid')
                            ->required()
                            ->helperText('💡 Pilih status pembayaran invoice'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deliveryNote.sj_number')
                    ->label('Surat Jalan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Tanggal Invoice')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'Paid' || $state === 'paid') return 'success';
                        if ($state === 'Partial' || $state === 'partial') return 'warning';
                        if ($state === 'Overdue' || $state === 'overdue') return 'danger';
                        return 'gray';
                    })
                    ->formatStateUsing(function (string $state): string {
                        if ($state === 'Paid' || $state === 'paid') return 'Lunas';
                        if ($state === 'Partial' || $state === 'partial') return 'Sebagian';
                        if ($state === 'Overdue' || $state === 'overdue') return 'Jatuh Tempo';
                        return 'Belum Lunas';
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Unpaid' => 'Belum Lunas',
                        'Partial' => 'Sebagian',
                        'Paid' => 'Lunas',
                        'Overdue' => 'Jatuh Tempo',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', session('selected_company_id'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}