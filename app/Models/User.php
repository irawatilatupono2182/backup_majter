<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name', // full_name
        'email',
        'password',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function companyRoles(): HasMany
    {
        return $this->hasMany(UserCompanyRole::class, 'user_id', 'id');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_company_roles', 'user_id', 'company_id')
            ->withPivot('role', 'is_default')
            ->withTimestamps();
    }

    public function getDefaultCompany()
    {
        $defaultRole = $this->companyRoles()->where('is_default', true)->first();
        return $defaultRole ? $defaultRole->company : null;
    }

    public function hasRoleInCompany(string $role, string $companyId): bool
    {
        return $this->companyRoles()
            ->where('company_id', $companyId)
            ->where('role', $role)
            ->exists();
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Set selected company in session when accessing panel
        if (!session('selected_company_id')) {
            $firstCompany = $this->companies()->first();
            if ($firstCompany) {
                session(['selected_company_id' => $firstCompany->company_id]);
            }
        }
        
        // Allow access if user is active
        return $this->is_active === true;
    }

    /**
     * Override hasVerifiedEmail to always return true
     * This disables email verification requirement
     */
    public function hasVerifiedEmail()
    {
        return true;
    }
}
