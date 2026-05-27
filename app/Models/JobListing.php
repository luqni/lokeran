<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    protected $guarded = [];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
