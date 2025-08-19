<?php

namespace Domain\Business\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    /** @use HasFactory<\Database\Factories\FilterFactory> */
    use HasFactory;

    protected $guarded = [];

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_filter', 'filter_id', 'business_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_filter', 'filter_id', 'category_id');
    }
}
