<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingDistribution extends Model
{
    protected $fillable = [
        'department_id',
        'project_id',
        'amount',
        'funding_source',
        'fiscal_year',
        'allocated_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fiscal_year' => 'integer',
        'allocated_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the department that owns this funding distribution
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the project associated with this funding
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
