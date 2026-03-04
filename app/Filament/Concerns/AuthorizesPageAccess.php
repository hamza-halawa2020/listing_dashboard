<?php

namespace App\Filament\Concerns;

trait AuthorizesPageAccess
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $permissionName = static::getAccessPermissionName();

        if (blank($permissionName)) {
            return true;
        }

        return $user->can($permissionName);
    }

    protected static function getAccessPermissionName(): ?string
    {
        return null;
    }
}
