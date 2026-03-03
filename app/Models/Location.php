<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    private const PRIORITY_NAME_GROUPS = [
        ['القاهرة', 'القاهره'],
        ['الجيزة', 'الجيزه'],
        ['القليوبية', 'القليوبيه'],
        ['الإسكندرية', 'الإسكندريه', 'الاسكندرية', 'الاسكندريه', 'اسكندرية', 'اسكندريه'],
        ['الشرقية', 'الشرقيه'],
    ];

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
        return $this->hasMany(Location::class, 'parent_id')->orderedForDisplay();
    }

    public function scopeOrderedForDisplay(Builder $query): Builder
    {
        $cases = [];
        $bindings = [];

        foreach (self::PRIORITY_NAME_GROUPS as $index => $names) {
            $cases[] = 'WHEN TRIM(name) IN (' . implode(', ', array_fill(0, count($names), '?')) . ") THEN {$index}";
            array_push($bindings, ...$names);
        }

        return $query
            ->orderByRaw(
                'CASE ' . implode(' ', $cases) . ' ELSE ' . count(self::PRIORITY_NAME_GROUPS) . ' END',
                $bindings,
            )
            ->orderBy('name');
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
