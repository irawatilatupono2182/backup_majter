<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn() => route('pdf.invoice.download', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->url(fn() => route('pdf.invoice.preview', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Invoice')
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label('Nomor Invoice'),
                        TextEntry::make('deliveryNote.sj_number')
                            ->label('Surat Jalan'),
                        TextEntry::make('deliveryNote.type')
                            ->label('Jenis Surat Jalan')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PPN' => 'success',
                                'Non-PPN' => 'warning',
                                'Supplier' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('customer.name')
                            ->label('Customer'),
                        TextEntry::make('invoice_date')
                            ->label('Tanggal Invoice')
                            ->date('d/m/Y'),
                        TextEntry::make('due_date')
                            ->label('Jatuh Tempo')
                            ->date('d/m/Y'),
                        TextEntry::make('po_number')
                            ->label('PO Number')
                            ->placeholder('-'),
                        TextEntry::make('payment_terms')
                            ->label('TOP')
                            ->suffix(' Hari')
                            ->default('30'),
                        TextEntry::make('ppn_included')
                            ->label('PPN')
                            ->formatStateUsing(fn ($state) => $state ? 'Ya (11%)' : 'Tidak')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'gray'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Item Invoice')
                    ->schema([
                        TextEntry::make('items')
                            ->label('')
                            ->listWithLineBreaks()
                            ->formatStateUsing(function ($record) {
                                return $record->items->map(function ($item) {
                                    $text = sprintf(
                                        '%s - %s %s × Rp %s = Rp %s',
                                        $item->product->name ?? 'N/A',
                                        number_format($item->qty, 0, ',', '.'),
                                        $item->unit,
                                        number_format($item->unit_price, 0, ',', '.'),
                                        number_format($item->subtotal, 0, ',', '.')
                                    );
                                    if ($item->notes) {
                                        $text .= sprintf('<br><small style="color: gray;">📝 %s</small>', e($item->notes));
                                    }
                                    return $text;
                                })->join('<br>');
                            })
                            ->html(),
                    ]),

                Section::make('Total')
                    ->schema([
                        TextEntry::make('subtotal_amount')
                            ->label('Subtotal')
                            ->money('IDR'),
                        TextEntry::make('ppn_amount')
                            ->label('PPN (11%)')
                            ->money('IDR'),
                        TextEntry::make('total_amount')
                            ->label('Total')
                            ->money('IDR')
                            ->weight('bold'),
                        TextEntry::make('status')
                            ->label('Status Pembayaran')
                            ->badge()
                            ->color(function (string $state): string {
                                if ($state === 'Paid' || $state === 'paid') return 'success';
                                if ($state === 'Partial' || $state === 'partial') return 'warning';
                                if ($state === 'Overdue' || $state === 'overdue') return 'danger';
                                return 'gray';
                            })
                            ->formatStateUsing(function (string $state): string {
                                if ($state === 'Paid' || $state === 'paid') return 'Lunas';
                                if ($state === 'Partial' || $state === 'partial') return 'Sebagian';
                                if ($state === 'Overdue' || $state === 'overdue') return 'Jatuh Tempo';
                                return 'Belum Lunas';
                            }),
                    ])
                    ->columns(2),
            ]);
    }
}