<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Auth\Access\Response;
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

    public static function getViewAnyAuthorizationResponse(): Response
    {
        return static::currentUserCanResponse('view_any');
    }

    public static function getViewAuthorizationResponse(Model $record): Response
    {
        return static::currentUserCanResponse('view');
    }

    public static function getCreateAuthorizationResponse(): Response
    {
        return static::currentUserCanResponse('create');
    }

    public static function getEditAuthorizationResponse(Model $record): Response
    {
        return static::currentUserCanResponse('update');
    }

    public static function getDeleteAuthorizationResponse(Model $record): Response
    {
        return static::currentUserCanResponse('delete');
    }

    public static function getDeleteAnyAuthorizationResponse(): Response
    {
        return static::currentUserCanResponse('delete_any');
    }

    public static function canViewAny(): bool
    {
        return static::getViewAnyAuthorizationResponse()->allowed();
    }

    public static function canView(Model $record): bool
    {
        return static::getViewAuthorizationResponse($record)->allowed();
    }

    public static function canCreate(): bool
    {
        return static::getCreateAuthorizationResponse()->allowed();
    }

    public static function canEdit(Model $record): bool
    {
        return static::getEditAuthorizationResponse($record)->allowed();
    }

    public static function canDelete(Model $record): bool
    {
        return static::getDeleteAuthorizationResponse($record)->allowed();
    }

    public static function canDeleteAny(): bool
    {
        return static::getDeleteAnyAuthorizationResponse()->allowed();
    }

    protected static function currentUserCan(string $action): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(static::permissionName($action));
    }

    protected static function currentUserCanResponse(string $action): Response
    {
        return static::currentUserCan($action)
            ? Response::allow()
            : Response::deny();
    }
}
