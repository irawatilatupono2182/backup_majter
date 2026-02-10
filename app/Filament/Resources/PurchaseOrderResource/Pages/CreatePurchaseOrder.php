<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function getTitle(): string
    {
        $type = session('po_type_create');
        if ($type === 'Local') {
            return 'Buat PO Lokal';
        } elseif ($type === 'Import') {
            return 'Buat PO Import';
        }
        return 'Buat Purchase Order';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate unique PO number
        $data['po_number'] = $this->generateUniquePONumber();
        
        // Store PO type in notes if selected from popup (untuk tracking saja)
        if (session('po_type_create')) {
            $poType = session('po_type_create');
            $typeLabel = $poType === 'Local' ? 'Pembelian Lokal' : 'Pembelian Import';
            $data['notes'] = ($data['notes'] ?? '') . "\n[Jenis: {$typeLabel}]";
        }
        
        // Auto-calculate subtotal for each item
        if (isset($data['items'])) {
            foreach ($data['items'] as &$item) {
                $baseAmount = $item['qty_ordered'] * $item['unit_price'];
                $discountAmount = $baseAmount * ($item['discount_percent'] / 100);
                $item['subtotal'] = $baseAmount - $discountAmount;
            }
        }

        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        // Clear session after create
        session()->forget('po_type_create');
        
        return $this->getResource()::getUrl('index');
    }
    
    protected function afterCreate(): void
    {
        // Clear session
        session()->forget('po_type_create');
    }

    /**
     * Generate unique PO number with database lock to prevent duplicates
     */
    protected function generateUniquePONumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $companyId = session('selected_company_id');
        
        // Use database transaction with lock to prevent race condition
        return \DB::transaction(function () use ($year, $month, $companyId) {
            // Get last number from existing records with lock (INCLUDING soft deleted)
            $lastRecord = PurchaseOrder::withTrashed()  // ✅ Include soft deleted records
                ->where('company_id', $companyId)
                ->where('po_number', 'like', "PO/{$year}/{$month}/%")
                ->lockForUpdate()
                ->orderBy('po_number', 'desc')
                ->first();
            
            if ($lastRecord) {
                $parts = explode('/', $lastRecord->po_number);
                $lastNumber = isset($parts[3]) ? (int)$parts[3] : 0;
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $poNumber = sprintf('PO/%s/%s/%03d', $year, $month, $nextNumber);
            
            // Double check if this number already exists (including soft deleted)
            $maxAttempts = 10;
            $attempt = 0;
            while (PurchaseOrder::withTrashed()->where('company_id', $companyId)->where('po_number', $poNumber)->exists() && $attempt < $maxAttempts) {
                $nextNumber++;
                $poNumber = sprintf('PO/%s/%s/%03d', $year, $month, $nextNumber);
                $attempt++;
            }
            
            return $poNumber;
        });
    }
}