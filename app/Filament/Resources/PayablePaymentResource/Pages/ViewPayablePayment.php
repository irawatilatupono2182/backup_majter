<?php

namespace App\Filament\Resources\PayablePaymentResource\Pages;

use App\Filament\Resources\PayablePaymentResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Storage;

class ViewPayablePayment extends ViewRecord
{
    protected static string $resource = PayablePaymentResource::class;
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('payment_number')
                                    ->label('Nomor Pembayaran')
                                    ->copyable()
                                    ->icon('heroicon-o-clipboard-document')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('payment_date')
                                    ->label('Tanggal Pembayaran')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-calendar'),
                                
                                Infolists\Components\TextEntry::make('payment_method_label')
                                    ->label('Metode Pembayaran')
                                    ->badge()
                                    ->color('info'),
                            ]),
                        
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('payable.payable_number')
                                    ->label('Nomor Hutang')
                                    ->url(fn($record) => route('filament.admin.resources.payables.view', ['record' => $record->payable_id]))
                                    ->color('info'),
                                
                                Infolists\Components\TextEntry::make('payable.supplier.name')
                                    ->label('Supplier')
                                    ->icon('heroicon-o-building-storefront'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Detail Pembayaran')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount')
                            ->label('Jumlah Dibayar')
                            ->money('IDR')
                            ->size('lg')
                            ->weight('bold')
                            ->color('success'),
                        
                        Infolists\Components\TextEntry::make('bank_name')
                            ->label('Nama Bank')
                            ->placeholder('-')
                            ->visible(fn($record) => $record->bank_name !== null),
                        
                        Infolists\Components\TextEntry::make('account_number')
                            ->label('Nomor Rekening')
                            ->placeholder('-')
                            ->visible(fn($record) => $record->account_number !== null),
                        
                        Infolists\Components\TextEntry::make('check_giro_number')
                            ->label('Nomor Cek/Giro')
                            ->placeholder('-')
                            ->visible(fn($record) => $record->check_giro_number !== null),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Bukti Pembayaran')
                    ->schema([
                        Infolists\Components\TextEntry::make('attachment_filename')
                            ->label('Nama File')
                            ->icon('heroicon-o-paper-clip'),
                        
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('download')
                                ->label('Download File')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->color('info')
                                ->action(function ($record) {
                                    if ($record->attachment_path && Storage::disk('public')->exists($record->attachment_path)) {
                                        return Storage::disk('public')->download(
                                            $record->attachment_path,
                                            $record->attachment_filename ?? basename($record->attachment_path)
                                        );
                                    }
                                }),
                            
                            Infolists\Components\Actions\Action::make('view')
                                ->label('Lihat File')
                                ->icon('heroicon-o-eye')
                                ->color('success')
                                ->url(fn($record) => $record->attachment_url)
                                ->openUrlInNewTab(),
                        ]),
                    ])
                    ->visible(fn($record) => $record->attachment_path !== null)
                    ->collapsible(),
                
                Infolists\Components\Section::make('Catatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->markdown()
                            ->placeholder('Tidak ada catatan'),
                    ])
                    ->visible(fn($record) => $record->notes)
                    ->collapsible(),
                
                Infolists\Components\Section::make('Informasi Sistem')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('creator.name')
                                    ->label('Dibuat Oleh')
                                    ->icon('heroicon-o-user'),
                                
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Tanggal Dibuat')
                                    ->dateTime('d F Y, H:i')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
    
    public function getTitle(): string
    {
        return 'Detail Pembayaran';
    }
}
