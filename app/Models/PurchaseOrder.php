<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'purchase_orders';
    protected $primaryKey = 'po_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'ph_id',
        'supplier_id',
        'po_number',
        'type',
        'order_date',
        'expected_delivery',
        'due_date',
        'payment_terms_days',
        'status',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery' => 'date',
        'due_date' => 'date',
        'payment_terms_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function priceQuotation(): BelongsTo
    {
        return $this->belongsTo(PriceQuotation::class, 'ph_id', 'ph_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id', 'po_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class, 'po_id', 'po_id');
    }

    public function payables(): HasMany
    {
        return $this->hasMany(\App\Models\Payable::class, 'purchase_order_id', 'po_id');
    }

    public function getTotalAmount(): float
    {
        return $this->items->sum('subtotal');
    }

    public function isPPN(): bool
    {
        return $this->type === 'PPN';
    }

    public function getPPNAmount(): float
    {
        return $this->isPPN() ? $this->getTotalAmount() * 0.11 : 0;
    }

    public function getGrandTotal(): float
    {
        return $this->getTotalAmount() + $this->getPPNAmount();
    }

    public function getReceivedPercentage(): float
    {
        $totalOrdered = $this->items->sum('qty_ordered');
        $totalReceived = $this->items->sum('qty_received');
        
        return $totalOrdered > 0 ? ($totalReceived / $totalOrdered) * 100 : 0;
    }

    // Method to receive items and update stock
    public function receiveItems(array $receivedItems): void
    {
        foreach ($receivedItems as $itemData) {
            $poItem = $this->items()->find($itemData['po_item_id']);
            if ($poItem && $poItem->product->product_type === 'STOCK') {
                // Update received quantity
                $previousReceived = $poItem->qty_received;
                $newReceived = $itemData['qty_received'];
                $actualReceived = $newReceived - $previousReceived;

                if ($actualReceived > 0) {
                    $poItem->update(['qty_received' => $newReceived]);

                    // Create stock movement
                    \App\Models\StockMovement::create([
                        'company_id' => $this->company_id,
                        'product_id' => $poItem->product_id,
                        'movement_type' => 'in',
                        'quantity' => $actualReceived,
                        'unit_cost' => $poItem->unit_price,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $this->po_id,
                        'batch_number' => $itemData['batch_number'] ?? null,
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'notes' => "Received from PO {$this->po_number}",
                        'created_by' => auth()->id(),
                    ]);

                    // Update or create stock record
                    $stock = \App\Models\Stock::firstOrCreate(
                        [
                            'company_id' => $this->company_id,
                            'product_id' => $poItem->product_id,
                            'batch_number' => $itemData['batch_number'] ?? null,
                        ],
                        [
                            'quantity' => 0,
                            'reserved_quantity' => 0,
                            'available_quantity' => 0,
                            'minimum_stock' => 0,
                            'unit_cost' => $poItem->unit_price,
                            'expiry_date' => $itemData['expiry_date'] ?? null,
                            'created_by' => auth()->id(),
                        ]
                    );

                    $stock->quantity += $actualReceived;
                    $stock->unit_cost = $poItem->unit_price; // Update with latest cost
                    $stock->updateAvailableQuantity();
                }
            }
        }

        // Update PO status based on received items
        $this->updateStatus();
    }

    private function updateStatus(): void
    {
        $receivedPercentage = $this->getReceivedPercentage();
        
        if ($receivedPercentage >= 100) {
            $this->status = 'completed';
        } elseif ($receivedPercentage > 0) {
            $this->status = 'partial';
        }
        
        $this->save();
    }

    // Payment-related methods
    public function getTotalPaid(): float
    {
        return $this->payments()->sum('amount');
    }

    public function getRemainingAmount(): float
    {
        return max(0, $this->getGrandTotal() - $this->getTotalPaid());
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->getTotalPaid();
        $grandTotal = $this->getGrandTotal();

        if ($totalPaid >= $grandTotal) {
            $this->payment_status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }

        $this->save();
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->payment_status === 'paid') {
            return false;
        }

        return now()->greaterThan($this->due_date);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    public function getDaysTillDue(): int
    {
        if (!$this->due_date || $this->payment_status === 'paid') {
            return 0;
        }

        return now()->diffInDays($this->due_date, false);
    }

    
    /**
     * Get outstanding payable amount for this PO
     */
    public function getOutstandingPayableAmount(): float
    {
        $payable = $this->payables()->first();
        return $payable ? $payable->remaining_amount : 0;
    }
    
    /**
     * Check if PO has outstanding payable
     */
    public function hasOutstandingPayable(): bool
    {
        return $this->getOutstandingPayableAmount() > 0;
    }
}
