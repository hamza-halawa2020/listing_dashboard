<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'location_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'status',
        'attachment',
        'notes',
        'delivery_required',
        'delivery_name',
        'delivery_phone',
        'delivery_address',
        'shipping_cost',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'delivery_required' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
