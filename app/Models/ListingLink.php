<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingLink extends Model
{
    protected $fillable = [
        'listing_id',
        'title',
        'url',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
