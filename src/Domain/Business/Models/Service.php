<?php

namespace Domain\Business\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'category_id',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Get the category that the service belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the businesses that offer this service.
     */
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_services', 'service_id', 'business_id');
    }

    /**
     * Scope to get only active services.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope to get only inactive services.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }
}
