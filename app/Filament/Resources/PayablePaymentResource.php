<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayablePaymentResource\Pages;
use App\Models\PayablePayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class PayablePaymentResource extends Resource
{
    protected static ?string $model = PayablePayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationLabel = 'Pembayaran Hutang';
    
    protected static ?string $pluralModelLabel = 'Pembayaran Hutang';
    
    protected static ?string $modelLabel = 'Pembayaran Hutang';
    
    protected static ?string $navigationGroup = '💰 Keuangan';
    
    protected static ?int $navigationSort = 4;
    
    public static function shouldRegisterNavigation(): bool
    {
        // Opsional: hide dari navigasi karena sudah ada di relation manager
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->with(['payable.supplier']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form schema sama seperti di RelationManager
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('payment_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('No. Pembayaran')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('payable.payable_number')
                    ->label('No. Hutang')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.payables.view', ['record' => $record->payable_id]))
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('payable.supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront'),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->money('IDR')
                        ->label('Total')),
                
                Tables\Columns\TextColumn::make('payment_method_label')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('-'),
                
                Tables\Columns\IconColumn::make('attachment_path')
                    ->label('Bukti')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => '💵 Tunai',
                        'transfer' => '🏦 Transfer Bank',
                        'check' => '📝 Cek',
                        'giro' => '📋 Giro',
                        'other' => '➕ Lainnya',
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
                                fn(Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    
                    Tables\Actions\Action::make('download_attachment')
                        ->label('Download Bukti')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->visible(fn($record) => $record->attachment_path !== null)
                        ->action(function ($record) {
                            if ($record->attachment_path && Storage::disk('public')->exists($record->attachment_path)) {
                                return Storage::disk('public')->download(
                                    $record->attachment_path,
                                    $record->attachment_filename ?? basename($record->attachment_path)
                                );
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum ada pembayaran hutang')
            ->emptyStateDescription('Pembayaran hutang akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayablePayments::route('/'),
            'view' => Pages\ViewPayablePayment::route('/{record}'),
        ];
    }
}
