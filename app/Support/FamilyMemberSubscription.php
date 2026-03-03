<?php

namespace App\Support;

use App\Models\FamilyMember;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class FamilyMemberSubscription
{
    public static function ensureValidForUser(User $user, int | string | null $subscriptionId, ?FamilyMember $ignore = null): ?Subscription
    {
        if (blank($subscriptionId)) {
            return null;
        }

        $subscription = Subscription::query()
            ->with('subscriptionPlan')
            ->whereKey($subscriptionId)
            ->where('user_id', $user->id)
            ->first();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription_id' => __('This subscription is invalid for the selected user.'),
            ]);
        }

        if ($subscription->maxFamilyMembersLimit() < 1) {
            throw ValidationException::withMessages([
                'subscription_id' => __('This subscription does not allow family members.'),
            ]);
        }

        if ($subscription->availableFamilyMemberSlots($ignore) < 1) {
            throw ValidationException::withMessages([
                'subscription_id' => __('This subscription has reached the maximum number of family members.'),
            ]);
        }

        return $subscription;
    }

    /**
     * @return array<int, string>
     */
    public static function optionsForUser(?User $user, ?FamilyMember $ignore = null): array
    {
        if (! $user?->exists) {
            return [];
        }

        return $user->subscriptions()
            ->with('subscriptionPlan')
            ->get()
            ->filter(function (Subscription $subscription) use ($ignore): bool {
                if ($subscription->maxFamilyMembersLimit() < 1) {
                    return false;
                }

                if ($ignore?->subscription_id === $subscription->id) {
                    return true;
                }

                return $subscription->availableFamilyMemberSlots($ignore) > 0;
            })
            ->mapWithKeys(fn (Subscription $subscription): array => [
                $subscription->id => self::formatOptionLabel($subscription, $ignore),
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function validateStateForUser(User $user, array $items, string $statePathPrefix = 'familyMembers'): void
    {
        $errors = [];
        $counts = [];
        $limits = [];

        foreach ($items as $index => $item) {
            $subscriptionId = data_get($item, 'subscription_id');
            $recordId = data_get($item, 'id');
            $ignore = blank($recordId) ? null : FamilyMember::find($recordId);

            if (blank($subscriptionId)) {
                continue;
            }

            try {
                $subscription = self::resolveSubscriptionForState($user, $subscriptionId);
            } catch (ValidationException $exception) {
                $errors["data.{$statePathPrefix}.{$index}.subscription_id"] = Arr::first(Arr::flatten($exception->errors()));

                continue;
            }

            $limits[$subscription->id] = $subscription->maxFamilyMembersLimit();
            $counts[$subscription->id] = ($counts[$subscription->id] ?? 0) + 1;

            if ($ignore?->subscription_id === $subscription->id) {
                $counts[$subscription->id]--;
            }
        }

        foreach ($counts as $subscriptionId => $count) {
            if ($count <= ($limits[$subscriptionId] ?? 0)) {
                continue;
            }

            foreach ($items as $index => $item) {
                if ((int) data_get($item, 'subscription_id') !== (int) $subscriptionId) {
                    continue;
                }

                $errors["data.{$statePathPrefix}.{$index}.subscription_id"] = __('This subscription has reached the maximum number of family members.');
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private static function resolveSubscriptionForState(User $user, int | string | null $subscriptionId): Subscription
    {
        $subscription = Subscription::query()
            ->with('subscriptionPlan')
            ->whereKey($subscriptionId)
            ->where('user_id', $user->id)
            ->first();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'subscription_id' => __('This subscription is invalid for the selected user.'),
            ]);
        }

        if ($subscription->maxFamilyMembersLimit() < 1) {
            throw ValidationException::withMessages([
                'subscription_id' => __('This subscription does not allow family members.'),
            ]);
        }

        return $subscription;
    }

    private static function formatOptionLabel(Subscription $subscription, ?FamilyMember $ignore = null): string
    {
        $cardNumber = $subscription->membership_card_number ?: '#' . $subscription->id;
        $planName = $subscription->subscriptionPlan?->name ? __($subscription->subscriptionPlan->name) : __('Subscription');
        $used = $subscription->maxFamilyMembersLimit() - $subscription->availableFamilyMemberSlots($ignore);
        $limit = $subscription->maxFamilyMembersLimit();

        return "{$cardNumber} - {$planName} ({$used}/{$limit})";
    }
}
