<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class AuthorizedResource extends Resource
{
    public static function getPermissionPrefix(): string
    {
        $baseName = Str::before(class_basename(static::class), 'Resource');

        return Str::snake(Str::pluralStudly($baseName));
    }

    public static function permissionName(string $action): string
    {
        return static::getPermissionPrefix() . '.' . $action;
    }

    public static function canViewAny(): bool
    {
        return static::currentUserCan('view_any');
    }

    public static function canView(Model $record): bool
    {
        return static::currentUserCan('view');
    }

    public static function canCreate(): bool
    {
        return static::currentUserCan('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::currentUserCan('update');
    }

    public static function canDelete(Model $record): bool
    {
        return static::currentUserCan('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::currentUserCan('delete_any');
    }

    protected static function currentUserCan(string $action): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(static::permissionName($action));
    }
}
