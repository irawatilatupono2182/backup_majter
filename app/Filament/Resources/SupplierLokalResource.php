<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource;
use Illuminate\Database\Eloquent\Builder;

class SupplierLokalResource extends SupplierResource
{
    protected static ?string $navigationLabel = 'Supplier Lokal';
    
    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static bool $shouldRegisterNavigation = false; // Hidden from navigation
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $slug = 'supplier-lokal';
    
    protected static ?string $breadcrumb = 'Supplier Lokal';

    public static function getNavigationTooltip(): ?string
    {
        return 'Data supplier/pemasok lokal';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->where('type', 'Local');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SupplierLokalResource\Pages\ListSupplierLokals::route('/'),
            'create' => \App\Filament\Resources\SupplierLokalResource\Pages\CreateSupplierLokal::route('/create'),
            'edit' => \App\Filament\Resources\SupplierLokalResource\Pages\EditSupplierLokal::route('/{record}/edit'),
        ];
    }
}
