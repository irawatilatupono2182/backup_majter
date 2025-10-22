<?php

use App\Models\Invoice;

// Check for duplicate invoice numbers
$duplicates = Invoice::where('invoice_number', 'INV/2025/10/001')->get();

echo "Found " . $duplicates->count() . " invoice(s) with number INV/2025/10/001:\n\n";

foreach ($duplicates as $invoice) {
    echo "- ID: {$invoice->invoice_id}\n";
    echo "  Number: {$invoice->invoice_number}\n";
    echo "  Company: {$invoice->company_id}\n";
    echo "  Created: {$invoice->created_at}\n\n";
}

// Delete all except the first one (or all if you want to recreate)
if ($duplicates->count() > 0) {
    echo "Deleting all invoices with this number...\n";
    foreach ($duplicates as $invoice) {
        $invoice->forceDelete(); // Force delete to bypass soft deletes
        echo "Deleted invoice {$invoice->invoice_id}\n";
    }
    echo "\nDone! You can now try creating a new invoice.\n";
}
