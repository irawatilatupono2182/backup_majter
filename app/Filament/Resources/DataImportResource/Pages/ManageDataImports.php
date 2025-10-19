<?php

namespace App\Filament\Resources\DataImportResource\Pages;

use App\Filament\Resources\DataImportResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ManageDataImports extends ManageRecords
{
    protected static string $resource = DataImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('process_import')
                ->label('Proses Import')
                ->icon('heroicon-o-cloud-arrow-up')
                ->form([
                    Forms\Components\Select::make('import_type')
                        ->label('Jenis Import')
                        ->options([
                            'customers' => 'Data Pelanggan',
                            'suppliers' => 'Data Supplier', 
                            'products' => 'Data Produk',
                        ])
                        ->required(),

                    Forms\Components\FileUpload::make('file')
                        ->label('File CSV/Excel')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->visibility('private'),
                ])
                ->action(function (array $data): void {
                    $filePath = Storage::path($data['file']);
                    $results = DataImportResource::processImport($data['import_type'], $filePath);
                    
                    if ($results['success'] > 0) {
                        Notification::make()
                            ->title('Import Berhasil')
                            ->body("Berhasil import {$results['success']} data")
                            ->success()
                            ->send();
                    }
                    
                    if (!empty($results['errors'])) {
                        foreach ($results['errors'] as $error) {
                            Notification::make()
                                ->title('Error Import')
                                ->body($error)
                                ->danger()
                                ->send();
                        }
                    }
                    
                    // Clean up uploaded file
                    Storage::delete($data['file']);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add any widgets if needed
        ];
    }
}