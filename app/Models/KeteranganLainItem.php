<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeteranganLainItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'keterangan_lain_items';
    protected $primaryKey = 'item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'keterangan_id',
        'product_id',
        'qty',
        'unit',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function keterangan(): BelongsTo
    {
        return $this->belongsTo(KeteranganLain::class, 'keterangan_id', 'keterangan_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
