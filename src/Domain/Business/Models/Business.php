<?php

namespace Domain\Business\Models;

use Domain\Address\Models\Area;
use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Business\Models\File;
use Domain\Business\Models\Facility;
use Domain\Business\Models\Service;
use Domain\Business\Models\Tag;
use Domain\Review\Models\Review;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessFactory> */
    use HasFactory;

    const PENDING = "pending";
    const APPROVED = "approved";
    const REJECT = "reject";

    protected $guarded = [];

    protected $casts = [
        'start_amount' => 'integer',
        'active' => 'integer',
        'vip' => 'boolean',
        'priority' => 'integer',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'business_category', 'business_id', 'category_id');
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'business_facility', 'business_id', 'facility_id');
    }

    public function filters()
    {
        return $this->belongsToMany(Filter::class, 'business_filter', 'business_id', 'filter_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    // public function services()
    // {
    //     return $this->belongsToMany(Service::class, 'business_services', 'business_id', 'service_id');
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function weekends()
    {
        return $this->belongsToMany(Weekend::class, 'business_weekend', 'business_id', 'weekend_id');
    }

    public function saves()
    {
        return $this->morphMany(Save::class, 'saveable');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
