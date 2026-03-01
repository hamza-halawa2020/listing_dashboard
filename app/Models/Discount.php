<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'listing_id',
        'service_name',
        'min_percentage',
        'max_percentage',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
