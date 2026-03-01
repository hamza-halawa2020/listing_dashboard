<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingWorkingHour extends Model
{
    protected $fillable = [
        'listing_id',
        'day',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
