<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingPhone extends Model
{
    protected $fillable = [
        'listing_id',
        'phone_number',
        'type',
        'contact_person',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
