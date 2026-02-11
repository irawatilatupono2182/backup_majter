<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaMenyusulItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'nota_menyusul_items';
    protected $primaryKey = 'item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nota_id',
        'product_id',
        'qty',
        'unit',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(NotaMenyusul::class, 'nota_id', 'nota_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
