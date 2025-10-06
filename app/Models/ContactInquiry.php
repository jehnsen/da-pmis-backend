<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Scope for pending inquiries
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for responded inquiries
     */
    public function scopeResponded($query)
    {
        return $query->where('status', 'responded');
    }
}
