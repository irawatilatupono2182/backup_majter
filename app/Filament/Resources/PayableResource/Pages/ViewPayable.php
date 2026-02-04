<?php

namespace App\Filament\Resources\PayableResource\Pages;

use App\Filament\Resources\PayableResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Storage;

class ViewPayable extends ViewRecord
{
    protected static string $resource = PayableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Hutang')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('payable_number')
                                    ->label('Nomor Hutang')
                                    ->copyable()
                                    ->icon('heroicon-o-clipboard-document')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => match($state) {
                                        'unpaid' => '⚪ Belum Dibayar',
                                        'partial' => '🟡 Dibayar Sebagian',
                                        'paid' => '🟢 Lunas',
                                        'overdue' => '🔴 Terlambat',
                                        default => $state,
                                    })
                                    ->color(fn($state) => match($state) {
                                        'unpaid' => 'gray',
                                        'partial' => 'warning',
                                        'paid' => 'success',
                                        'overdue' => 'danger',
                                        default => 'gray',
                                    }),
                                
                                Infolists\Components\TextEntry::make('reference_type')
                                    ->label('Tipe Referensi')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => $state === 'po' ? '📋 Dari PO' : '✍️ Manual')
                                    ->color(fn($state) => $state === 'po' ? 'info' : 'gray'),
                            ]),
                        
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('supplier.name')
                                    ->label('Supplier')
                                    ->icon('heroicon-o-building-storefront'),
                                
                                Infolists\Components\TextEntry::make('reference_label')
                                    ->label('Nomor Referensi'),
                            ]),
                        
                        Infolists\Components\TextEntry::make('reference_description')
                            ->label('Deskripsi Referensi')
                            ->visible(fn($record) => $record->reference_type === 'manual' && $record->reference_description)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                
                Infolists\Components\Section::make('Detail Tanggal & Jumlah')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('payable_date')
                                    ->label('Tanggal Hutang')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-calendar'),
                                
                                Infolists\Components\TextEntry::make('due_date')
                                    ->label('Jatuh Tempo')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-clock')
                                    ->color(function ($record) {
                                        $days = $record->days_until_due;
                                        if ($days < 0) return 'danger';
                                        if ($days <= 7) return 'warning';
                                        return 'success';
                                    })
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('days_until_due')
                                    ->label('Sisa Waktu')
                                    ->formatStateUsing(function ($record) {
                                        $days = $record->days_until_due;
                                        if ($days < 0) {
                                            return abs($days) . ' hari terlambat';
                                        } elseif ($days == 0) {
                                            return 'JATUH TEMPO HARI INI!';
                                        } else {
                                            return $days . ' hari lagi';
                                        }
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $days = $record->days_until_due;
                                        if ($days < 0) return 'danger';
                                        if ($days == 0) return 'warning';
                                        if ($days <= 7) return 'info';
                                        return 'success';
                                    }),
                            ]),
                        
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('Total Hutang')
                                    ->money('IDR')
                                    ->size('lg')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('paid_amount')
                                    ->label('Sudah Dibayar')
                                    ->money('IDR')
                                    ->color('success')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('remaining_amount')
                                    ->label('Sisa Hutang')
                                    ->money('IDR')
                                    ->color('danger')
                                    ->size('lg')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('payment_percentage')
                                    ->label('% Terbayar')
                                    ->formatStateUsing(fn($record) => 
                                        number_format(($record->paid_amount / $record->amount) * 100, 1) . '%'
                                    )
                                    ->badge()
                                    ->color(function ($record) {
                                        $percentage = ($record->paid_amount / $record->amount) * 100;
                                        if ($percentage >= 100) return 'success';
                                        if ($percentage >= 50) return 'warning';
                                        return 'danger';
                                    }),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Bukti Hutang')
                    ->schema([
                        Infolists\Components\TextEntry::make('attachment_filename')
                            ->label('Nama File')
                            ->icon('heroicon-o-paper-clip')
                            ->visible(fn($record) => $record->attachment_path !== null),
                        
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
                                })
                                ->visible(fn($record) => $record->attachment_path !== null),
                            
                            Infolists\Components\Actions\Action::make('view')
                                ->label('Lihat File')
                                ->icon('heroicon-o-eye')
                                ->color('success')
                                ->url(fn($record) => $record->attachment_url)
                                ->openUrlInNewTab()
                                ->visible(fn($record) => $record->attachment_path !== null),
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
                                
                                Infolists\Components\TextEntry::make('updater.name')
                                    ->label('Diubah Oleh')
                                    ->icon('heroicon-o-user')
                                    ->visible(fn($record) => $record->updated_by !== null),
                                
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Terakhir Diubah')
                                    ->dateTime('d F Y, H:i')
                                    ->icon('heroicon-o-clock')
                                    ->visible(fn($record) => $record->updated_by !== null),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
    
    public function getTitle(): string
    {
        return 'Detail Hutang';
    }
}
