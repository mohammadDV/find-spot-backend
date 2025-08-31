<?php

namespace Domain\Business\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    /** @use HasFactory<\Database\Factories\FileFactory> */
    use HasFactory;

    protected $fillable = [
        'path',
        'type',
        'status',
        'priority',
    ];

    protected $casts = [
        'status' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Get the business that owns the file.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
