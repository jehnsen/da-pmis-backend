<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DocumentCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get documents in this category
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_category_pivot')
            ->withTimestamps();
    }
}
