<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class MembershipCard extends Model
{
    // ── Statuses ────────────────────────────────────────────────────
    public const STATUS_UNASSIGNED  = 'unassigned';
    public const STATUS_PENDING     = 'pending_activation';
    public const STATUS_ACTIVE      = 'active';
    public const STATUS_REJECTED    = 'rejected';

    protected $fillable = [
        'card_number',
        'subscription_plan_id',
        'starts_at',
        'ends_at',
        'status',
        'user_id',
        'national_id',
        'activation_requested_at',
        'activation_approved_at',
        'notes',
        'created_by',
        'subscription_id',
    ];

    protected $casts = [
        'starts_at'                => 'date',
        'ends_at'                  => 'date',
        'activation_requested_at'  => 'datetime',
        'activation_approved_at'   => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Activation Flow ──────────────────────────────────────────────

    /**
     * User requests activation by submitting their national ID.
     * Card must be unassigned or previously rejected to re-request.
     */
    public function requestActivation(string $nationalId): void
    {
        $this->update([
            'status'                   => self::STATUS_PENDING,
            'national_id'              => $nationalId,
            'activation_requested_at'  => now(),
            'activation_approved_at'   => null,
            'notes'                    => null,
        ]);
    }

    /**
     * Admin approves activation:
     *  1. Find the user by national_id (must already exist in the system)
     *  2. Create a subscription from the card's plan + dates
     *  3. Link the card to the user + subscription
     */
    public function approveActivation(?string $notes = null): void
    {
        DB::transaction(function () use ($notes) {
            // Find user by national_id
            $user = User::where('national_id', $this->national_id)->first();

            if (! $user) {
                throw new \RuntimeException(__('No user found with national ID :id', ['id' => $this->national_id]));
            }

            // Sync national_id to user profile if not already set
            if (blank($user->national_id)) {
                $user->update(['national_id' => $this->national_id]);
            }

            // Create subscription from the card's plan
            $subscription = Subscription::create([
                'user_id'             => $user->id,
                'subscription_plan_id' => $this->subscription_plan_id,
                'starts_at'           => $this->starts_at,
                'ends_at'             => $this->ends_at,
                'status'              => 'active',
                'is_card_issued'      => true,
                'card_issued_at'      => now(),
                'payment_method'      => 'free',
                'notes'               => __('Activated via membership card :number', ['number' => $this->card_number]),
            ]);

            // Generate membership card number on the subscription
            $subscription->update([
                'membership_card_number' => $this->card_number,
            ]);

            // Link card to user + subscription
            $this->update([
                'status'                 => self::STATUS_ACTIVE,
                'user_id'               => $user->id,
                'subscription_id'        => $subscription->id,
                'activation_approved_at' => now(),
                'notes'                 => $notes,
            ]);
        });
    }

    /**
     * Admin rejects the activation request.
     */
    public function rejectActivation(string $reason): void
    {
        $this->update([
            'status'   => self::STATUS_REJECTED,
            'notes'    => $reason,
            'activation_approved_at' => null,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_UNASSIGNED  => __('Unassigned'),
            self::STATUS_PENDING     => __('Pending Activation'),
            self::STATUS_ACTIVE      => __('Card Active'),
            self::STATUS_REJECTED    => __('Rejected'),
            default                  => $status,
        };
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_UNASSIGNED  => 'gray',
            self::STATUS_PENDING     => 'warning',
            self::STATUS_ACTIVE      => 'success',
            self::STATUS_REJECTED    => 'danger',
            default                  => 'gray',
        };
    }

    /**
     * Generate the next sequential card number for a given prefix.
     * Looks at existing cards with the same prefix to find the max number.
     */
    public static function generateNextCardNumber(string $prefix, int $startFrom = 1): string
    {
        $existingNumbers = static::where('card_number', 'like', "{$prefix}-%")
            ->pluck('card_number')
            ->map(function ($num) use ($prefix) {
                $parts = explode('-', $num);
                return (int) (end($parts));
            })
            ->max();

        $next = max($existingNumbers + 1, $startFrom);

        return $prefix . '-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
