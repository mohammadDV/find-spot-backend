<?php

namespace Domain\Business\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Get the business that owns this tag.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope to get only active tags.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope to get only inactive tags.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }
}
