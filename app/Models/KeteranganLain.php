<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeteranganLain extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'keterangan_lains';
    protected $primaryKey = 'kl_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'customer_id',
        'reference_type',
        'reference_id',
        'document_number',
        'document_category',
        'reference_document',
        'type',
        'document_date',
        'total_amount',
        'ppn_amount',
        'grand_total',
        'status',
        'description',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'document_date' => 'date',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(KeteranganLainItem::class, 'kl_id', 'kl_id');
    }

    public function referenceable()
    {
        return $this->morphTo('reference');
    }

    public function getReferenceDocument()
    {
        if ($this->reference_type && $this->reference_id) {
            $model = match($this->reference_type) {
                'invoice' => Invoice::class,
                'nota_menyusul' => NotaMenyusul::class,
                'delivery_note' => DeliveryNote::class,
                default => null,
            };
            
            return $model ? $model::find($this->reference_id) : null;
        }
        
        return null;
    }

    public static function generateDocumentNumber(): string
    {
        $year = date('Y');
        $companyId = session('selected_company_id');
        
        $lastRecord = self::where('company_id', $companyId)
            ->where('document_number', 'like', "KL-{$year}-%")
            ->orderBy('document_number', 'desc')
            ->first();
        
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->document_number);
            $lastNumber = isset($parts[2]) ? (int)$parts[2] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('KL-%s-%04d', $year, $nextNumber);
    }

    public function isPPN(): bool
    {
        return $this->type === 'PPN';
    }
}
