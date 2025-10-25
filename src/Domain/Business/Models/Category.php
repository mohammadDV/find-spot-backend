<?php

namespace Domain\Business\Models;

use Domain\Business\Models\Facility;
use Domain\Business\Models\Service;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filters()
    {
        return $this->belongsToMany(Filter::class, 'category_filter', 'category_id', 'filter_id');
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_category', 'category_id', 'business_id');
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'category_facility', 'category_id', 'facility_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the image attribute with fallback to parent image
     */
    public function getImageAttribute($value)
    {
        // If this category has an image, return it
        if (!empty($value)) {
            return $value;
        }

        // If no image and has a parent, try to get parent's image
        if ($this->parent_id && $this->parent) {
            return $this->parent->image;
        }

        // Return null if no image found
        return null;
    }

    /**
     * Get the full image URL
     */
    public function getImageUrlAttribute()
    {
        $image = $this->getImageAttribute($this->attributes['image'] ?? null);

        if (empty($image)) {
            return null;
        }

        return $image;
    }
}
