<?php

namespace Domain\Event\Models;

use Domain\Business\Models\Save;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $guarded = [];

    public function saves()
    {
        return $this->morphMany(Save::class, 'saveable');
    }
}
