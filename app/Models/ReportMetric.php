<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportMetric extends Model
{
    protected $fillable = [
        'progress_report_id',
        'metric_name',
        'metric_value',
        'previous_value',
    ];

    protected $casts = [
        'metric_value' => 'decimal:2',
        'previous_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the progress report that owns this metric
     */
    public function progressReport(): BelongsTo
    {
        return $this->belongsTo(ProgressReport::class);
    }
}
