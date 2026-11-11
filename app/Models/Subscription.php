<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    private const MEMBERSHIP_NATIONAL_SUFFIX_LENGTH = 4;
    private const MEMBERSHIP_SUBSCRIPTION_ID_LENGTH = 5;

    // Card activation statuses
    const CARD_STATUS_UNASSIGNED   = 'unassigned';
    const CARD_STATUS_PENDING      = 'pending_activation';
    const CARD_STATUS_ACTIVE       = 'active';
    const CARD_STATUS_REJECTED     = 'rejected';

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'membership_card_number',
        'is_card_issued',
        'card_issued_at',
        'reward_points_granted_at',
        'starts_at',
        'ends_at',
        'status',
        'payment_reference',
        'payment_method',
        'notes',
        // Card activation
        'card_activation_status',
        'card_activation_national_id',
        'card_activation_requested_at',
        'card_activation_approved_at',
        'card_activation_notes',
    ];

    protected $casts = [
        'is_card_issued'               => 'boolean',
        'card_issued_at'               => 'datetime',
        'reward_points_granted_at'     => 'datetime',
        'starts_at'                    => 'date',
        'ends_at'                      => 'date',
        'card_activation_requested_at' => 'datetime',
        'card_activation_approved_at'  => 'datetime',
    ];

    /**
     * Issue a sequential batch of card numbers on this subscription.
     * Example prefix: "GOLD", count: 3  →  GOLD-00001, GOLD-00002, GOLD-00003
     * Only works when no card has been issued yet (is_card_issued = false).
     * Returns the generated card number.
     */
    public function issueCard(string $cardNumber): void
    {
        $this->update([
            'membership_card_number'  => $cardNumber,
            'is_card_issued'          => true,
            'card_activation_status'  => self::CARD_STATUS_UNASSIGNED,
            'card_activation_national_id'    => null,
            'card_activation_requested_at'   => null,
            'card_activation_approved_at'    => null,
            'card_activation_notes'          => null,
        ]);
    }

    /**
     * User requests activation: stores their national_id and sets status to pending.
     */
    public function requestActivation(string $nationalId): void
    {
        $this->update([
            'card_activation_status'       => self::CARD_STATUS_PENDING,
            'card_activation_national_id'  => $nationalId,
            'card_activation_requested_at' => now(),
            'card_activation_approved_at'  => null,
            'card_activation_notes'        => null,
        ]);
    }

    /**
     * Admin approves the activation request.
     */
    public function approveActivation(?string $notes = null): void
    {
        // Sync national_id to the user profile if not already set
        if (filled($this->card_activation_national_id) && blank($this->user?->national_id)) {
            $this->user->update(['national_id' => $this->card_activation_national_id]);
        }

        $this->update([
            'card_activation_status'      => self::CARD_STATUS_ACTIVE,
            'card_activation_approved_at' => now(),
            'card_activation_notes'       => $notes,
        ]);
    }

    /**
     * Admin rejects the activation request.
     */
    public function rejectActivation(string $reason): void
    {
        $this->update([
            'card_activation_status'      => self::CARD_STATUS_REJECTED,
            'card_activation_approved_at' => null,
            'card_activation_notes'       => $reason,
        ]);
    }

    /** Readable label for card_activation_status */
    public static function cardActivationStatusLabel(string $status): string
    {
        return match ($status) {
            self::CARD_STATUS_UNASSIGNED => __('Unassigned'),
            self::CARD_STATUS_PENDING    => __('Pending Activation'),
            self::CARD_STATUS_ACTIVE     => __('Card Active'),
            self::CARD_STATUS_REJECTED   => __('Rejected'),
            default                      => $status,
        };
    }

    public static function cardActivationStatusColor(string $status): string
    {
        return match ($status) {
            self::CARD_STATUS_UNASSIGNED => 'gray',
            self::CARD_STATUS_PENDING    => 'warning',
            self::CARD_STATUS_ACTIVE     => 'success',
            self::CARD_STATUS_REJECTED   => 'danger',
            default                      => 'gray',
        };
    }

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
  
        $planCode = strtoupper($this->subscriptionPlan?->code ?: 'SUB');
        $nationalIdDigits = preg_replace('/\D+/', '', (string) ($this->user?->national_id ?? ''));
        $lastFourDigits = str_pad(substr($nationalIdDigits, -self::MEMBERSHIP_NATIONAL_SUFFIX_LENGTH), self::MEMBERSHIP_NATIONAL_SUFFIX_LENGTH, '0', STR_PAD_LEFT);
        $subscriptionIdPart = str_pad((string) ($this->getKey() ?? 0), self::MEMBERSHIP_SUBSCRIPTION_ID_LENGTH, '0', STR_PAD_LEFT);

        return "{$planCode}{$subscriptionIdPart}{$lastFourDigits}";

    }
}
