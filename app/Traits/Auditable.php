<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->auditCreated();
        });

        static::updated(function ($model) {
            $model->auditUpdated();
        });

        static::deleted(function ($model) {
            $model->auditDeleted();
        });
    }

    /**
     * Log model creation.
     */
    protected function auditCreated()
    {
        $this->createAuditLog('created', [], $this->getAuditableAttributes());
    }

    /**
     * Log model updates.
     */
    protected function auditUpdated()
    {
        $changes = $this->getChanges();
        $original = $this->getOriginal();

        if (empty($changes)) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($changes as $key => $value) {
            if (in_array($key, ['updated_at', 'created_at'])) {
                continue;
            }

            $oldValues[$key] = $original[$key] ?? null;
            $newValues[$key] = $value;
        }

        if (!empty($newValues)) {
            $this->createAuditLog('updated', $oldValues, $newValues);
        }
    }

    /**
     * Log model deletion.
     */
    protected function auditDeleted()
    {
        $this->createAuditLog('deleted', $this->getAuditableAttributes(), []);
    }

    /**
     * Create an audit log entry.
     *
     * @param string $action
     * @param array $oldValues
     * @param array $newValues
     */
    protected function createAuditLog(string $action, array $oldValues, array $newValues)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get auditable attributes (excludes timestamps and sensitive fields).
     *
     * @return array
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();

        // Remove non-auditable fields
        $excluded = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return AuditLog::where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
