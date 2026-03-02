<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = 
    [
        'name',
        'parent_id',
        'type',
        'shipping_cost', // cost when this record is a governorate
    ];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function getDescendantIds()
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getDescendantIds());
        }
        return array_unique($ids);
    }

    protected static function booted()
    {
        static::saving(function (Location $location) {
            if ($location->type === 'governorate') {
                $location->parent_id = null;
            }
        });

        static::deleting(function (Location $location) {
            if ($location->listings()->exists()) {
                throw new \Exception('this location has related listings and cannot be deleted');
            }
            if ($location->children()->exists()) {
                throw new \Exception('this location has child locations and cannot be deleted');
            }
        });
    }
}
