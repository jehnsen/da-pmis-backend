<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'target_date',
        'completion_date',
        'status',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completion_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns this milestone
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
