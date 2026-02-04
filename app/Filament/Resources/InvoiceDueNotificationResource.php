<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceDueNotificationResource\Pages;
use App\Models\Invoice;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceDueNotificationResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Notifikasi Jatuh Tempo';
    protected static ?string $navigationGroup = '🔔 Notifikasi';
    
    protected static ?int $navigationSort = 2;
    
    protected static bool $shouldRegisterNavigation = false; // Hidden, accessible via URL
    protected static ?string $slug = 'invoice-due-notifications';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', session('selected_company_id'))
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->where('due_date', '<=', now()->addDays(7));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Notifikasi Invoice Jatuh Tempo (7 Hari ke Depan)')
            ->description('Pantau invoice yang akan atau sudah jatuh tempo')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->colors([
                        'success' => 'PPN',
                        'warning' => 'Non-PPN',
                    ]),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Tanggal Invoice')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('days_status')
                    ->label('Status Waktu')
                    ->getStateUsing(function ($record) {
                        $days = now()->diffInDays($record->due_date, false);
                        if ($days < 0) {
                            return '🔴 ' . abs($days) . ' hari terlambat';
                        } elseif ($days == 0) {
                            return '🟡 Jatuh tempo hari ini!';
                        } elseif ($days <= 3) {
                            return '🟠 ' . $days . ' hari lagi';
                        } else {
                            return '🟢 ' . $days . ' hari lagi';
                        }
                    })
                    ->color(function ($record) {
                        $days = now()->diffInDays($record->due_date, false);
                        if ($days < 0) return 'danger';
                        if ($days == 0) return 'warning';
                        if ($days <= 3) return 'warning';
                        return 'success';
                    })
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Sisa Belum Dibayar')
                    ->getStateUsing(fn($record) => $record->getRemainingAmount())
                    ->money('IDR')
                    ->weight('bold')
                    ->color('danger'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'Unpaid',
                        'info' => 'Partial',
                    ]),
            ])
            ->filters([
                Tables\Filters\Filter::make('overdue')
                    ->label('Sudah Jatuh Tempo')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())),
                Tables\Filters\Filter::make('today')
                    ->label('Jatuh Tempo Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('due_date', now())),
                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('due_date', [now(), now()->addDays(7)])),
            ])
            ->actions([
                Tables\Actions\Action::make('record_payment')
                    ->label('Catat Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->url(fn($record) => route('filament.admin.resources.payments.create', [
                        'invoice_id' => $record->invoice_id
                    ])),
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => route('filament.admin.resources.invoices.view', $record)),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoiceDueNotifications::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('due_date', '<', now())
            ->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
