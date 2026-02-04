<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataImportResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Imports\CustomerImport;
use App\Imports\SupplierImport;
use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class DataImportResource extends Resource
{
    protected static ?string $model = null;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel = 'Import Data';
    protected static ?string $slug = 'data-import';
    protected static ?string $navigationGroup = '⚙️ Pengaturan';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->can('import_data');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Import Data')
                    ->description('Upload file CSV atau Excel untuk import data')
                    ->schema([
                        Forms\Components\Select::make('import_type')
                            ->label('Jenis Import')
                            ->options([
                                'customers' => 'Data Pelanggan',
                                'suppliers' => 'Data Supplier',
                                'products' => 'Data Produk',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('file', null)),

                        Forms\Components\FileUpload::make('file')
                            ->label('File')
                            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->required()
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private'),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('download_template')
                                ->label('Download Template')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->action(function (callable $get) {
                                    $importType = $get('import_type');
                                    if (!$importType) {
                                        Notification::make()
                                            ->title('Pilih jenis import terlebih dahulu')
                                            ->warning()
                                            ->send();
                                        return;
                                    }
                                    
                                    return static::downloadTemplate($importType);
                                })
                                ->visible(fn (callable $get) => $get('import_type')),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Import Data CSV/Excel')
            ->description('Upload file untuk import data master')
            ->emptyStateHeading('Belum ada import yang dilakukan')
            ->emptyStateDescription('Gunakan form di atas untuk melakukan import data')
            ->emptyStateIcon('heroicon-o-arrow-down-tray')
            ->columns([])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDataImports::route('/'),
        ];
    }

    public static function downloadTemplate(string $type): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $templates = [
            'customers' => [
                'filename' => 'template_customers.csv',
                'headers' => ['name', 'email', 'phone', 'address', 'city', 'postal_code', 'tax_number', 'credit_limit', 'payment_terms', 'is_active'],
                'sample' => ['PT Contoh Customer', 'customer@example.com', '081234567890', 'Jl. Contoh No. 123', 'Jakarta', '12345', '12.345.678.9-012.000', '10000000', '30', 'true'],
            ],
            'suppliers' => [
                'filename' => 'template_suppliers.csv',
                'headers' => ['name', 'email', 'phone', 'address', 'city', 'postal_code', 'tax_number', 'payment_terms', 'is_active'],
                'sample' => ['PT Contoh Supplier', 'supplier@example.com', '081234567890', 'Jl. Supplier No. 456', 'Bandung', '40123', '98.765.432.1-543.000', '14', 'true'],
            ],
            'products' => [
                'filename' => 'template_products.csv',
                'headers' => ['name', 'code', 'description', 'category', 'unit', 'purchase_price', 'sale_price', 'min_stock', 'max_stock', 'is_active'],
                'sample' => ['Produk Contoh', 'PRD001', 'Deskripsi produk contoh', 'Elektronik', 'pcs', '100000', '150000', '10', '100', 'true'],
            ],
        ];

        $template = $templates[$type];
        $content = implode(',', $template['headers']) . "\n";
        $content .= implode(',', $template['sample']) . "\n";

        $path = 'import-templates/' . $template['filename'];
        Storage::put($path, $content);

        return Storage::download($path, $template['filename']);
    }

    public static function processImport(string $importType, string $filePath): array
    {
        try {
            $results = ['success' => 0, 'errors' => []];
            
            switch ($importType) {
                case 'customers':
                    $import = new CustomerImport();
                    break;
                case 'suppliers':
                    $import = new SupplierImport();
                    break;
                case 'products':
                    $import = new ProductImport();
                    break;
                default:
                    throw new \Exception('Jenis import tidak valid');
            }

            Excel::import($import, $filePath);
            
            $results['success'] = Excel::getRowCount($filePath) - 1; // minus header
            $results['errors'] = $import->getErrors();
            
            return $results;
        } catch (\Exception $e) {
            return ['success' => 0, 'errors' => [$e->getMessage()]];
        }
    }
}