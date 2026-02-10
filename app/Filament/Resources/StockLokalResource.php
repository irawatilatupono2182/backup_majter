<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource;
use Illuminate\Database\Eloquent\Builder;

class StockLokalResource extends StockResource
{
    protected static ?string $navigationLabel = 'Barang Lokal';
    
    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static bool $shouldRegisterNavigation = false; // Hidden from navigation
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $slug = 'barang-lokal';
    
    protected static ?string $breadcrumb = 'Barang Lokal';

    public static function getNavigationTooltip(): ?string
    {
        return 'Manajemen barang/stock lokal';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('stocks.company_id', $companyId))
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'Local');
            });
    }
    
    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        return static::getModel()::whereHas('product', function ($query) {
                $query->where('product_type', 'Local');
            })
            ->where('company_id', $companyId)
            ->where('quantity', '<=', 'minimum_stock')
            ->count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\StockLokalResource\Pages\ListStockLokals::route('/'),
            'create' => \App\Filament\Resources\StockLokalResource\Pages\CreateStockLokal::route('/create'),
            'view' => \App\Filament\Resources\StockLokalResource\Pages\ViewStockLokal::route('/{record}'),
            'edit' => \App\Filament\Resources\StockLokalResource\Pages\EditStockLokal::route('/{record}/edit'),
        ];
    }
}
