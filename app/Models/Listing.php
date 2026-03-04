<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'location_id',
        'address',
        'description',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function phones()
    {
        return $this->hasMany(ListingPhone::class);
    }

    public function links()
    {
        return $this->hasMany(ListingLink::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }


    public function workingHours()
    {
        return $this->hasMany(ListingWorkingHour::class);
    }

}
