<?php

namespace Domain\Address\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;
    protected $guarded = [];

    public function areas() {
        return $this->hasMany(Area::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }
}
