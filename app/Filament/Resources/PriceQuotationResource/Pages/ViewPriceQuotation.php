<?php

namespace App\Filament\Resources\PriceQuotationResource\Pages;

use App\Filament\Resources\PriceQuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPriceQuotation extends ViewRecord
{
    protected static string $resource = PriceQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn() => route('pdf.price-quotation.download', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->url(fn() => route('pdf.price-quotation.preview', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}