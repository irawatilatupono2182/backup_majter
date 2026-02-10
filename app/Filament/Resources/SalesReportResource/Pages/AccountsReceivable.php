<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountsReceivable extends ListRecords
{
    protected static string $resource = SalesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('laporan_penjualan')
                ->label('📊 Laporan Penjualan')
                ->badge(fn() => Invoice::where('company_id', session('selected_company_id'))->count())
                ->badgeColor('primary')
                ->url(fn() => static::getResource()::getUrl('index'))
                ->color('gray')
                ->outlined(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('💰 Piutang Usaha - Tagihan Belum Lunas')
            ->description('Monitor dan kelola piutang dari customer. Prioritas: Overdue → Due Today → Upcoming')
            ->query(
                Invoice::query()
                    ->where('company_id', session('selected_company_id'))
                    ->whereIn('status', ['Unpaid', 'Partial', 'Overdue'])
                    ->with(['customer', 'payments'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('urgency_status')
                    ->label('Prioritas')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $daysUntilDue = now()->diffInDays($record->due_date, false);
                        if ($daysUntilDue < 0) {
                            $daysOverdue = abs($daysUntilDue);
                            return $daysOverdue . ' hari terlambat';
                        } elseif ($daysUntilDue == 0) {
                            return 'Jatuh tempo HARI INI';
                        } elseif ($daysUntilDue <= 7) {
                            return $daysUntilDue . ' hari lagi';
                        } else {
                            return 'Normal';
                        }
                    })
                    ->color(fn($record) => match(true) {
                        $record->isOverdue() => 'danger',
                        now()->diffInDays($record->due_date, false) == 0 => 'warning',
                        now()->diffInDays($record->due_date, false) <= 7 => 'info',
                        default => 'success'
                    })
                    ->icon(fn($record) => match(true) {
                        $record->isOverdue() => 'heroicon-o-exclamation-circle',
                        now()->diffInDays($record->due_date, false) == 0 => 'heroicon-o-clock',
                        default => 'heroicon-o-check-circle'
                    })
                    ->sortable()
                    ->weight('bold'),
                
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
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn($record) => $record->isOverdue() ? 'bold' : null),
                
                Tables\Columns\TextColumn::make('days_overdue')
                    ->label('Hari Terlambat')
                    ->getStateUsing(function ($record) {
                        if ($record->isOverdue()) {
                            return now()->diffInDays($record->due_date) . ' hari';
                        }
                        return '-';
                    })
                    ->color('danger'),
                
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Terbayar')
                    ->getStateUsing(fn($record) => $record->getTotalPaid())
                    ->money('IDR'),
                
                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->getStateUsing(fn($record) => $record->getRemainingAmount())
                    ->money('IDR')
                    ->weight('bold')
                    ->color('danger'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'Unpaid',
                        'info' => 'Partial',
                        'danger' => 'Overdue',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('urgency')
                    ->label('Filter Prioritas')
                    ->options([
                        'overdue' => '🔴 Overdue (Terlambat)',
                        'today' => '🟡 Due Today (Hari Ini)',
                        'this_week' => '🟢 This Week (7 Hari)',
                        'all' => 'Semua',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        return match($value) {
                            'overdue' => $query->where('due_date', '<', now()),
                            'today' => $query->whereDate('due_date', now()),
                            'this_week' => $query->whereBetween('due_date', [now(), now()->addDays(7)]),
                            default => $query
                        };
                    })
                    ->default('all'),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Unpaid' => 'Belum Dibayar',
                        'Partial' => 'Sebagian',
                        'Overdue' => 'Jatuh Tempo',
                    ]),
                
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'PPN' => 'PPN',
                        'Non-PPN' => 'Non-PPN',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('record_payment')
                        ->label('💰 Catat Pembayaran')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->url(fn($record) => route('filament.admin.resources.payments.create', [
                            'invoice_id' => $record->invoice_id
                        ])),
                    
                    Tables\Actions\Action::make('send_reminder')
                        ->label('📧 Kirim Reminder')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Payment Reminder')
                        ->modalDescription(fn($record) => "Kirim reminder pembayaran ke {$record->customer->name}?")
                        ->action(function ($record) {
                            \Filament\Notifications\Notification::make()
                                ->title('Reminder Terkirim')
                                ->body("Payment reminder telah dikirim ke {$record->customer->name}")
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\Action::make('call_customer')
                        ->label('📞 Log Telepon')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan Telepon')
                                ->placeholder('Catat hasil telepon dengan customer...')
                                ->required()
                                ->rows(3),
                            Forms\Components\DatePicker::make('next_follow_up')
                                ->label('Follow Up Berikutnya')
                                ->default(now()->addDays(3)),
                        ])
                        ->action(function ($record, array $data) {
                            \Filament\Notifications\Notification::make()
                                ->title('Call Log Tersimpan')
                                ->body('Catatan telepon dengan customer telah disimpan')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\ViewAction::make()
                        ->url(fn($record) => route('filament.admin.resources.invoices.view', ['record' => $record->invoice_id])),
                ])
                ->button()
                ->label('Actions')
                ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('send_bulk_reminder')
                        ->label('📧 Kirim Reminder Massal')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            \Filament\Notifications\Notification::make()
                                ->title('Reminder Massal Terkirim')
                                ->body('Reminder telah dikirim ke ' . $records->count() . ' customer')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('due_date', 'asc')
            ->poll('30s')
            ->striped()
            ->persistFiltersInSession();
    }
}
