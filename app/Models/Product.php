<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'product_code',
        'name',
        'description',
        'unit',
        'base_price',
        'default_discount_percent',
        'min_stock_alert',
        'category',
        'product_type',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'default_discount_percent' => 'decimal:2',
        'min_stock_alert' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class, 'product_id', 'product_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'product_id', 'product_id');
    }

    public function isStock(): bool
    {
        return $this->product_type === 'STOCK';
    }

    public function isCatalog(): bool
    {
        return $this->product_type === 'CATALOG';
    }
}