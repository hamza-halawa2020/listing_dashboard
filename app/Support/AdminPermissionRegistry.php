<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminPermissionRegistry
{
    /**
     * @return array<int, string>
     */
    public static function panelRoles(): array
    {
        return [
            'super_admin',
            'admin',
            'moderator',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function roleLabels(): array
    {
        return [
            'super_admin' => __('Super Admin'),
            'admin' => __('Admin'),
            'moderator' => __('Moderator'),
        ];
    }

    public static function roleLabel(string $role): string
    {
        return static::roleLabels()[$role]
            ?? Str::of($role)->replace('_', ' ')->title()->toString();
    }

    public static function permissionLabel(string $permission): string
    {
        $segments = explode('.', $permission, 2);

        if (count($segments) !== 2) {
            return Str::of($permission)
                ->replace(['.', '_'], ' ')
                ->title()
                ->toString();
        }

        [$subject, $action] = $segments;

        $subjectLabel = static::permissionSubjectLabel($subject);
        $actionLabel = static::permissionActionLabel($action);

        if ($subjectLabel !== null && $actionLabel !== null) {
            return $actionLabel . ' ' . $subjectLabel;
        }

        return Str::of($permission)
            ->replace(['.', '_'], ' ')
            ->title()
            ->toString();
    }

    /**
     * @return array<int, string>
     */
    public static function resourcePrefixes(): array
    {
        return [
            'roles',
            'permissions',
            'users',
            'reviews',
            'categories',
            'posts',
            'locations',
            'listings',
            'subscriptions',
            'family_members',
            'contacts',
            'payments',
            'subscription_plans',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function resourceActions(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function pagePermissions(): array
    {
        return [
            'dashboard.view',
            'settings.manage',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        return array_values(array_unique(array_merge(
            static::permissionsForResources(static::resourcePrefixes()),
            static::pagePermissions(),
        )));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rolePermissions(): array
    {
        return [
            'super_admin' => static::allPermissions(),
            'admin' => array_values(array_unique(array_merge(
                ['dashboard.view'],
                static::permissionsForResources(array_values(array_diff(static::resourcePrefixes(), ['users', 'roles', 'permissions']))),
            ))),
            'moderator' => array_values(array_unique(array_merge(
                ['dashboard.view'],
                static::permissionsForMap([
                    'listings' => ['view_any', 'view', 'create', 'update'],
                    'categories' => ['view_any', 'view'],
                    'locations' => ['view_any', 'view'],
                    'contacts' => ['view_any', 'view', 'delete', 'delete_any'],
                    'reviews' => ['view_any', 'view', 'update'],
                    'posts' => ['view_any', 'view', 'create', 'update'],
                ]),
            ))),
        ];
    }

    public static function resourcePermission(string $prefix, string $action): string
    {
        return $prefix . '.' . $action;
    }

    /**
     * @param  array<int, string>  $prefixes
     * @return array<int, string>
     */
    public static function permissionsForResources(array $prefixes): array
    {
        $permissions = [];

        foreach ($prefixes as $prefix) {
            foreach (static::resourceActions() as $action) {
                $permissions[] = static::resourcePermission($prefix, $action);
            }
        }

        return $permissions;
    }

    /**
     * @param  array<string, array<int, string>>  $map
     * @return array<int, string>
     */
    public static function permissionsForMap(array $map): array
    {
        $permissions = [];

        foreach ($map as $prefix => $actions) {
            foreach ($actions as $action) {
                $permissions[] = static::resourcePermission($prefix, $action);
            }
        }

        return $permissions;
    }

    private static function permissionSubjectLabel(string $subject): ?string
    {
        return match ($subject) {
            'roles' => __('Roles'),
            'permissions' => __('Permissions'),
            'users' => __('Users'),
            'reviews' => __('Reviews'),
            'categories' => __('Categories'),
            'posts' => __('Posts'),
            'locations' => __('Locations'),
            'listings' => __('Listings'),
            'subscriptions' => __('Subscriptions'),
            'family_members' => __('Family Members'),
            'contacts' => __('Contacts'),
            'payments' => __('Payments'),
            'subscription_plans' => __('Subscription Plans'),
            'dashboard' => __('Dashboard'),
            'settings' => __('Settings'),
            default => null,
        };
    }

    private static function permissionActionLabel(string $action): ?string
    {
        return match ($action) {
            'view_any' => __('View Any'),
            'view' => __('View'),
            'create' => __('Create'),
            'update' => __('Update'),
            'delete' => __('Delete'),
            'delete_any' => __('Delete Any'),
            'manage' => __('Manage'),
            default => null,
        };
    }
}
