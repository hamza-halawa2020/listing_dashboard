<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

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

    protected static function booted(): void
    {
        static::saving(static function (Listing $listing): void {
            $listing->preventDuplicate();
        });
    }

    private function preventDuplicate(): void
    {
        if (! filled($this->name)
            || ! filled($this->category_id)
            || ! filled($this->location_id)
            || ! filled($this->address)) {
            return;
        }

        $query = static::query()
            ->where('name', $this->name)
            ->where('category_id', $this->category_id)
            ->where('location_id', $this->location_id)
            ->where('address', $this->address);

        if ($this->getKey()) {
            $query->where('id', '<>', $this->getKey());
        }

        if ($query->exists()) {
            throw new RuntimeException(__('listing.duplicate'));
        }
    }
}
