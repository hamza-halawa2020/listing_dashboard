<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PointTransaction;
use App\Models\RegistrationRewardSetting;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReferralService
{
    public function __construct(
        private readonly SystemNotificationService $notifications,
    ) {}
    public function isUserEligibleToRefer(User $user): bool
    {
        return $user->payments()->where('status', 'completed')->exists();
    }

    public function registerReferralForUser(User $user, ?string $referralCode): ?Referral
    {
        $normalizedCode = $this->normalizeReferralCode($referralCode);

        if (! $this->isEnabled() || blank($normalizedCode)) {
            return null;
        }

        $referrer = User::query()
            ->whereRaw('UPPER(referral_code) = ?', [$normalizedCode])
            ->first();

        if (! $referrer || $referrer->is($user)) {
            return null;
        }

        // Only users who have at least one completed payment can refer others
        if (! $this->isUserEligibleToRefer($referrer)) {
            return null;
        }

        return DB::transaction(function () use ($user, $referrer, $normalizedCode): ?Referral {
            $existingReferral = Referral::query()
                ->where('referred_user_id', $user->getKey())
                ->lockForUpdate()
                ->first();

            if ($existingReferral) {
                return $existingReferral;
            }

            $user->forceFill([
                'referred_by_user_id' => $referrer->getKey(),
            ])->saveQuietly();

            // Rewards are determined per-plan at payment time, store zeros for now
            $referral = Referral::create([
                'referrer_user_id' => $referrer->getKey(),
                'referred_user_id' => $user->getKey(),
                'referral_code_used' => $normalizedCode,
                'status' => Referral::STATUS_PENDING,
                'trigger_type' => Referral::TRIGGER_FIRST_PAYMENT,
                'referrer_points_awarded' => 0,
                'referee_reward_type' => Referral::REWARD_NONE,
                'referee_reward_value' => 0,
            ]);

            return $referral->fresh();
        });
    }

    /**
     * @return array{referral: Referral|null, original_amount: float, discount_amount: float, final_amount: float}
     */
    public function applyRefereeDiscount(User $user, float $originalAmount, ?SubscriptionPlan $plan = null): array
    {
        $originalAmount = round(max($originalAmount, 0), 2);

        if (! $this->isEnabled() || ! $plan) {
            return $this->noDiscount($originalAmount);
        }

        $referral = Referral::query()
            ->with('refereeRewardPayment')
            ->where('referred_user_id', $user->getKey())
            ->where('status', Referral::STATUS_PENDING)
            ->first();

        if (! $referral) {
            return $this->noDiscount($originalAmount);
        }

        // Check if discount was already used on a non-failed payment
        $reservedPayment = $referral->refereeRewardPayment;
        if ($reservedPayment && ! in_array($reservedPayment->status, ['failed', 'refunded'], true)) {
            return $this->noDiscount($originalAmount);
        }

        $rewardType = $plan->referee_reward_type ?? Referral::REWARD_NONE;
        $rewardValue = round(max((float) ($plan->referee_reward_value ?? 0), 0), 2);

        if (! in_array($rewardType, Referral::discountRewardTypes(), true)) {
            return $this->noDiscount($originalAmount);
        }

        $discountAmount = $this->calculateDiscount($rewardType, $rewardValue, $originalAmount);

        if ($discountAmount <= 0) {
            return $this->noDiscount($originalAmount);
        }

        return [
            'referral' => $referral,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => round(max($originalAmount - $discountAmount, 0), 2),
        ];
    }

    public function reserveDiscountForPayment(Payment $payment): void
    {
        if (! $payment->referral_id || (float) $payment->discount_amount <= 0) {
            return;
        }

        DB::transaction(function () use ($payment): void {
            $referral = Referral::query()->lockForUpdate()->find($payment->referral_id);

            if (! $referral) {
                return;
            }

            $referral->forceFill([
                'referee_reward_payment_id' => $payment->getKey(),
                'referee_reward_applied_at' => now(),
                'referee_discount_amount_applied' => $payment->discount_amount,
            ])->saveQuietly();
        });
    }

    public function releaseDiscountReservation(Payment $payment): void
    {
        if (! $payment->referral_id || ! in_array($payment->status, ['failed', 'refunded'], true)) {
            return;
        }

        DB::transaction(function () use ($payment): void {
            $referral = Referral::query()->lockForUpdate()->find($payment->referral_id);

            if (! $referral || $referral->referee_reward_payment_id !== $payment->getKey()) {
                return;
            }

            if ($referral->qualified_payment_id === $payment->getKey()) {
                return;
            }

            $referral->forceFill([
                'referee_reward_payment_id' => null,
                'referee_reward_applied_at' => null,
                'referee_discount_amount_applied' => 0,
            ])->saveQuietly();
        });
    }

    public function processCompletedPayment(Payment $payment): void
    {
        $payment->loadMissing('user', 'subscription.subscriptionPlan');

        if (! $payment->user) {
            return;
        }

        $referral = Referral::query()
            ->where('referred_user_id', $payment->user_id)
            ->where('status', Referral::STATUS_PENDING)
            ->first();

        if (! $referral) {
            return;
        }

        // Get rewards from the plan that was purchased
        $plan = $payment->subscription?->subscriptionPlan;

        if ($plan) {
            $referral->forceFill([
                'referrer_points_awarded' => max((int) ($plan->referrer_reward_points ?? 0), 0),
                'referee_reward_type' => $plan->referee_reward_type ?? Referral::REWARD_NONE,
                'referee_reward_value' => round(max((float) ($plan->referee_reward_value ?? 0), 0), 2),
            ])->saveQuietly();
        }

        $this->qualifyAndRewardReferral($referral, $payment);
    }

    public function addPoints(
        User $user,
        int $points,
        string $type,
        ?Referral $referral = null,
        ?int $createdByAdminId = null,
        ?string $note = null,
    ): ?PointTransaction {
        if ($points === 0) {
            return null;
        }

        return DB::transaction(function () use ($user, $points, $type, $referral, $createdByAdminId, $note): PointTransaction {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $currentBalance = (int) $lockedUser->points_balance;
            $nextBalance = $currentBalance + $points;

            if ($nextBalance < 0) {
                throw ValidationException::withMessages([
                    'points' => __('The user does not have enough points for this deduction.'),
                ]);
            }

            $transaction = PointTransaction::create([
                'user_id' => $lockedUser->getKey(),
                'referral_id' => $referral?->getKey(),
                'created_by_admin_id' => $createdByAdminId,
                'type' => $type,
                'points' => $points,
                'balance_after' => $nextBalance,
                'note' => $note,
            ]);

            $lockedUser->forceFill([
                'points_balance' => $nextBalance,
            ])->saveQuietly();

            return $transaction;
        });
    }

    public function awardRegistrationPoints(User $user): ?PointTransaction
    {
        $points = RegistrationRewardSetting::getPoints();

        if ($points <= 0) {
            return null;
        }

        return $this->addPoints(
            $user,
            $points,
            'signup_bonus',
            null,
            null,
            __('Registration reward'),
        );
    }

    public function sendRegistrationWelcomeNotification(User $user): void
    {
        $this->notifications->notifyUser(
            $user,
            __('Welcome to the app!'),
            __('Your account has been created successfully and you received :points points as a registration bonus.', [
                'points' => RegistrationRewardSetting::getPoints(),
            ]),
            'success',
            ['source' => 'registration'],
        );
    }

    private function qualifyAndRewardReferral(Referral $referral, ?Payment $payment = null): void
    {
        DB::transaction(function () use ($referral, $payment): void {
            $lockedReferral = Referral::query()
                ->with(['referrer', 'referredUser'])
                ->lockForUpdate()
                ->find($referral->getKey());

            if (! $lockedReferral || $lockedReferral->status !== Referral::STATUS_PENDING) {
                return;
            }

            $now = now();
            $referrerPoints = max((int) $lockedReferral->referrer_points_awarded, 0);
            $refereePoints = $lockedReferral->referee_reward_type === Referral::REWARD_POINTS
                ? max((int) round((float) $lockedReferral->referee_reward_value), 0)
                : 0;

            if ($referrerPoints > 0 && $lockedReferral->referrer) {
                $this->addPoints(
                    $lockedReferral->referrer,
                    $referrerPoints,
                    'referral_bonus',
                    $lockedReferral,
                    null,
                    __('Referral reward for inviting :user', [
                        'user' => $lockedReferral->referredUser?->name ?? '#' . $lockedReferral->referred_user_id,
                    ]),
                );
            }

            if ($refereePoints > 0 && $lockedReferral->referredUser) {
                $this->addPoints(
                    $lockedReferral->referredUser,
                    $refereePoints,
                    'referee_bonus',
                    $lockedReferral,
                    null,
                    __('Referral welcome reward'),
                );
            }

            $lockedReferral->forceFill([
                'status' => Referral::STATUS_REWARDED,
                'qualified_payment_id' => $payment?->getKey() ?? $lockedReferral->qualified_payment_id,
                'qualified_subscription_id' => $payment?->subscription_id ?? $lockedReferral->qualified_subscription_id,
                'qualified_at' => $now,
                'rewarded_at' => $now,
                'referee_points_awarded' => $refereePoints,
                'referee_reward_payment_id' => $payment && (float) $payment->discount_amount > 0
                    ? $payment->getKey()
                    : $lockedReferral->referee_reward_payment_id,
                'referee_discount_amount_applied' => $payment && (float) $payment->discount_amount > 0
                    ? $payment->discount_amount
                    : $lockedReferral->referee_discount_amount_applied,
                'referee_reward_applied_at' => $refereePoints > 0
                    ? $now
                    : $lockedReferral->referee_reward_applied_at,
            ])->saveQuietly();
        });

        // Send notification to referrer after transaction completes
        $referral->refresh()->loadMissing(['referrer', 'referredUser']);
        $referrerPoints = max((int) $referral->referrer_points_awarded, 0);

        if ($referrerPoints > 0 && $referral->referrer) {
            $this->notifications->notifyUser(
                $referral->referrer,
                __('You earned referral points!'),
                __('You earned :points points because :name subscribed using your referral code.', [
                    'points' => $referrerPoints,
                    'name' => $referral->referredUser?->name ?? __('a new user'),
                ]),
                'success',
                ['source' => 'referral'],
            );
        }
    }

    private function calculateDiscount(string $type, float $value, float $amount): float
    {
        return match ($type) {
            Referral::REWARD_FIXED_DISCOUNT => min($amount, $value),
            Referral::REWARD_PERCENT_DISCOUNT => round(
                min($amount, $amount * (min($value, 100) / 100)),
                2
            ),
            default => 0.0,
        };
    }

    private function noDiscount(float $originalAmount): array
    {
        return [
            'referral' => null,
            'original_amount' => $originalAmount,
            'discount_amount' => 0.0,
            'final_amount' => $originalAmount,
        ];
    }

    private function isEnabled(): bool
    {
        return filter_var(Setting::getValue('referral_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    private function normalizeReferralCode(?string $referralCode): ?string
    {
        if (blank($referralCode)) {
            return null;
        }

        return Str::upper(trim($referralCode));
    }
}
