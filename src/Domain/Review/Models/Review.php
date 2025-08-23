<?php

namespace Domain\Review\Models;

use Domain\Business\Models\Business;
use Domain\Business\Models\ServiceVote;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function business() {
        return $this->belongsTo(Business::class);
    }

    public function serviceVotes() {
        return $this->hasMany(ServiceVote::class);
    }
}