<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    private const MEMBERSHIP_USER_ID_LENGTH = 5;
    private const MEMBERSHIP_NATIONAL_SUFFIX_LENGTH = 4;
    private const MEMBERSHIP_SUBSCRIPTION_ID_LENGTH = 5;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'membership_card_number',
        'is_card_issued',
        'card_issued_at',
        'starts_at',
        'ends_at',
        'status',
        'payment_reference',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'is_card_issued' => 'boolean',
        'card_issued_at' => 'datetime',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Subscription $subscription): void {
            if ($subscription->is_card_issued) {
                if (blank($subscription->card_issued_at)) {
                    $subscription->card_issued_at = now();
                }

                return;
            }

            if (filled($subscription->card_issued_at)) {
                $subscription->card_issued_at = null;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function maxFamilyMembersLimit(): int
    {
        $this->loadMissing('subscriptionPlan');

        return max((int) ($this->subscriptionPlan?->max_family_members ?? 0), 0);
    }

    public function availableFamilyMemberSlots(?FamilyMember $ignore = null): int
    {
        $assignedCount = $this->familyMembers()
            ->when(
                $ignore?->exists,
                fn ($query) => $query->whereKeyNot($ignore->getKey()),
            )
            ->count();

        return max($this->maxFamilyMembersLimit() - $assignedCount, 0);
    }

    public function generateMembershipCardNumber(): string
    {
        // Format: [plan code][user id: minimum 5 digits][last 4 digits of national ID][subscription id: minimum 5 digits]
        // Example: user 1 => 00001, user 9999 => 09999. The same rule applies to the subscription id segment.
        // The subscription id suffix also guarantees a different number for each renewal on the same plan.
        $planCodePart = strtoupper((string) ($this->subscriptionPlan?->code ?: 'SUB'));
        $userIdPart = str_pad((string) $this->user_id, self::MEMBERSHIP_USER_ID_LENGTH, '0', STR_PAD_LEFT);
        $nationalIdDigits = preg_replace('/\D+/', '', (string) ($this->user?->national_id ?? ''));
        $lastFourDigits = str_pad(substr($nationalIdDigits, -self::MEMBERSHIP_NATIONAL_SUFFIX_LENGTH), self::MEMBERSHIP_NATIONAL_SUFFIX_LENGTH, '0', STR_PAD_LEFT);
        $subscriptionIdPart = str_pad((string) ($this->getKey() ?? 0), self::MEMBERSHIP_SUBSCRIPTION_ID_LENGTH, '0', STR_PAD_LEFT);

        return "{$planCodePart}{$userIdPart}{$lastFourDigits}{$subscriptionIdPart}";
    }
}
