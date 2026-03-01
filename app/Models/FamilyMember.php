<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    protected $fillable = [
        'subscription_id',
        'name',
        'national_id',
        'relation',
        'birth_date',
        'gender',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
