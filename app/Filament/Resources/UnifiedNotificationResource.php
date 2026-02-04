<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnifiedNotificationResource\Pages;
use App\Models\Stock;
use App\Models\Invoice;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UnifiedNotificationResource extends Resource
{
    protected static ?string $model = null;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Notifikasi';
    protected static ?string $navigationGroup = '🔔 Notifikasi';
    
    protected static ?int $navigationSort = -1;
    protected static ?string $slug = 'notifications';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        $companyId = session('selected_company_id');

        return $table
            ->heading('🔔 Semua Notifikasi Penting')
            ->description('Stock rendah, produk kadaluarsa, dan invoice jatuh tempo')
            ->query(
                // This will be populated dynamically in the page
                Stock::query()->whereRaw('1 = 0')
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'stock_low' => 'danger',
                        'stock_expired' => 'danger',
                        'stock_expiring' => 'warning',
                        'invoice_overdue' => 'danger',
                        'invoice_due_today' => 'warning',
                        'invoice_due_soon' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('Keterangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('details')
                    ->label('Detail')
                    ->wrap(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'URGENT' => 'danger',
                        'HIGH' => 'warning',
                        'NORMAL' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->visible(fn($record) => isset($record->amount)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'stock' => 'Stock & Inventory',
                        'invoice' => 'Invoice & Piutang',
                    ])
                    ->query(function (Builder $query, array $data) {
                        // This will be handled in the page
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => $record->action_url ?? '#'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnifiedNotifications::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;

        $stockCount = Stock::where('company_id', $companyId)
            ->where(function ($q) {
                $q->whereColumn('available_quantity', '<', 'minimum_stock')
                  ->orWhere('expiry_date', '<', now())
                  ->orWhereBetween('expiry_date', [now(), now()->addDays(30)]);
            })
            ->count();

        $invoiceCount = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->where('due_date', '<=', now()->addDays(7))
            ->count();

        $total = $stockCount + $invoiceCount;
        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;

        $stockCount = Stock::where('company_id', $companyId)
            ->where(function ($q) {
                $q->whereColumn('available_quantity', '<', 'minimum_stock')
                  ->orWhere('expiry_date', '<', now());
            })
            ->count();

        $invoiceCount = Invoice::where('company_id', $companyId)
            ->where('due_date', '<', now())
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->count();

        return "{$stockCount} stock alert, {$invoiceCount} invoice overdue";
    }
}
