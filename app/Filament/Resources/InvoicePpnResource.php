<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoicePpnResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicePpnResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = ' Invoice PPN';
    protected static ?string $navigationGroup = '� Penjualan';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'invoice-ppn';

    public static function getNavigationTooltip(): ?string
    {
        return 'Invoice dengan PPN 11%';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->where('type', 'PPN');
    }

    public static function form(Form $form): Form
    {
        return InvoiceResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query) {
                $companyId = session('selected_company_id');
                return $query
                    ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                    ->where('type', 'PPN');
            })
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : null),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'Unpaid',
                        'info' => 'Partial',
                        'success' => 'Paid',
                        'danger' => 'Overdue',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Unpaid' => 'Belum Dibayar',
                        'Partial' => 'Sebagian',
                        'Paid' => 'Lunas',
                        'Overdue' => 'Jatuh Tempo',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoicePpn::route('/'),
            'create' => Pages\CreateInvoicePpn::route('/create'),
            'view' => Pages\ViewInvoicePpn::route('/{record}'),
            'edit' => Pages\EditInvoicePpn::route('/{record}/edit'),
        ];
    }
}
