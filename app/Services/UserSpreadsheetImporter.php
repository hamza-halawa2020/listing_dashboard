<?php

namespace App\Services;

use App\Models\Location;
use App\Models\User;
use App\Support\AdminPermissionRegistry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role as PermissionRole;
use Throwable;

class UserSpreadsheetImporter
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

        [$user, $exists] = $this->resolveUser($row);

        $attributes = $this->extractAttributes($row, $user);
        $systemRoles = $this->extractSystemRoles($row);

        if (! $exists && $attributes === [] && $systemRoles === null) {
            return 'skipped';
        }

        if (! $exists) {
            foreach (['name', 'email', 'phone'] as $requiredField) {
                if (! array_key_exists($requiredField, $attributes) || blank($attributes[$requiredField])) {
                    throw new RuntimeException("Missing required field [{$requiredField}] for a new user.");
                }
            }
        }

        if ($exists && $attributes === [] && $systemRoles === null) {
            return 'skipped';
        }

        $user->fill($attributes);

        if ($exists && ! $user->isDirty() && $systemRoles === null) {
            return 'skipped';
        }

        $user->save();

        if ($systemRoles !== null) {
            $user->syncRoles($systemRoles);
        } else {
            $this->syncImplicitAdminRole($user);
        }

        return $exists ? 'updated' : 'created';
    }

    /**
     * @param  array<string, string>  $row
     * @return array{0: User, 1: bool}
     */
    private function resolveUser(array $row): array
    {
        if ($this->hasFilledValue($row, 'id') && ctype_digit($row['id'])) {
            $user = User::find((int) $row['id']);

            if ($user) {
                return [$user, true];
            }
        }

        if ($this->hasFilledValue($row, 'email')) {
            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($row['email']))])
                ->first();

            if ($user) {
                return [$user, true];
            }
        }

        if ($this->hasFilledValue($row, 'phone')) {
            $user = User::query()
                ->where('phone', trim($row['phone']))
                ->first();

            if ($user) {
                return [$user, true];
            }
        }

        return [new User(), false];
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, mixed>
     */
    private function extractAttributes(array $row, User $user): array
    {
        $attributes = [];

        foreach (['name', 'email', 'phone', 'national_id', 'address'] as $field) {
            if ($this->hasFilledValue($row, $field)) {
                $attributes[$field] = trim($row[$field]);
            }
        }

        if ($this->hasFilledValue($row, 'email')) {
            if (! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('The provided email is not valid.');
            }

            $this->ensureUniqueValue('email', trim($row['email']), $user);
        }

        if ($this->hasFilledValue($row, 'phone')) {
            $this->ensureUniqueValue('phone', trim($row['phone']), $user);
        }

        if ($this->hasFilledValue($row, 'national_id')) {
            $this->ensureUniqueValue('national_id', trim($row['national_id']), $user);
        }

        if ($this->hasFilledValue($row, 'role')) {
            $attributes['role'] = $this->normalizeAppRole($row['role']);
        } elseif (! $user->exists) {
            $attributes['role'] = 'member';
        }

        if ($this->hasFilledValue($row, 'gender')) {
            $attributes['gender'] = $this->normalizeGender($row['gender']);
        }

        if ($this->hasFilledValue($row, 'birth_date')) {
            $attributes['birth_date'] = $this->parseDate($row['birth_date'], 'birth_date')->toDateString();
        }

        if ($this->hasFilledValue($row, 'email_verified_at')) {
            $attributes['email_verified_at'] = $this->parseEmailVerification($row['email_verified_at']);
        }

        if ($this->hasFilledValue($row, 'password')) {
            $attributes['password'] = $row['password'];
        } elseif (! $user->exists && $this->hasFilledValue($row, 'phone')) {
            $attributes['password'] = trim($row['phone']);
        }

        $locationId = $this->resolveLocationId($row);

        if ($locationId !== null) {
            $attributes['location_id'] = $locationId;
        }

        return $attributes;
    }

    /**
     * @param  array<string, string>  $row
     * @return array<int, string>|null
     */
    private function extractSystemRoles(array $row): ?array
    {
        if (! $this->hasFilledValue($row, 'roles')) {
            return null;
        }

        $values = preg_split('/[\s,;|،]+/u', trim($row['roles'])) ?: [];
        $roles = collect($values)
            ->filter(fn (?string $value): bool => filled($value))
            ->map(fn (string $value): string => $this->normalizeSystemRole($value))
            ->unique()
            ->values()
            ->all();

        if ($roles === []) {
            return [];
        }

        $existingRoles = PermissionRole::query()
            ->whereIn('name', $roles)
            ->pluck('name')
            ->all();

        $missingRoles = array_values(array_diff($roles, $existingRoles));

        if ($missingRoles !== []) {
            throw new RuntimeException('Unknown system roles: ' . implode(', ', $missingRoles));
        }

        return $roles;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function resolveLocationId(array $row): ?int
    {
        if ($this->hasFilledValue($row, 'governorate_name') || $this->hasFilledValue($row, 'area_name')) {
            if (! $this->hasFilledValue($row, 'governorate_name') || ! $this->hasFilledValue($row, 'area_name')) {
                throw new RuntimeException('Both governorate_name and area_name are required to resolve the location.');
            }

            $location = $this->findLocationByGovernorateAndArea(
                $row['governorate_name'],
                $row['area_name'],
            );

            if (! $location) {
                throw new RuntimeException('The related location could not be resolved from the provided governorate and area.');
            }

            return $location->id;
        }

        if ($this->hasFilledValue($row, 'location_id')) {
            if (! ctype_digit($row['location_id'])) {
                throw new RuntimeException('The provided location_id must be a valid integer.');
            }

            $location = Location::find((int) $row['location_id']);

            if ($location) {
                return $location->id;
            }

            throw new RuntimeException('The provided location_id was not found.');
        }

        if ($this->hasFilledValue($row, 'location_name')) {
            $location = $this->applyExactNameSearch(Location::query(), $row['location_name'])->first();

            if ($location) {
                return $location->id;
            }

            throw new RuntimeException('The related location could not be resolved from the provided name.');
        }

        if ($this->hasFilledValue($row, 'location_path')) {
            $location = $this->findLocationByPath($row['location_path']);

            if ($location) {
                return $location->id;
            }

            throw new RuntimeException('The related location could not be resolved from the provided path.');
        }

        return null;
    }

    private function findLocationByGovernorateAndArea(string $governorateName, string $areaName): ?Location
    {
        $governorate = $this->applyExactNameSearch(
            Location::query()->whereNull('parent_id'),
            $governorateName,
        )->first();

        if (! $governorate) {
            return null;
        }

        return $this->applyExactNameSearch(
            Location::query()->where('parent_id', $governorate->id),
            $areaName,
        )->first();
    }

    private function findLocationByPath(string $path): ?Location
    {
        $segments = array_values(array_filter(
            preg_split('/\s*(?:>|\/|\\\\)\s*/u', trim($path)) ?: [],
            static fn (?string $segment): bool => filled($segment),
        ));

        if ($segments === []) {
            return null;
        }

        $record = null;

        foreach ($segments as $index => $segment) {
            $query = $this->applyExactNameSearch(Location::query(), $segment);

            if ($index === 0) {
                $query->whereNull('parent_id');
            } elseif ($record) {
                $query->where('parent_id', $record->id);
            }

            $record = $query->first();

            if (! $record) {
                return null;
            }
        }

        return $record;
    }

    private function applyExactNameSearch(Builder $query, string $value): Builder
    {
        return $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($value))]);
    }

    private function syncImplicitAdminRole(User $user): void
    {
        if ($user->role === 'admin') {
            if (! $user->hasAnyRole(AdminPermissionRegistry::panelRoles()) && PermissionRole::query()->where('name', 'admin')->exists()) {
                $user->assignRole('admin');
            }

            return;
        }

        foreach (AdminPermissionRegistry::panelRoles() as $panelRole) {
            if ($user->hasRole($panelRole)) {
                $user->removeRole($panelRole);
            }
        }
    }

    private function normalizeAppRole(string $value): string
    {
        $normalized = Str::of($value)->trim()->lower()->replace([' ', '-'], '_')->toString();

        return match ($normalized) {
            'admin', 'ادمن', 'مسؤول' => 'admin',
            'member', 'user', 'عضو', 'مستخدم' => 'member',
            'service_provider', 'provider', 'serviceprovider', 'مزود_خدمة', 'مزود_الخدمة', 'مزود' => 'service_provider',
            default => throw new RuntimeException('The provided role is invalid. Expected admin, member, or service_provider.'),
        };
    }

    private function normalizeSystemRole(string $value): string
    {
        $normalized = Str::of($value)->trim()->lower()->replace([' ', '-'], '_')->toString();

        return match ($normalized) {
            'super_admin', 'superadmin', 'سوبر_ادمن', 'مدير_عام' => 'super_admin',
            'admin', 'ادمن', 'مسؤول' => 'admin',
            'moderator', 'مشرف' => 'moderator',
            default => $normalized,
        };
    }

    private function normalizeGender(string $value): string
    {
        $normalized = Str::of($value)->trim()->lower()->replace([' ', '-'], '_')->toString();

        return match ($normalized) {
            'male', 'm', 'ذكر' => 'male',
            'female', 'f', 'انثى', 'أنثى' => 'female',
            default => throw new RuntimeException('The provided gender is invalid. Expected male or female.'),
        };
    }

    private function parseDate(string $value, string $field): Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            throw new RuntimeException("The [{$field}] value must be a valid date.");
        }
    }

    private function parseEmailVerification(string $value): ?string
    {
        $normalized = Str::of($value)->trim()->lower()->toString();

        if (in_array($normalized, ['1', 'true', 'yes', 'y', 'نعم'], true)) {
            return now()->toDateTimeString();
        }

        if (in_array($normalized, ['0', 'false', 'no', 'n', 'لا'], true)) {
            return null;
        }

        return $this->parseDate($value, 'email_verified_at')->toDateTimeString();
    }

    private function ensureUniqueValue(string $field, string $value, User $user): void
    {
        if ($value === '') {
            return;
        }

        $query = User::query()->where($field, $value);

        if ($user->exists) {
            $query->whereKeyNot($user->getKey());
        }

        if ($query->exists()) {
            throw new RuntimeException("The provided {$field} is already used by another user.");
        }
    }

    /**
     * @return array<string, string>
     */
    private function headingAliases(): array
    {
        return [
            'id' => 'id',
            'user id' => 'id',
            'user_id' => 'id',
            'رقم' => 'id',
            'رقم المستخدم' => 'id',
            'name' => 'name',
            'full name' => 'name',
            'الاسم' => 'name',
            'اسم المستخدم' => 'name',
            'email' => 'email',
            'email address' => 'email',
            'البريد الإلكتروني' => 'email',
            'البريد الالكتروني' => 'email',
            'phone' => 'phone',
            'mobile' => 'phone',
            'الهاتف' => 'phone',
            'رقم الهاتف' => 'phone',
            'role' => 'role',
            'الدور' => 'role',
            'نوع المستخدم' => 'role',
            'national id' => 'national_id',
            'national_id' => 'national_id',
            'الرقم القومي' => 'national_id',
            'location id' => 'location_id',
            'location_id' => 'location_id',
            'معرف الموقع' => 'location_id',
            'location name' => 'location_name',
            'location_name' => 'location_name',
            'اسم الموقع' => 'location_name',
            'location path' => 'location_path',
            'location_path' => 'location_path',
            'مسار الموقع' => 'location_path',
            'governorate name' => 'governorate_name',
            'governorate_name' => 'governorate_name',
            'المحافظة' => 'governorate_name',
            'اسم المحافظة' => 'governorate_name',
            'area name' => 'area_name',
            'area_name' => 'area_name',
            'المنطقة' => 'area_name',
            'اسم المنطقة' => 'area_name',
            'address' => 'address',
            'العنوان' => 'address',
            'birth date' => 'birth_date',
            'birth_date' => 'birth_date',
            'تاريخ الميلاد' => 'birth_date',
            'gender' => 'gender',
            'الجنس' => 'gender',
            'password' => 'password',
            'كلمة المرور' => 'password',
            'email verified at' => 'email_verified_at',
            'email_verified_at' => 'email_verified_at',
            'تاريخ تأكيد البريد' => 'email_verified_at',
            'تاريخ التحقق من البريد' => 'email_verified_at',
            'roles' => 'roles',
            'الصلاحيات' => 'roles',
            'الأدوار' => 'roles',
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
}
