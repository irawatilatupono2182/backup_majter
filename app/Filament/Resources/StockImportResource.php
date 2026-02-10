<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource;
use Illuminate\Database\Eloquent\Builder;

class StockImportResource extends StockResource
{
    protected static ?string $navigationLabel = 'Barang Import';
    
    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static bool $shouldRegisterNavigation = false; // Hidden from navigation
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $slug = 'barang-import';
    
    protected static ?string $breadcrumb = 'Barang Import';

    public static function getNavigationTooltip(): ?string
    {
        return 'Manajemen barang/stock import';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('stocks.company_id', $companyId))
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'Import');
            });
    }
    
    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        return static::getModel()::whereHas('product', function ($query) {
                $query->where('product_type', 'Import');
            })
            ->where('company_id', $companyId)
            ->where('quantity', '<=', 'minimum_stock')
            ->count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\StockImportResource\Pages\ListStockImports::route('/'),
            'create' => \App\Filament\Resources\StockImportResource\Pages\CreateStockImport::route('/create'),
            'view' => \App\Filament\Resources\StockImportResource\Pages\ViewStockImport::route('/{record}'),
            'edit' => \App\Filament\Resources\StockImportResource\Pages\EditStockImport::route('/{record}/edit'),
        ];
    }
}
