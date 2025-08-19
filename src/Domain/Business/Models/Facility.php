<?php

namespace Domain\Business\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    /** @use HasFactory<\Database\Factories\FacilityFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'category_id',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Get the category that the facility belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the businesses that have this facility.
     */
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_facility', 'facility_id', 'business_id');
    }
}
