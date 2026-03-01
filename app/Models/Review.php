<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $fillable = [
        'review',
        'status',
        'approved_by',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
