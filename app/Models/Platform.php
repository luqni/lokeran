<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $guarded = [];

    public function jobs()
    {
        return $this->hasMany(JobListing::class);
    }
}
