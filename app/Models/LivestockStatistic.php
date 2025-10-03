<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivestockStatistic extends Model
{
    protected $fillable = [
        'region_id',
        'livestock_type',
        'population',
        'unit',
        'recorded_date',
        'fiscal_year',
    ];

    protected $casts = [
        'population' => 'integer',
        'recorded_date' => 'date',
        'fiscal_year' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the region that owns this livestock statistic
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
