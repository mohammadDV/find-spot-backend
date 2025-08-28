<?php

namespace Domain\Review\Models;

use Domain\Business\Models\Business;
use Domain\Business\Models\Service;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    const PENDING = 'pending';
    const APPROVED = 'approved';
    const CANCELLED = 'cancelled';

    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function business() {
        return $this->belongsTo(Business::class);
    }

    public function services() {
        return $this->belongsToMany(Service::class, 'service_votes', 'review_id', 'service_id');
    }

    public function likes() {
        return $this->hasMany(ReviewLike::class)->where('is_like', true);
    }

    public function dislikes() {
        return $this->hasMany(ReviewLike::class)->where('is_like', false);
    }

    public function files() {
        return $this->hasMany(ReviewFile::class);
    }
}
