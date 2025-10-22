<?php

namespace App\Filament\Resources\DeliveryNoteResource\Pages;

use App\Filament\Resources\DeliveryNoteResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryNote extends ViewRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Surat Jalan')
                    ->schema([
                        Infolists\Components\TextEntry::make('sj_number')
                            ->label('Nomor SJ'),
                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('Customer'),
                        Infolists\Components\TextEntry::make('type')
                            ->label('Jenis')
                            ->badge()
                            ->color(function ($state) {
                                if ($state === 'PPN') return 'success';
                                if ($state === 'Supplier') return 'warning';
                                return 'gray';
                            }),
                        Infolists\Components\TextEntry::make('delivery_date')
                            ->label('Tanggal Kirim')
                            ->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(function ($state) {
                                if ($state === 'Completed') return 'success';
                                if ($state === 'Sent') return 'info';
                                return 'gray';
                            }),
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Detail Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Produk'),
                                Infolists\Components\TextEntry::make('qty')
                                    ->label('Qty')
                                    ->formatStateUsing(function ($state, $record) {
                                        $qty = $state == intval($state) ? intval($state) : number_format($state, 2);
                                        return "{$qty} {$record->unit}";
                                    }),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('discount_percent')
                                    ->label('Diskon')
                                    ->suffix('%'),
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('IDR'),
                            ])
                            ->columns(5),
                    ]),

                Infolists\Components\Section::make('Total')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money('IDR')
                            ->getStateUsing(fn($record) => $record->getTotalAmount())
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('ppn_amount')
                            ->label('PPN (11%)')
                            ->money('IDR')
                            ->getStateUsing(fn($record) => $record->getPPNAmount())
                            ->visible(fn($record) => $record->isPPN()),
                        Infolists\Components\TextEntry::make('grand_total')
                            ->label('Grand Total')
                            ->money('IDR')
                            ->getStateUsing(fn($record) => $record->getGrandTotal())
                            ->weight('bold')
                            ->size('xl')
                            ->color('success'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Stock Movement')
                    ->schema([
                        Infolists\Components\TextEntry::make('stock_movements')
                            ->label('Status Stock Movement')
                            ->getStateUsing(function ($record) {
                                $movements = \App\Models\StockMovement::where('reference_type', 'delivery_note')
                                    ->where('reference_id', $record->sj_id)
                                    ->count();
                                
                                if ($movements > 0) {
                                    return "✅ {$movements} movement record(s) dibuat";
                                }
                                
                                if (in_array($record->status, ['Sent', 'Completed'])) {
                                    return "⚠️ Belum ada stock movement (seharusnya sudah ada)";
                                }
                                
                                return "📋 Status masih Draft, stock movement belum dibuat";
                            })
                            ->badge()
                            ->color(function ($record) {
                                $movements = \App\Models\StockMovement::where('reference_type', 'delivery_note')
                                    ->where('reference_id', $record->sj_id)
                                    ->count();
                                
                                if ($movements > 0) return 'success';
                                if (in_array($record->status, ['Sent', 'Completed'])) return 'warning';
                                return 'gray';
                            }),
                        Infolists\Components\TextEntry::make('movement_details')
                            ->label('Detail Movement')
                            ->getStateUsing(function ($record) {
                                $movements = \App\Models\StockMovement::with('product')
                                    ->where('reference_type', 'delivery_note')
                                    ->where('reference_id', $record->sj_id)
                                    ->get();
                                
                                if ($movements->isEmpty()) {
                                    return '-';
                                }
                                
                                $details = [];
                                foreach ($movements as $movement) {
                                    $details[] = "{$movement->product->name}: -{$movement->quantity} unit";
                                }
                                
                                return implode("\n", $details);
                            })
                            ->columnSpanFull()
                            ->visible(function ($record) {
                                return \App\Models\StockMovement::where('reference_type', 'delivery_note')
                                    ->where('reference_id', $record->sj_id)
                                    ->exists();
                            }),
                    ])
                    ->columns(2)
                    ->collapsed(false)
                    ->visible(fn($record) => in_array($record->status, ['Sent', 'Completed'])),
            ]);
    }

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