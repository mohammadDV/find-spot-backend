<?php

namespace Domain\Business\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Save extends Model
{
    /** @use HasFactory<\Database\Factories\SaveFactory> */
    use HasFactory;

    protected $hidden = [
        'saveable_type'
    ];

    public function saveable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}