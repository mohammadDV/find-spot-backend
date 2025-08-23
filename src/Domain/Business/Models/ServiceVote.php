<?php

namespace Domain\Business\Models;

use Domain\Business\Models\Business;
use Domain\Business\Models\Service;
use Domain\Review\Models\Review;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceVote extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceVoteFactory> */
    use HasFactory;

    protected $guarded = [];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

}
