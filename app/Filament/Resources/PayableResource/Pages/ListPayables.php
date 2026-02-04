<?php

namespace App\Filament\Resources\PayableResource\Pages;

use App\Filament\Resources\PayableResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListPayables extends ListRecords
{
    protected static string $resource = PayableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Hutang Baru')
                ->icon('heroicon-o-plus'),
            
            Actions\Action::make('sync_from_po')
                ->label('🔄 Sync dari PO')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Sync Hutang dari Purchase Order')
                ->modalDescription('Sistem akan mengecek semua PO yang belum punya record hutang dan membuat record baru secara otomatis.')
                ->modalSubmitActionLabel('Ya, Sync Sekarang')
                ->action(function () {
                    $companyId = session('selected_company_id');
                    
                    // Ambil semua PO yang belum punya payable
                    $posWithoutPayable = \App\Models\PurchaseOrder::where('company_id', $companyId)
                        ->whereDoesntHave('payables')
                        ->whereNotNull('due_date')
                        ->with(['supplier', 'items'])
                        ->get();
                    
                    $created = 0;
                    
                    foreach ($posWithoutPayable as $po) {
                        $grandTotal = $po->getGrandTotal();
                        
                        if ($grandTotal > 0) {
                            // Hitung total yang sudah dibayar
                            $totalPaid = $po->payments()->sum('amount');
                            $remainingAmount = $grandTotal - $totalPaid;
                            
                            // Tentukan status
                            $status = 'unpaid';
                            if ($totalPaid > 0) {
                                $status = $totalPaid >= $grandTotal ? 'paid' : 'partial';
                            }
                            if ($status !== 'paid' && now()->greaterThan($po->due_date)) {
                                $status = 'overdue';
                            }
                            
                            \App\Models\Payable::create([
                                'payable_id' => \Illuminate\Support\Str::uuid(),
                                'company_id' => $po->company_id,
                                'supplier_id' => $po->supplier_id,
                                'purchase_order_id' => $po->po_id,
                                'reference_type' => 'po',
                                'reference_number' => $po->po_number,
                                'payable_date' => $po->order_date,
                                'due_date' => $po->due_date,
                                'amount' => $grandTotal,
                                'paid_amount' => $totalPaid,
                                'remaining_amount' => max(0, $remainingAmount),
                                'status' => $status,
                                'notes' => 'Auto-sync dari PO #' . $po->po_number,
                                'created_by' => auth()->id(),
                            ]);
                            
                            $created++;
                        }
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Sync Berhasil!')
                        ->body("Berhasil membuat {$created} record hutang dari Purchase Order.")
                        ->success()
                        ->send();
                }),
        ];
    }
    

    public function getTitle(): string
    {
        return 'Daftar Hutang';
    }
}
