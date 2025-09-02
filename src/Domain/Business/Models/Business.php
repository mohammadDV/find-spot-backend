<?php

namespace Domain\Business\Models;

use Domain\Address\Models\Area;
use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Business\Models\Category;
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
        'from_monday' => 'integer',
        'to_monday' => 'integer',
        'from_tuesday' => 'integer',
        'to_tuesday' => 'integer',
        'from_wednesday' => 'integer',
        'to_wednesday' => 'integer',
        'from_thursday' => 'integer',
        'to_thursday' => 'integer',
        'from_friday' => 'integer',
        'to_friday' => 'integer',
        'from_saturday' => 'integer',
        'to_saturday' => 'integer',
        'from_sunday' => 'integer',
        'to_sunday' => 'integer',
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

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable', 'favoritable_type', 'favoritable_id');
    }

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

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function serviceVotes()
    {
        return $this->hasMany(ServiceVote::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_votes', 'business_id', 'service_id');
    }

    // Helper methods for working hours
    public function getWorkingHoursAttribute()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            $from = $this->{"from_$day"};
            $to = $this->{"to_$day"};

            if ($from !== null && $to !== null) {
                $hours[$day] = [
                    'from' => $this->minutesToTime($from),
                    'to' => $this->minutesToTime($to),
                    'from_minutes' => $from,
                    'to_minutes' => $to,
                ];
            }
        }

        return $hours;
    }

    private function minutesToTime($minutes)
    {
        if ($minutes === null) return null;

        $hours = intval($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function setWorkingHours($day, $fromTime, $toTime)
    {
        $this->{"from_$day"} = $this->timeToMinutes($fromTime);
        $this->{"to_$day"} = $this->timeToMinutes($toTime);
    }

    private function timeToMinutes($time)
    {
        if (empty($time)) return null;

        $parts = explode(':', $time);
        if (count($parts) !== 2) return null;

        return intval($parts[0]) * 60 + intval($parts[1]);
    }
}
