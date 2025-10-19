<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNote extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'delivery_notes';
    protected $primaryKey = 'sj_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'customer_id',
        'sj_number',
        'type',
        'delivery_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class, 'sj_id', 'sj_id');
    }

    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class, 'sj_id', 'sj_id');
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

    public function hasInvoice(): bool
    {
        return $this->invoice()->exists();
    }

    // Method to deliver items and update stock
    public function deliverItems(): void
    {
        if ($this->status !== 'pending') {
            return; // Only process pending delivery notes
        }

        foreach ($this->items as $item) {
            if ($item->product->product_type === 'STOCK') {
                // Create stock movement for outgoing stock
                \App\Models\StockMovement::create([
                    'company_id' => $this->company_id,
                    'product_id' => $item->product_id,
                    'movement_type' => 'out',
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_price,
                    'reference_type' => 'delivery_note',
                    'reference_id' => $this->delivery_note_id,
                    'notes' => "Delivered via SJ {$this->delivery_note_number}",
                    'created_by' => auth()->id(),
                ]);

                // Update stock records - use FIFO method
                $this->updateStockFIFO($item->product_id, $item->quantity);
            }
        }

        // Update delivery note status
        $this->status = 'delivered';
        $this->save();
    }

    private function updateStockFIFO(string $productId, float $quantityToDeduct): void
    {
        $stocks = \App\Models\Stock::where('company_id', $this->company_id)
            ->where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO - First In, First Out
            ->get();

        $remainingToDeduct = $quantityToDeduct;

        foreach ($stocks as $stock) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $availableInThisStock = $stock->available_quantity;
            $deductFromThisStock = min($remainingToDeduct, $availableInThisStock);

            $stock->quantity -= $deductFromThisStock;
            $stock->updateAvailableQuantity();

            $remainingToDeduct -= $deductFromThisStock;
        }

        if ($remainingToDeduct > 0) {
            // Log warning if not enough stock
            \Log::warning("Insufficient stock for product {$productId}. Remaining to deduct: {$remainingToDeduct}");
        }
    }

    // Method to check stock availability before delivery
    public function checkStockAvailability(): array
    {
        $stockIssues = [];

        foreach ($this->items as $item) {
            if ($item->product->product_type === 'STOCK') {
                $availableStock = \App\Models\Stock::where('company_id', $this->company_id)
                    ->where('product_id', $item->product_id)
                    ->sum('available_quantity');

                if ($availableStock < $item->quantity) {
                    $stockIssues[] = [
                        'product' => $item->product->name,
                        'required' => $item->quantity,
                        'available' => $availableStock,
                        'shortage' => $item->quantity - $availableStock,
                    ];
                }
            }
        }

        return $stockIssues;
    }
}