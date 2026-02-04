<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchasePaymentResource\Pages;
use App\Models\PurchasePayment;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchasePaymentResource extends Resource
{
    protected static ?string $model = PurchasePayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Pembayaran ke Supplier';
    
    protected static ?string $pluralModelLabel = 'Pembayaran Hutang';
    
    protected static ?string $modelLabel = 'Pembayaran Hutang';
    
    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static ?int $navigationSort = 3;

    public static function getNavigationTooltip(): ?string
    {
        return 'Catat pembayaran hutang ke supplier';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->with(['purchaseOrder', 'supplier']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Forms\Components\Select::make('po_id')
                            ->label('Purchase Order')
                            ->options(function() {
                                $companyId = session('selected_company_id');
                                return PurchaseOrder::where('company_id', $companyId)
                                    ->whereIn('payment_status', ['unpaid', 'partial'])
                                    ->get()
                                    ->mapWithKeys(function($po) {
                                        $remaining = $po->getRemainingAmount();
                                        return [$po->po_id => "{$po->po_number} - {$po->supplier->name} - Sisa: Rp " . number_format($remaining, 0, ',', '.')];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $po = PurchaseOrder::find($state);
                                    if ($po) {
                                        $set('supplier_id', $po->supplier_id);
                                        $set('amount', $po->getRemainingAmount());
                                    }
                                }
                            }),
                        
                        Forms\Components\Hidden::make('supplier_id'),
                        
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Pembayaran')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->helperText('Masukkan jumlah pembayaran dalam Rupiah'),
                        
                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Tunai',
                                'transfer' => 'Transfer Bank',
                                'check' => 'Cek',
                                'giro' => 'Giro',
                                'other' => 'Lainnya',
                            ])
                            ->required()
                            ->default('transfer'),
                        
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Nomor Referensi')
                            ->helperText('Nomor bukti transfer, nomor cek, dll')
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query) {
                $companyId = session('selected_company_id');
                return $query->when($companyId, fn($q) => $q->where('company_id', $companyId));
            })
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'check' => 'Cek',
                        'giro' => 'Giro',
                        'other' => 'Lainnya',
                        default => $state
                    })
                    ->color(fn($state) => match($state) {
                        'cash' => 'success',
                        'transfer' => 'info',
                        'check' => 'warning',
                        'giro' => 'warning',
                        default => 'gray'
                    }),
                
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('No. Ref')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('payment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer Bank',
                        'check' => 'Cek',
                        'giro' => 'Giro',
                        'other' => 'Lainnya',
                    ]),
                
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchasePayments::route('/'),
            'create' => Pages\CreatePurchasePayment::route('/create'),
            'view' => Pages\ViewPurchasePayment::route('/{record}'),
            'edit' => Pages\EditPurchasePayment::route('/{record}/edit'),
        ];
    }
}
