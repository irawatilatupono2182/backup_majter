<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource;
use Illuminate\Database\Eloquent\Builder;

class SupplierImportResource extends SupplierResource
{
    protected static ?string $navigationLabel = 'Supplier Import';
    
    protected static ?string $navigationGroup = '🛒 Pembelian';
    
    protected static bool $shouldRegisterNavigation = false; // Hidden from navigation
    
    protected static ?int $navigationSort = 4;
    
    protected static ?string $slug = 'supplier-import';
    
    protected static ?string $breadcrumb = 'Supplier Import';

    public static function getNavigationTooltip(): ?string
    {
        return 'Data supplier/pemasok import';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->where('type', 'Import');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SupplierImportResource\Pages\ListSupplierImports::route('/'),
            'create' => \App\Filament\Resources\SupplierImportResource\Pages\CreateSupplierImport::route('/create'),
            'edit' => \App\Filament\Resources\SupplierImportResource\Pages\EditSupplierImport::route('/{record}/edit'),
        ];
    }
}
