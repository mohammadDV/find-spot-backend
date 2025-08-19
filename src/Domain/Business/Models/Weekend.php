<?php

namespace Domain\Business\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weekend extends Model
{
    /** @use HasFactory<\Database\Factories\WeekendFactory> */
    use HasFactory;

    protected $guarded = [];

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_weekend', 'weekend_id', 'business_id');
    }
}