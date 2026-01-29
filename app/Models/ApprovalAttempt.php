<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAttempt extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'attempted_action',
        'result',
        'failure_reason',
        'user_level',
        'project_status_at_attempt',
        'ip_address',
        'user_agent',
        'request_data',
    ];

    protected $casts = [
        'request_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project this attempt relates to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who made the attempt
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for failed attempts
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('result', ['failed', 'unauthorized', 'conflict']);
    }

    /**
     * Scope for successful attempts
     */
    public function scopeSuccessful($query)
    {
        return $query->where('result', 'success');
    }

    /**
     * Scope for unauthorized attempts
     */
    public function scopeUnauthorized($query)
    {
        return $query->where('result', 'unauthorized');
    }

    /**
     * Log an approval attempt
     */
    public static function logAttempt(
        int $projectId,
        int $userId,
        string $action,
        string $result,
        ?string $failureReason = null,
        ?string $userLevel = null,
        ?string $projectStatus = null,
        ?array $requestData = null
    ): void {
        try {
            self::create([
                'project_id' => $projectId,
                'user_id' => $userId,
                'attempted_action' => $action,
                'result' => $result,
                'failure_reason' => $failureReason,
                'user_level' => $userLevel,
                'project_status_at_attempt' => $projectStatus,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'request_data' => $requestData,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to log approval attempt: ' . $e->getMessage());
        }
    }
}
