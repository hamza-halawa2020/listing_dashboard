<?php

namespace App\Models;

use App\Support\FamilyMemberSubscription;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    protected static function booted(): void
    {
        static::saving(function (FamilyMember $familyMember) {
            if (blank($familyMember->user_id)) {
                return;
            }

            $familyMember->loadMissing('user');

            if (! $familyMember->user) {
                return;
            }

            FamilyMemberSubscription::ensureValidForUser(
                $familyMember->user,
                $familyMember->subscription_id,
                $familyMember->exists ? $familyMember : null,
            );
        });
    }
}
