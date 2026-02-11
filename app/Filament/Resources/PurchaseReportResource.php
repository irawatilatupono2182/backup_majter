<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseReportResource\Pages;
use App\Models\PurchaseReport;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseReportResource extends Resource
{
    protected static ?string $model = PurchaseReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Laporan Pembelian';
    protected static ?string $navigationGroup = '📈 Laporan';
    
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'purchase-report';

    public static function getNavigationTooltip(): ?string
    {
        return 'Laporan pembelian dan pengeluaran';
    }

    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) {
            return null;
        }
        
        // Count payables that are overdue
        $count = \App\Models\Payable::where('company_id', $companyId)
            ->where('due_date', '<', now())
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->count();
        
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
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
            ->columns([
                TextColumn::make('order_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(function ($record) {
                        $daysAgo = now()->diffInDays($record->order_date);
                        return $daysAgo > 0 ? "🕐 {$daysAgo} hari lalu" : '🆕 Hari ini';
                    }),

                TextColumn::make('po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->description(function ($record) {
                        $totalItems = $record->items->count();
                        $totalQty = $record->items->sum('qty_ordered');
                        return "📦 {$totalItems} item ({$totalQty} unit)";
                    }),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->description(function ($record) {
                        $supplierType = $record->supplier && $record->supplier->type === 'Local' ? '🏭 Lokal' : '🌍 Import';
                        $ppnType = $record->type === 'PPN' ? '✅ PPN' : '❌ Non-PPN';
                        return "{$supplierType} | {$ppnType}";
                    }),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($state) => $state === 'PPN' ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state === 'PPN' ? '✅ PPN' : '❌ Non-PPN')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('items_sum_subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->getStateUsing(function ($record) {
                        return $record->items->sum('subtotal');
                    })
                    ->summarize([
                        Sum::make()->money('IDR')->label('Total Subtotal')
                    ]),

                TextColumn::make('ppn_amount')
                    ->label('PPN')
                    ->money('IDR')
                    ->getStateUsing(function ($record) {
                        $subtotal = $record->items->sum('subtotal');
                        return $record->type === 'PPN' ? $subtotal * 0.11 : 0;
                    })
                    ->summarize([
                        Sum::make()->money('IDR')->label('Total PPN')
                    ])
                    ->toggleable(),

                TextColumn::make('grand_total_calculated')
                    ->label('Grand Total')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->getGrandTotal())
                    ->weight('bold')
                    ->summarize([
                        Sum::make()->money('IDR')->label('Total Pembelian')
                    ]),

                TextColumn::make('status')
                    ->label('Status PO')
                    ->badge()
                    ->color(function ($state) {
                        return match($state) {
                            'Completed' => 'success',
                            'Confirmed' => 'info',
                            'Received' => 'warning',
                            'Cancelled' => 'danger',
                            default => 'gray'
                        };
                    })
                    ->icon(function ($state) {
                        return match($state) {
                            'Completed' => 'heroicon-o-check-circle',
                            'Confirmed' => 'heroicon-o-clock',
                            'Received' => 'heroicon-o-truck',
                            'Cancelled' => 'heroicon-o-x-circle',
                            default => 'heroicon-o-question-mark-circle'
                        };
                    })
                    ->sortable(),

                TextColumn::make('payment_info')
                    ->label('Pembayaran')
                    ->getStateUsing(function ($record) {
                        $payable = \App\Models\Payable::where('po_id', $record->po_id)->first();
                        if (!$payable) return 'Belum Ada Data';
                        
                        $paid = $payable->paid_amount ?? 0;
                        $total = $payable->total_amount ?? 0;
                        $remaining = $total - $paid;
                        
                        if ($payable->status === 'paid') {
                            return '✅ Lunas';
                        } elseif ($payable->status === 'partial') {
                            $percentage = $total > 0 ? round(($paid / $total) * 100) : 0;
                            return "⚠️ {$percentage}% (Sisa: Rp " . number_format($remaining, 0, ',', '.') . ')';
                        } else {
                            if ($payable->due_date && $payable->due_date < now()) {
                                $daysOverdue = now()->diffInDays($payable->due_date);
                                return "🚨 Jatuh Tempo ({$daysOverdue} hari)";
                            }
                            return '⏳ Belum Lunas';
                        }
                    })
                    ->badge()
                    ->color(function ($record) {
                        $payable = \App\Models\Payable::where('po_id', $record->po_id)->first();
                        if (!$payable) return 'gray';
                        
                        if ($payable->status === 'paid') return 'success';
                        if ($payable->status === 'partial') return 'warning';
                        if ($payable->due_date && $payable->due_date < now()) return 'danger';
                        return 'gray';
                    })
                    ->description(function ($record) {
                        $payable = \App\Models\Payable::where('po_id', $record->po_id)->first();
                        if (!$payable || !$payable->due_date) return null;
                        
                        return '📅 Jatuh tempo: ' . $payable->due_date->format('d/m/Y');
                    }),
            ])
            ->defaultSort('order_date', 'desc')
            ->filters([
                Filter::make('order_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),

                SelectFilter::make('status')
                    ->label('Status PO')
                    ->options([
                        'Pending' => 'Pending',
                        'Confirmed' => 'Confirmed',
                        'Received' => 'Received',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Lunas',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                    ]),

                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'PPN' => 'PPN',
                        'Non-PPN' => 'Non-PPN',
                    ]),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        // TODO: Implement export
                        \Filament\Notifications\Notification::make()
                            ->title('Export Excel')
                            ->body('Fitur export akan segera tersedia')
                            ->info()
                            ->send();
                    }),

                Action::make('print_summary')
                    ->label('Print Summary')
                    ->icon('heroicon-o-printer')
                    ->action(function () {
                        // TODO: Implement print
                        \Filament\Notifications\Notification::make()
                            ->title('Print Summary')
                            ->body('Fitur print akan segera tersedia')
                            ->info()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseReports::route('/'),
            'hutang' => Pages\AccountsPayable::route('/hutang'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', session('selected_company_id'))
            ->with(['supplier', 'items', 'payments']);
    }
}
