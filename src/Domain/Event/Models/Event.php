<?php

namespace Domain\Event\Models;

use Domain\Business\Models\Favorite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $guarded = [];

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable', 'favoritable_type', 'favoritable_id');
    }
}
