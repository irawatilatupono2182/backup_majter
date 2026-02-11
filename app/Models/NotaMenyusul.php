<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaMenyusul extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'nota_menyusuls';
    protected $primaryKey = 'nm_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'customer_id',
        'sj_id',
        'converted_to_invoice_id',
        'converted_at',
        'nota_number',
        'po_number',
        'type',
        'nota_date',
        'estimated_payment_date',
        'total_amount',
        'ppn_amount',
        'grand_total',
        'status',
        'notes',
        'payment_notes',
        'created_by',
    ];

    protected $casts = [
        'nota_date' => 'date',
        'estimated_payment_date' => 'date',
        'converted_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
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

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class, 'sj_id', 'sj_id');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id', 'invoice_id');
    }

    public function isConverted(): bool
    {
        return !is_null($this->converted_to_invoice_id);
    }

    public function canConvert(): bool
    {
        return !$this->isConverted() && in_array($this->status, ['Draft', 'Approved']);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(NotaMenyusulItem::class, 'nm_id', 'nm_id');
    }

    public static function generateNotaNumber(): string
    {
        $year = date('Y');
        $companyId = session('selected_company_id');
        
        $lastRecord = self::where('company_id', $companyId)
            ->where('nota_number', 'like', "NM-{$year}-%")
            ->orderBy('nota_number', 'desc')
            ->first();
        
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->nota_number);
            $lastNumber = isset($parts[2]) ? (int)$parts[2] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('NM-%s-%04d', $year, $nextNumber);
    }

    public function isPPN(): bool
    {
        return $this->type === 'PPN';
    }
}
