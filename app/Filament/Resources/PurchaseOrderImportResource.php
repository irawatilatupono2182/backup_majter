<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderImportResource extends PurchaseOrderResource
{
    protected static ?string $navigationLabel = 'Pembelian Barang Import';
    
    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static bool $shouldRegisterNavigation = false; // Hidden from navigation
    
    protected static ?int $navigationSort = 6;
    
    protected static ?string $slug = 'pembelian-import';
    
    protected static ?string $breadcrumb = 'Pembelian Import';

    public static function getNavigationTooltip(): ?string
    {
        return 'Purchase Order untuk barang import';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->where('type', 'Import');
    }
    
    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        return static::getModel()::where('company_id', $companyId)
            ->where('type', 'Import')
            ->where('status', 'Pending')
            ->count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PurchaseOrderImportResource\Pages\ListPurchaseOrderImports::route('/'),
            'create' => \App\Filament\Resources\PurchaseOrderImportResource\Pages\CreatePurchaseOrderImport::route('/create'),
            'edit' => \App\Filament\Resources\PurchaseOrderImportResource\Pages\EditPurchaseOrderImport::route('/{record}/edit'),
        ];
    }
}
