<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * ✅ CRITICAL FIX #6: Audit Trail System
 * Track all changes: WHO changed WHAT WHEN and HOW
 */
trait HasAuditLog
{
    // Static cache untuk menyimpan original attributes
    protected static $auditOriginalCache = [];
    
    protected static function bootHasAuditLog()
    {
        static::created(function ($model) {
            static::logAudit('created', $model, null, $model->getAttributes());
        });

        static::updating(function ($model) {
            // Simpan ke static cache menggunakan object hash sebagai key
            $hash = spl_object_hash($model);
            static::$auditOriginalCache[$hash] = $model->getOriginal();
        });

        static::updated(function ($model) {
            // Ambil dari static cache
            $hash = spl_object_hash($model);
            $original = static::$auditOriginalCache[$hash] ?? [];
            $changes = $model->getChanges();
            
            // Bersihkan cache setelah digunakan
            unset(static::$auditOriginalCache[$hash]);
            
            // Remove timestamps from changes to focus on business data
            unset($changes['updated_at'], $changes['created_at']);
            
            if (!empty($changes)) {
                static::logAudit('updated', $model, $original, $changes);
            }
        });

        static::deleted(function ($model) {
            static::logAudit('deleted', $model, $model->getAttributes(), null);
        });
    }

    protected static function logAudit(string $action, $model, ?array $oldValues, ?array $newValues)
    {
        $user = Auth::user();
        $modelName = class_basename($model);
        $modelId = $model->getKey();
        
        $auditData = [
            'action' => $action,
            'model' => $modelName,
            'model_id' => $modelId,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'user_email' => $user?->email ?? 'system@adamjaya.com',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            // created_at will be set automatically by database
        ];

        // Log to database
        \DB::table('audit_logs')->insert($auditData);
        
        // Also log to file for backup
        Log::channel('audit')->info("[$action] $modelName #$modelId", $auditData);
    }

    /**
     * Get audit history for this model
     */
    public function auditLogs()
    {
        return \DB::table('audit_logs')
            ->where('model', class_basename($this))
            ->where('model_id', $this->getKey())
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
