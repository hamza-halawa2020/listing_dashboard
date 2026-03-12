<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SubscriptionSpreadsheetImporter
{
    public function __construct(
        private readonly SpreadsheetRowReader $rowReader,
    ) {}

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     errors: array<int, string>
     * }
     */
    public function import(string $path): array
    {
        $rows = $this->rowReader->readRows($path, $this->headingAliases());

        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $row) {
            try {
                $result = $this->importRow($row);

                if ($result === 'created') {
                    $summary['created']++;

                    continue;
                }

                if ($result === 'updated') {
                    $summary['updated']++;

                    continue;
                }

                $summary['skipped']++;
            } catch (Throwable $exception) {
                $summary['skipped']++;
                $summary['errors'][] = 'Row ' . ($index + 2) . ': ' . $exception->getMessage();
            }
        }

        return $summary;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function importRow(array $row): string
    {
        $row = $this->normalizeRow($row);

        if (! $this->rowHasValues($row)) {
            return 'skipped';
        }

        if ($this->isTemplateExampleRow($row)) {
            return 'skipped';
        }

        $user = $this->resolveUser($row);
        $plan = $this->resolvePlan($row);
        $startsAt = $this->parseDate($row['starts_at'] ?? '', 'starts_at');
        $endsAt = $this->parseDate($row['ends_at'] ?? '', 'ends_at');
        $subscription = $this->resolveSubscription($row, $user, $plan, $startsAt);
        $exists = $subscription->exists;

        $attributes = [
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => $startsAt->toDateString(),
            'ends_at' => $endsAt->toDateString(),
            'status' => 'active',
        ];

        if ($this->hasFilledValue($row, 'membership_card_number')) {
            $membershipCardNumber = trim($row['membership_card_number']);
            $this->ensureUniqueMembershipNumber($membershipCardNumber, $subscription);

            $attributes['membership_card_number'] = $membershipCardNumber;
            $attributes['is_card_issued'] = true;
        }

        $subscription->fill($attributes);

        if ($exists && ! $subscription->isDirty()) {
            return 'skipped';
        }

        $subscription->save();

        return $exists ? 'updated' : 'created';
    }

    /**
     * @param  array<string, string>  $row
     */
    private function resolveUser(array $row): User
    {
        if (! $this->hasFilledValue($row, 'user_phone')) {
            throw new RuntimeException('The [user_phone] field is required.');
        }

        $phone = trim($row['user_phone']);

        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if (! $user) {
            throw new RuntimeException("No user was found for phone [{$phone}].");
        }

        return $user;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function resolvePlan(array $row): SubscriptionPlan
    {
        if (! $this->hasFilledValue($row, 'subscription_plan_code')) {
            throw new RuntimeException('The [subscription_plan_code] field is required.');
        }

        $planCode = trim($row['subscription_plan_code']);

        $plan = $this->applyExactCodeSearch(SubscriptionPlan::query(), $planCode)->first();

        if (! $plan) {
            throw new RuntimeException("No subscription plan was found for code [{$planCode}].");
        }

        return $plan;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function resolveSubscription(array $row, User $user, SubscriptionPlan $plan, Carbon $startsAt): Subscription
    {
        if ($this->hasFilledValue($row, 'membership_card_number')) {
            $subscription = Subscription::query()
                ->where('membership_card_number', trim($row['membership_card_number']))
                ->first();

            if ($subscription) {
                return $subscription;
            }
        }

        $subscription = Subscription::query()
            ->where('user_id', $user->id)
            ->where('subscription_plan_id', $plan->id)
            ->whereDate('starts_at', $startsAt->toDateString())
            ->first();

        return $subscription ?? new Subscription();
    }

    private function parseDate(string $value, string $field): Carbon
    {
        if (blank($value)) {
            throw new RuntimeException("The [{$field}] field is required.");
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            throw new RuntimeException("The [{$field}] value must be a valid date.");
        }
    }

    private function ensureUniqueMembershipNumber(string $membershipCardNumber, Subscription $subscription): void
    {
        $query = Subscription::query()
            ->where('membership_card_number', $membershipCardNumber);

        if ($subscription->exists) {
            $query->whereKeyNot($subscription->getKey());
        }

        if ($query->exists()) {
            throw new RuntimeException('The provided membership_card_number is already used by another subscription.');
        }
    }

    private function applyExactCodeSearch(Builder $query, string $value): Builder
    {
        return $query->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($value))]);
    }

    /**
     * @return array<string, string>
     */
    private function headingAliases(): array
    {
        return [
            'user_phone' => 'user_phone',
            'user phone' => 'user_phone',
            'phone' => 'user_phone',
            'رقم الهاتف' => 'user_phone',
            'رقم التليفون' => 'user_phone',
            'تليفون المستخدم' => 'user_phone',
            'رقم تليفون المستخدم' => 'user_phone',
            'subscription_plan_code' => 'subscription_plan_code',
            'subscription plan code' => 'subscription_plan_code',
            'plan_code' => 'subscription_plan_code',
            'plan code' => 'subscription_plan_code',
            'code' => 'subscription_plan_code',
            'كود الخطة' => 'subscription_plan_code',
            'كود خطة الاشتراك' => 'subscription_plan_code',
            'membership_card_number' => 'membership_card_number',
            'membership card number' => 'membership_card_number',
            'membership number' => 'membership_card_number',
            'رقم العضوية' => 'membership_card_number',
            'رقم الكارت' => 'membership_card_number',
            'starts_at' => 'starts_at',
            'starts at' => 'starts_at',
            'start_date' => 'starts_at',
            'start date' => 'starts_at',
            'تاريخ البداية' => 'starts_at',
            'ends_at' => 'ends_at',
            'ends at' => 'ends_at',
            'end_date' => 'ends_at',
            'end date' => 'ends_at',
            'تاريخ النهاية' => 'ends_at',
        ];
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, string>
     */
    private function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalizedKey = trim(Str::snake((string) $key));

            if ($normalizedKey === '') {
                continue;
            }

            $normalized[$normalizedKey] = trim($value);
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function rowHasValues(array $row): bool
    {
        foreach ($row as $value) {
            if (filled($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function hasFilledValue(array $row, string $key): bool
    {
        return array_key_exists($key, $row) && filled($row[$key]);
    }

    /**
     * @param  array<string, string>  $row
     */
    private function isTemplateExampleRow(array $row): bool
    {
        return ($row['user_phone'] ?? null) === '01XXXXXXXXX'
            && ($row['subscription_plan_code'] ?? null) === 'IG'
            && ($row['membership_card_number'] ?? null) === 'IG00011234'
            && ($row['starts_at'] ?? null) === '2026-03-12'
            && ($row['ends_at'] ?? null) === '2027-03-11';
    }
}
