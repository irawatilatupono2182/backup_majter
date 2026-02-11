<?php

namespace App\Filament\Resources\NotaMenyusulResource\Pages;

use App\Filament\Resources\NotaMenyusulResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewNotaMenyusul extends ViewRecord
{
    protected static string $resource = NotaMenyusulResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            // Convert to Invoice Action
            Actions\Action::make('convert_to_invoice')
                ->label('🔄 Convert ke Invoice')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->visible(fn ($record) => $record->canConvert())
                ->requiresConfirmation()
                ->modalHeading('Convert Nota Menyusul ke Invoice')
                ->modalDescription('Nota akan di-convert menjadi invoice dengan data yang sama.')
                ->form([
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date Invoice')
                        ->required()
                        ->default(now()->addDays(30))
                        ->helperText('Tanggal jatuh tempo invoice'),
                    Forms\Components\TextInput::make('payment_terms')
                        ->label('Terms of Payment')
                        ->numeric()
                        ->default(30)
                        ->suffix('Hari'),
                ])
                ->action(function ($record, array $data) {
                    try {
                        \DB::beginTransaction();
                        
                        // Create Invoice from Nota
                        $invoice = Invoice::create([
                            'company_id' => $record->company_id,
                            'customer_id' => $record->customer_id,
                            'sj_id' => $record->sj_id,
                            'nota_menyusul_id' => $record->nm_id,
                            'invoice_number' => Invoice::generateInvoiceNumber(),
                            'po_number' => $record->po_number,
                            'type' => $record->type,
                            'invoice_date' => now(),
                            'due_date' => $data['due_date'],
                            'payment_terms' => $data['payment_terms'],
                            'total_amount' => $record->total_amount,
                            'ppn_amount' => $record->ppn_amount,
                            'grand_total' => $record->grand_total,
                            'status' => 'Unpaid',
                            'notes' => 'Converted from Nota Menyusul: ' . $record->nota_number,
                            'created_by' => auth()->id(),
                        ]);
                        
                        // Copy items
                        foreach ($record->items as $item) {
                            $invoice->items()->create([
                                'product_id' => $item->product_id,
                                'qty' => $item->qty,
                                'unit' => $item->unit,
                                'unit_price' => $item->unit_price,
                                'discount_percent' => $item->discount_percent ?? 0,
                                'subtotal' => $item->subtotal,
                                'notes' => $item->notes,
                            ]);
                        }
                        
                        // Update Nota status
                        $record->update([
                            'converted_to_invoice_id' => $invoice->invoice_id,
                            'converted_at' => now(),
                            'status' => 'Converted',
                        ]);
                        
                        \DB::commit();
                        
                        Notification::make()
                            ->success()
                            ->title('✅ Nota berhasil di-convert!')
                            ->body("Invoice {$invoice->invoice_number} telah dibuat.")
                            ->send();
                        
                        // Redirect to invoice
                        return redirect()->route('filament.admin.resources.invoices.view', $invoice);
                        
                    } catch (\Exception $e) {
                        \DB::rollBack();
                        
                        Notification::make()
                            ->danger()
                            ->title('❌ Gagal convert nota')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
