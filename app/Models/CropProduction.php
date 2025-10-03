<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CropProduction extends Model
{
    protected $fillable = [
        'region_id',
        'crop_name',
        'production_volume',
        'unit',
        'harvest_date',
        'fiscal_year',
    ];

    protected $casts = [
        'production_volume' => 'decimal:2',
        'harvest_date' => 'date',
        'fiscal_year' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the region that owns this crop production
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
