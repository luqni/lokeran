<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    protected $guarded = [];

    protected static function booted()
    {
        static::created(function ($job) {
            $users = \App\Models\User::whereNotNull('skills')->get();
            $usersToNotify = collect();
            
            foreach ($users as $user) {
                $skills = array_filter(array_map('trim', explode(',', $user->skills)));
                foreach ($skills as $skill) {
                    if (stripos($job->job_title, $skill) !== false || stripos((string)$job->requirements, $skill) !== false) {
                        $usersToNotify->push($user);
                        break;
                    }
                }
            }

            if ($usersToNotify->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Notifications\NewJobNotification($job));
            }
        });
    }

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
