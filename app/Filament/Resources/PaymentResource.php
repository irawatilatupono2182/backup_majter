<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pembayaran';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pembayaran')
                    ->schema([
                        Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),

                        Select::make('invoice_id')
                            ->label('Invoice')
                            ->relationship('invoice', 'invoice_number', function (Builder $query) {
                                $companyId = session('selected_company_id');
                                return $query->where('company_id', $companyId)
                                    ->whereIn('status', ['unpaid', 'partial', 'overdue']);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $invoice = Invoice::find($state);
                                    if ($invoice) {
                                        $set('customer_id', $invoice->customer_id);
                                        $set('amount', $invoice->getRemainingAmount());
                                    }
                                }
                            }),

                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name', function (Builder $query) {
                                $companyId = session('selected_company_id');
                                return $query->where('company_id', $companyId);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->dehydrated()
                            ->disabled(function (Get $get) {
                                return !empty($get('invoice_id'));
                            }),

                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->default(now()),

                        TextInput::make('amount')
                            ->label('Jumlah Pembayaran')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),

                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Tunai',
                                'transfer' => 'Transfer Bank',
                                'qris' => 'QRIS',
                                'check' => 'Cek',
                                'credit_card' => 'Kartu Kredit',
                            ])
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Nomor Referensi')
                            ->helperText('Contoh: Nomor transfer, kode QRIS, dll'),

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
            ->columns([
                TextColumn::make('invoice.invoice_number')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->formatStateUsing(function (string $state): string {
                        $methods = [
                            'cash' => 'Tunai',
                            'transfer' => 'Transfer Bank',
                            'qris' => 'QRIS',
                            'check' => 'Cek',
                            'credit_card' => 'Kartu Kredit',
                        ];
                        return $methods[$state] ?? $state;
                    }),

                TextColumn::make('reference_number')
                    ->label('Nomor Ref')
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
                SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer Bank',
                        'qris' => 'QRIS',
                        'check' => 'Cek',
                        'credit_card' => 'Kartu Kredit',
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', session('selected_company_id'));
    }
}