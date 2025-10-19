<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'companies';
    protected $primaryKey = 'company_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'npwp',
        'logo_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(UserCompanyRole::class, 'company_id', 'company_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'company_id', 'company_id');
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'company_id', 'company_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'company_id', 'company_id');
    }
}