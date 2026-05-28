<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
        ];
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_jobs', 'job_listing_id', 'user_id')->withTimestamps();
    }
}
