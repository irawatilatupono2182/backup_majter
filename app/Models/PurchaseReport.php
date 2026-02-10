<?php

namespace App\Models;

/**
 * PurchaseReport - Alias model for Purchase Order specifically for reporting
 * This prevents model conflicts with PurchaseOrderResource in Filament
 */
class PurchaseReport extends PurchaseOrder
{
    protected $table = 'purchase_orders';
    
    // Inherit all methods and properties from PurchaseOrder
    // This is just an alias to allow multiple Filament resources
}
