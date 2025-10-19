<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryNoteResource\Pages;
use App\Models\DeliveryNote;
use App\Models\Customer;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Surat Jalan (SJ)';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Surat Jalan')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->id()),
                        Forms\Components\TextInput::make('sj_number')
                            ->label('Nomor SJ')
                            ->required()
                            ->default(fn() => self::generateSJNumber())
                            ->maxLength(50),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn() => Customer::where('company_id', session('selected_company_id'))
                                ->where('is_active', true)
                                ->pluck('name', 'customer_id'))
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    $set('type', $customer && $customer->is_ppn ? 'PPN' : 'Non-PPN');
                                }
                            }),
                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'PPN' => 'PPN',
                                'Non-PPN' => 'Non-PPN',
                                'Supplier' => 'Supplier',
                            ])
                            ->required()
                            ->default('PPN'),
                        Forms\Components\DatePicker::make('delivery_date')
                            ->label('Tanggal Kirim')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Draft' => 'Draft',
                                'Sent' => 'Sent',
                                'Completed' => 'Completed',
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
                Tables\Columns\TextColumn::make('sj_number')
                    ->label('Nomor SJ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'PPN',
                        'secondary' => 'Non-PPN',
                        'warning' => 'Supplier',
                    ]),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Tanggal Kirim')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'Draft',
                        'primary' => 'Sent',
                        'success' => 'Completed',
                    ]),
                Tables\Columns\IconColumn::make('has_invoice')
                    ->label('Invoice')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->hasInvoice()),
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
                        'Completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'PPN' => 'PPN',
                        'Non-PPN' => 'Non-PPN',
                        'Supplier' => 'Supplier',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('create_invoice')
                    ->label('Buat Invoice')
                    ->icon('heroicon-o-document-plus')
                    ->visible(fn($record) => !$record->hasInvoice() && $record->status === 'Completed')
                    ->url(fn($record) => route('filament.admin.resources.invoices.create', ['sj_id' => $record->sj_id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    protected static function generateSJNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $companyId = session('selected_company_id');
        
        // Get last number from existing records
        $lastRecord = DeliveryNote::where('company_id', $companyId)
            ->where('sj_number', 'like', "SJ/{$year}/{$month}/%")
            ->orderBy('sj_number', 'desc')
            ->first();
        
        if ($lastRecord) {
            // Extract number from last record (e.g., "SJ/2025/10/001" -> 1)
            $parts = explode('/', $lastRecord->sj_number);
            $lastNumber = isset($parts[3]) ? (int)$parts[3] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('SJ/%s/%s/%03d', $year, $month, $nextNumber);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryNotes::route('/'),
            'create' => Pages\CreateDeliveryNote::route('/create'),
            'view' => Pages\ViewDeliveryNote::route('/{record}'),
            'edit' => Pages\EditDeliveryNote::route('/{record}/edit'),
        ];
    }
}