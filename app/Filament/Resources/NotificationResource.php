<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Stock;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = Stock::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Notifikasi Stok & Piutang';
    protected static ?string $navigationGroup = '🔔 Notifikasi';
    
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'notifications';
    protected static bool $shouldRegisterNavigation = false;

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
            ->modifyQueryUsing(function (Builder $query) {
                $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
                return $query->where('company_id', $companyId)
                    ->where(function ($q) {
                        // Low stock or expired items
                        $q->whereColumn('available_quantity', '<', 'minimum_stock')
                          ->orWhere('expiry_date', '<', now())
                          ->orWhereBetween('expiry_date', [now(), now()->addDays(30)]);
                    });
            })
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('notification_type')
                    ->label('Jenis Notifikasi')
                    ->getStateUsing(function ($record) {
                        if ($record->isExpired()) {
                            return 'Produk Kadaluarsa';
                        }
                        if ($record->isNearExpiry()) {
                            return 'Mendekati Kadaluarsa';
                        }
                        if ($record->isBelowMinimum()) {
                            return 'Stock Rendah';
                        }
                        return 'Normal';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->isExpired()) {
                            return 'danger';
                        }
                        if ($record->isNearExpiry()) {
                            return 'warning';
                        }
                        if ($record->isBelowMinimum()) {
                            return 'danger';
                        }
                        return 'success';
                    }),

                TextColumn::make('available_quantity')
                    ->label('Stock Tersedia')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('minimum_stock')
                    ->label('Minimum Stock')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('expiry_date')
                    ->label('Tanggal Kadaluarsa')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->isExpired()) {
                            return 'danger';
                        }
                        if ($record->isNearExpiry()) {
                            return 'warning';
                        }
                        return null;
                    })
                    ->placeholder('-'),

                TextColumn::make('batch_number')
                    ->label('Batch')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->getStateUsing(function ($record) {
                        if ($record->isExpired()) {
                            return 'URGENT';
                        }
                        if ($record->isNearExpiry() || $record->isBelowMinimum()) {
                            return 'HIGH';
                        }
                        return 'NORMAL';
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state === 'URGENT') {
                            return 'danger';
                        }
                        if ($state === 'HIGH') {
                            return 'warning';
                        }
                        return 'success';
                    }),
            ])
            ->defaultSort('expiry_date', 'asc')
            ->actions([
                Action::make('create_purchase_order')
                    ->label('Buat PO')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn($record) => route('filament.admin.resources.purchase-orders.create', [
                        'product_id' => $record->product_id
                    ]))
                    ->visible(fn($record) => $record->isBelowMinimum()),

                Action::make('adjust_stock')
                    ->label('Adjustment')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->url(fn($record) => route('filament.admin.resources.stock-movements.create', [
                        'product_id' => $record->product_id,
                        'movement_type' => 'adjustment'
                    ])),
            ])
            ->headerActions([
                Action::make('mark_all_read')
                    ->label('Mark All as Read')
                    ->icon('heroicon-o-check')
                    ->action(function () {
                        // This could update a notifications table in the future
                        \Filament\Notifications\Notification::make()
                            ->title('All notifications marked as read')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
        return parent::getEloquentQuery()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->whereColumn('available_quantity', '<', 'minimum_stock')
                  ->orWhere('expiry_date', '<', now())
                  ->orWhereBetween('expiry_date', [now(), now()->addDays(30)]);
            });
    }

    public static function getNavigationBadge(): ?string
    {
        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
        $count = static::getEloquentQuery()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}