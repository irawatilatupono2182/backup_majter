<?php

namespace App\Filament\Resources\DeliveryNoteResource\Pages;

use App\Filament\Resources\DeliveryNoteResource;
use App\Models\DeliveryNote;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryNote extends CreateRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    /**
     * Mutate form data before creating the record.
     * Generate SJ number here to avoid race condition.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate unique SJ number with database lock
        $data['sj_number'] = $this->generateUniqueSJNumber();
        
        return $data;
    }

    /**
     * Generate unique SJ number with database lock to prevent duplicates
     */
    protected function generateUniqueSJNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $companyId = session('selected_company_id');
        
        // Use database transaction with lock to prevent race condition
        return \DB::transaction(function () use ($year, $month, $companyId) {
            // Get last number from existing records with lock (INCLUDING soft deleted)
            $lastRecord = DeliveryNote::withTrashed()  // ✅ Include soft deleted records
                ->where('company_id', $companyId)
                ->where('sj_number', 'like', "SJ/{$year}/{$month}/%")
                ->lockForUpdate() // Lock the rows to prevent concurrent reads
                ->orderBy('sj_number', 'desc')
                ->first();
            
            if ($lastRecord) {
                // Extract number from last record (e.g., "SJ/2025/10/001" -> 1)
                $parts = explode('/', $lastRecord->sj_number);
                $lastNumber = isset($parts[3]) ? (int)$parts[3] : 0;
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $sjNumber = sprintf('SJ/%s/%s/%03d', $year, $month, $nextNumber);
            
            // Double check if this number already exists (including soft deleted)
            $maxAttempts = 10;
            $attempt = 0;
            while (DeliveryNote::withTrashed()->where('company_id', $companyId)->where('sj_number', $sjNumber)->exists() && $attempt < $maxAttempts) {
                $nextNumber++;
                $sjNumber = sprintf('SJ/%s/%s/%03d', $year, $month, $nextNumber);
                $attempt++;
            }
            
            return $sjNumber;
        });
    }
}
