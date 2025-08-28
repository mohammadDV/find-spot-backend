<?php

namespace Domain\Review\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewFile extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFileFactory> */
    use HasFactory;

    protected $guarded = [];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}