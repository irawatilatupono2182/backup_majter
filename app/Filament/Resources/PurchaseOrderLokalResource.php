<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderLokalResource extends PurchaseOrderResource
{
    protected static ?string $navigationLabel = 'Pembelian Barang Lokal';
    
    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static bool $shouldRegisterNavigation = false; // Hidden from navigation
    
    protected static ?int $navigationSort = 5;
    
    protected static ?string $slug = 'pembelian-lokal';
    
    protected static ?string $breadcrumb = 'Pembelian Lokal';

    public static function getNavigationTooltip(): ?string
    {
        return 'Purchase Order untuk barang lokal';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->where('type', 'Local');
    }
    
    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        return static::getModel()::where('company_id', $companyId)
            ->where('type', 'Local')
            ->where('status', 'Pending')
            ->count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PurchaseOrderLokalResource\Pages\ListPurchaseOrderLokals::route('/'),
            'create' => \App\Filament\Resources\PurchaseOrderLokalResource\Pages\CreatePurchaseOrderLokal::route('/create'),
            'edit' => \App\Filament\Resources\PurchaseOrderLokalResource\Pages\EditPurchaseOrderLokal::route('/{record}/edit'),
        ];
    }
}
