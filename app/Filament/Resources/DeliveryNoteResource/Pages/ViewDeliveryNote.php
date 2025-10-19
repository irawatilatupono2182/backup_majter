<?php

namespace App\Filament\Resources\DeliveryNoteResource\Pages;

use App\Filament\Resources\DeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryNote extends ViewRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn() => route('pdf.delivery-note.download', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->url(fn() => route('pdf.delivery-note.preview', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('create_invoice')
                ->label('Buat Invoice')
                ->icon('heroicon-o-document-text')
                ->url(fn() => route('filament.admin.resources.invoices.create', ['delivery_note_id' => $this->record->delivery_note_id]))
                ->visible(fn() => $this->record->status === 'delivered' && !$this->record->invoice),
        ];
    }
}