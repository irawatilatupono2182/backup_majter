<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Stock;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DueNotificationsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $companyId = session('selected_company_id');

        return $table
            ->heading('🔔 Notifikasi Penting')
            ->query(
                Invoice::query()
                    ->where('company_id', $companyId)
                    ->whereIn('status', ['Unpaid', 'Partial'])
                    ->where('due_date', '<=', now()->addDays(7))
            )
            ->columns([
                Tables\Columns\TextColumn::make('notification_type')
                    ->label('Jenis')
                    ->getStateUsing(fn() => 'Piutang Jatuh Tempo')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Sisa Waktu')
                    ->getStateUsing(function ($record) {
                        $days = now()->diffInDays($record->due_date, false);
                        if ($days < 0) {
                            return abs($days) . ' hari terlambat';
                        } elseif ($days == 0) {
                            return 'Hari ini!';
                        } else {
                            return $days . ' hari lagi';
                        }
                    })
                    ->color(function ($record) {
                        $days = now()->diffInDays($record->due_date, false);
                        if ($days < 0) return 'danger';
                        if ($days <= 3) return 'warning';
                        return 'success';
                    }),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Jumlah')
                    ->money('IDR'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.invoices.view', $record)),
            ]);
    }
}
