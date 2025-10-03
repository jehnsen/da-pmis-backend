<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentKpi extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'target_value',
        'current_value',
        'unit',
        'fiscal_year',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'fiscal_year' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the department that owns this KPI
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
