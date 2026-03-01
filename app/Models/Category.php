<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'created_by',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function getBranchIds()
    {
        $ids = [$this->id];

        if ($this->parent_id) {
            $ids[] = $this->parent_id;
        }

        $childrenIds = $this->children()->pluck('id')->toArray();
        
        return array_unique(array_merge($ids, $childrenIds));
    }
}
