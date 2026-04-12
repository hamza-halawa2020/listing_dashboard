<?php

namespace App\Services;

use App\Models\User;
use App\Support\AdminPermissionRegistry;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

class SystemNotificationService
{
    public function notifyAdmins(string $title, ?string $body = null, string $status = 'info', array $meta = []): void
    {
        $admins = $this->adminRecipients();

        if ($admins->isEmpty()) {
            return;
        }

        $this->notifyUsers($admins, $title, $body, $status, $meta);
    }

    public function notifyUser(?User $user, string $title, ?string $body = null, string $status = 'info', array $meta = []): void
    {
        if (! $user?->exists) {
            return;
        }

        $this->notifyUsers([$user], $title, $body, $status, $meta);
    }

    public function notifyUsers(iterable $users, string $title, ?string $body = null, string $status = 'info', array $meta = []): void
    {
        $payload = $this->buildPayload($title, $body, $status, $meta);

        collect($users)
            ->filter(fn ($user): bool => $user instanceof User && $user->exists)
            ->unique('id')
            ->each(function (User $user) use ($payload): void {
                $user->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => \Filament\Notifications\DatabaseNotification::class,
                    'data' => $payload,
                ]);
            });
    }

    /**
     * @return EloquentCollection<int, User>
     */
    protected function adminRecipients(): EloquentCollection
    {
        $roles = AdminPermissionRegistry::panelRoles();
        $permissions = AdminPermissionRegistry::allPermissions();

        return User::query()
            ->where(function (Builder $query) use ($roles, $permissions): void {
                $query
                    ->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereIn('name', $roles))
                    ->orWhereHas('permissions', fn (Builder $permissionQuery) => $permissionQuery->whereIn('name', $permissions));
            })
            ->get();
    }

    protected function buildPayload(string $title, ?string $body, string $status, array $meta): array
    {
        $icon = $meta['icon'] ?? null;
        unset($meta['icon']);

        $notification = FilamentNotification::make()
            ->title($title)
            ->body($body);

        match ($status) {
            'success' => $notification->success(),
            'warning' => $notification->warning(),
            'danger' => $notification->danger(),
            default => $notification->info(),
        };

        if (filled($icon)) {
            $notification->icon($icon);
        }

        return array_merge($notification->getDatabaseMessage(), $meta);
    }
}
