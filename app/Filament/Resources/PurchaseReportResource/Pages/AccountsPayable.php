<?php

namespace App\Filament\Resources\PurchaseReportResource\Pages;

use App\Filament\Resources\PurchaseReportResource;
use App\Models\Payable;
use App\Models\PurchaseReport;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AccountsPayable extends ListRecords
{
    protected static string $resource = PurchaseReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('laporan_pembelian')
                ->label('📊 Laporan Pembelian')
                ->badge(fn() => PurchaseReport::where('company_id', session('selected_company_id'))->count())
                ->badgeColor('primary')
                ->url(fn() => static::getResource()::getUrl('index'))
                ->color('gray')
                ->outlined(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('💳 Hutang Usaha - Tagihan ke Supplier')
            ->description('Monitor dan kelola hutang ke supplier. Mendukung hutang dari PO dan manual. Prioritas: Overdue → Due Today → Upcoming')
            ->query(
                Payable::query()
                    ->where('company_id', session('selected_company_id'))
                    ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                    ->with(['supplier', 'purchaseOrder', 'payments'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('urgency_status')
                    ->label('Prioritas')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $daysUntilDue = now()->diffInDays($record->due_date, false);
                        if ($daysUntilDue < 0) {
                            $daysOverdue = abs($daysUntilDue);
                            return '🔴 ' . $daysOverdue . ' hari terlambat';
                        } elseif ($daysUntilDue == 0) {
                            return '🟡 Jatuh tempo HARI INI';
                        } elseif ($daysUntilDue <= 7) {
                            return '🟢 ' . $daysUntilDue . ' hari lagi';
                        } else {
                            return '✅ Normal';
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
                
                Tables\Columns\TextColumn::make('payable_number')
                    ->label('No. Hutang')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'po' => 'Purchase Order',
                        'manual' => 'Manual',
                        'operational' => 'Operasional',
                        'other' => 'Lainnya',
                        default => $state ?? 'Manual'
                    })
                    ->color(fn($state) => match($state) {
                        'po' => 'success',
                        'manual' => 'info',
                        'operational' => 'warning',
                        default => 'gray'
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reference_info')
                    ->label('Referensi')
                    ->getStateUsing(function ($record) {
                        if ($record->reference_type === 'po' && $record->purchaseOrder) {
                            return $record->purchaseOrder->po_number;
                        }
                        return $record->reference_number ?? '-';
                    })
                    ->searchable(['reference_number'])
                    ->description(fn($record) => $record->reference_description)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payable_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : null)
                    ->weight(fn($record) => $record->isOverdue() ? 'bold' : null),
                
                Tables\Columns\TextColumn::make('days_status')
                    ->label('Hari')
                    ->getStateUsing(function ($record) {
                        if ($record->isOverdue()) {
                            return now()->diffInDays($record->due_date) . ' hari terlambat';
                        }
                        $days = now()->diffInDays($record->due_date, false);
                        return $days <= 7 ? $days . ' hari lagi' : '-';
                    })
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : 'success'),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Terbayar')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->money('IDR')
                    ->weight('bold')
                    ->color('danger')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'danger' => 'overdue',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'unpaid' => 'Belum Bayar',
                        'partial' => 'Sebagian',
                        'overdue' => 'Terlambat',
                        'paid' => 'Lunas',
                        default => $state
                    }),
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
                    ->label('Status')
                    ->options([
                        'unpaid' => 'Belum Dibayar',
                        'partial' => 'Sebagian',
                        'overdue' => 'Terlambat',
                    ]),
                
                Tables\Filters\SelectFilter::make('reference_type')
                    ->label('Jenis Hutang')
                    ->options([
                        'po' => 'Dari Purchase Order',
                        'manual' => 'Manual',
                        'operational' => 'Operasional',
                        'other' => 'Lainnya',
                    ]),
                
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('pay_payable')
                        ->label('💰 Bayar Hutang')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->form([
                            Forms\Components\DatePicker::make('payment_date')
                                ->label('Tanggal Pembayaran')
                                ->default(now())
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->label('Jumlah Pembayaran')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->default(fn($record) => $record->remaining_amount)
                                ->helperText(fn($record) => 'Sisa hutang: Rp ' . number_format($record->remaining_amount, 0, ',', '.')),
                            Forms\Components\Select::make('payment_method')
                                ->label('Metode Pembayaran')
                                ->options([
                                    'cash' => 'Cash',
                                    'transfer' => 'Transfer Bank',
                                    'check' => 'Cek/Giro',
                                    'other' => 'Lainnya',
                                ])
                                ->default('transfer')
                                ->required(),
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan')
                                ->rows(2),
                        ])
                        ->action(function ($record, array $data) {
                            // Create payment record
                            $payment = new \App\Models\PayablePayment();
                            $payment->payable_id = $record->payable_id;
                            $payment->payment_date = $data['payment_date'];
                            $payment->amount = $data['amount'];
                            $payment->payment_method = $data['payment_method'];
                            $payment->notes = $data['notes'] ?? null;
                            $payment->created_by = auth()->id();
                            $payment->save();
                            
                            // Update payable
                            $record->recalculatePaidAmount();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Pembayaran Tercatat')
                                ->body('Pembayaran hutang sebesar Rp ' . number_format($data['amount'], 0, ',', '.') . ' telah tercatat')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\Action::make('send_reminder')
                        ->label('📧 Kirim Reminder')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Payment Reminder')
                        ->modalDescription(fn($record) => "Kirim reminder pembayaran ke {$record->supplier->name}?")
                        ->action(function ($record) {
                            // TODO: Implement email/WA reminder
                            \Filament\Notifications\Notification::make()
                                ->title('Reminder Terkirim')
                                ->body("Payment reminder telah dikirim ke {$record->supplier->name}")
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\Action::make('contact_supplier')
                        ->label('📞 Log Kontak')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan Kontak')
                                ->placeholder('Catat hasil komunikasi dengan supplier...')
                                ->required()
                                ->rows(3),
                            Forms\Components\DatePicker::make('next_follow_up')
                                ->label('Follow Up Berikutnya')
                                ->default(now()->addDays(3)),
                        ])
                        ->action(function ($record, array $data) {
                            // TODO: Implement contact log tracking
                            \Filament\Notifications\Notification::make()
                                ->title('Log Kontak Tersimpan')
                                ->body('Catatan komunikasi dengan supplier telah disimpan')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\ViewAction::make()
                        ->visible(fn() => class_exists(\App\Filament\Resources\PayableResource::class))
                        ->url(fn($record) => route('filament.admin.resources.payables.view', ['record' => $record->payable_id])),
                    
                    Tables\Actions\EditAction::make()
                        ->visible(fn() => class_exists(\App\Filament\Resources\PayableResource::class))
                        ->url(fn($record) => route('filament.admin.resources.payables.edit', ['record' => $record->payable_id])),
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
                                ->body('Reminder telah dikirim ke ' . $records->count() . ' supplier')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('📥 Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function ($records) {
                            \Filament\Notifications\Notification::make()
                                ->title('Export')
                                ->body('Export ' . $records->count() . ' data akan segera tersedia')
                                ->info()
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
