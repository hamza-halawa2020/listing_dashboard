<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AdminPermissionRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AdminPermissionRegistry::allPermissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (AdminPermissionRegistry::rolePermissions() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        $this->assignSystemRolesToExistingUsers();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function assignSystemRolesToExistingUsers(): void
    {
        $superAdmin = User::query()
            ->where('email', 'admin@example.com')
            ->first()
            ?? User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->first();

        if ($superAdmin) {
            $superAdmin->syncRoles(['super_admin']);
        }

        User::query()
            ->where('role', 'admin')
            ->when(
                $superAdmin,
                fn ($query) => $query->whereKeyNot($superAdmin->getKey()),
            )
            ->get()
            ->each(function (User $user): void {
                if (! $user->hasAnyRole(AdminPermissionRegistry::panelRoles())) {
                    $user->assignRole('admin');
                }
            });
    }
}
