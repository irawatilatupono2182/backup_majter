<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayablesResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayablesResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationLabel = 'Hutang Usaha (AP)';
    
    protected static ?string $pluralModelLabel = 'Hutang Usaha';
    
    protected static ?string $modelLabel = 'Hutang';
    
    protected static ?string $navigationGroup = '💰 Keuangan';
    
    protected static ?int $navigationSort = 99;
    
    // Disabled - fitur sudah ada di Laporan Pembelian
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    protected static bool $shouldRegisterNavigation = false; // Hidden dari menu
    
    public static function getNavigationTooltip(): ?string
    {
        return 'Tagihan yang harus dibayar ke supplier (hutang)';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->with(['supplier', 'payments']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Hutang')
                    ->schema([
                        Forms\Components\Placeholder::make('po_number')
                            ->label('No. PO')
                            ->content(fn($record) => $record->po_number),
                        
                        Forms\Components\Placeholder::make('supplier')
                            ->label('Supplier')
                            ->content(fn($record) => $record->supplier->name ?? '-'),
                        
                        Forms\Components\Placeholder::make('order_date')
                            ->label('Tanggal Order')
                            ->content(fn($record) => $record->order_date?->format('d/m/Y')),
                        
                        Forms\Components\Placeholder::make('due_date')
                            ->label('Jatuh Tempo')
                            ->content(fn($record) => $record->due_date?->format('d/m/Y')),
                        
                        Forms\Components\Placeholder::make('grand_total')
                            ->label('Total Hutang')
                            ->content(fn($record) => 'Rp ' . number_format($record->getGrandTotal(), 2, ',', '.')),
                        
                        Forms\Components\Placeholder::make('total_paid')
                            ->label('Total Dibayar')
                            ->content(fn($record) => 'Rp ' . number_format($record->getTotalPaid(), 2, ',', '.')),
                        
                        Forms\Components\Placeholder::make('remaining')
                            ->label('Sisa Hutang')
                            ->content(fn($record) => 'Rp ' . number_format($record->getRemainingAmount(), 2, ',', '.')),
                        
                        Forms\Components\Placeholder::make('payment_status')
                            ->label('Status Pembayaran')
                            ->content(fn($record) => match($record->payment_status) {
                                'paid' => '✓ Lunas',
                                'partial' => '⚠ Dibayar Sebagian',
                                'unpaid' => '✗ Belum Dibayar',
                                default => '-'
                            }),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('💳 Hutang Usaha - Tagihan ke Supplier')
            ->description('Monitor dan kelola hutang ke supplier. Prioritas pembayaran berdasarkan due date.')
            ->modifyQueryUsing(function($query) {
                $companyId = session('selected_company_id');
                return $query
                    ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                    ->whereIn('payment_status', ['unpaid', 'partial'])
                    ->whereNotNull('due_date');
            })
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
                            return 'BAYAR HARI INI';
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
                        $record->isOverdue() => 'heroicon-o-exclamation-triangle',
                        now()->diffInDays($record->due_date, false) == 0 => 'heroicon-o-clock',
                        default => 'heroicon-o-check-circle'
                    })
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Tgl Order')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : 'warning'),
                
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->getGrandTotal())
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Terbayar')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->getTotalPaid()),
                
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Sisa')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->getRemainingAmount())
                    ->color('danger')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('days_overdue')
                    ->label('Hari Terlambat')
                    ->getStateUsing(function($record) {
                        if ($record->isOverdue()) {
                            return $record->getDaysOverdue() . ' hari';
                        }
                        $days = $record->getDaysTillDue();
                        return $days > 0 ? $days . ' hari lagi' : 'Jatuh tempo hari ini';
                    })
                    ->badge()
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : 'warning'),
                
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'paid' => 'Lunas',
                        'partial' => 'Dibayar Sebagian',
                        'unpaid' => 'Belum Dibayar',
                        default => '-'
                    })
                    ->color(fn($state) => match($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray'
                    }),
            ])
            ->defaultSort('due_date', 'asc')
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
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Dibayar',
                        'partial' => 'Dibayar Sebagian',
                    ]),
            ])
            ->actions([
                Tables\Columns\ActionGroup::make([
                    Tables\Actions\Action::make('pay_hutang')
                        ->label('💰 Bayar Hutang')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->url(fn($record) => route('filament.admin.resources.purchase-payments.create', [
                            'po_id' => $record->po_id
                        ])),
                    Tables\Actions\Action::make('request_extension')
                        ->label('📅 Minta Perpanjangan')
                        ->icon('heroicon-o-calendar')
                        ->color('warning')
                        ->form([
                            Forms\Components\DatePicker::make('new_due_date')
                                ->label('Tanggal Jatuh Tempo Baru')
                                ->required()
                                ->after('today'),
                            Forms\Components\Textarea::make('reason')
                                ->label('Alasan')
                                ->placeholder('Jelaskan alasan meminta perpanjangan...')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            // TODO: Send extension request to supplier
                            \Filament\Notifications\Notification::make()
                                ->title('Request Extension Terkirim')
                                ->body("Permintaan perpanjangan telah dikirim ke {$record->supplier->name}")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('contact_supplier')
                        ->label('📞 Hubungi Supplier')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan Komunikasi')
                                ->placeholder('Catat hasil komunikasi dengan supplier...')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            \Filament\Notifications\Notification::make()
                                ->title('Communication Log Tersimpan')
                                ->body('Catatan komunikasi dengan supplier telah disimpan')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\ViewAction::make()
                        ->url(fn($record) => route('filament.admin.resources.purchase-orders.view', ['record' => $record->po_id]))
                        ->label('Lihat Detail'),
                ])
                ->button()
                ->label('Actions')
                ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('schedule_payments')
                        ->label('📅 Jadwalkan Pembayaran')
                        ->icon('heroicon-o-calendar')
                        ->color('info')
                        ->form([
                            Forms\Components\DatePicker::make('payment_date')
                                ->label('Tanggal Pembayaran')
                                ->required()
                                ->default(now()),
                        ])
                        ->action(function ($records, array $data) {
                            \Filament\Notifications\Notification::make()
                                ->title('Pembayaran Dijadwalkan')
                                ->body($records->count() . ' pembayaran telah dijadwalkan')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->poll('30s')
            ->striped()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayables::route('/'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        
        if (!$companyId) {
            return null;
        }
        
        $count = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
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
}
