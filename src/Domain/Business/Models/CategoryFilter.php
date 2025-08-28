<?php

namespace Domain\Business\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryFilter extends Model
{
    /** @use HasFactory<\Database\Factories\FilterFactory> */
    use HasFactory;

    protected $table = 'category_filter';

    public function filter()
    {
        return $this->belongsTo(Filter::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
