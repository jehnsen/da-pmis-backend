<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'department_id',
        'report_period',
        'report_date',
        'summary',
        'created_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the department that owns this report
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who created this report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get metrics for this report
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(ReportMetric::class);
    }
}
