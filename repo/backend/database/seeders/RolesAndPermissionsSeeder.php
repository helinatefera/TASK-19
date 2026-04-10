<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [];
        foreach (UserRole::cases() as $userRole) {
            $roles[$userRole->value] = Role::updateOrCreate(
                ['name' => $userRole->value],
                ['description' => ucfirst($userRole->value) . ' role'],
            );
        }

        // Define permissions with their role assignments
        $permissionMatrix = [
            'campaigns.create'       => ['admin', 'creator'],
            'campaigns.update_own'   => ['admin', 'creator'],
            'campaigns.review'       => ['admin', 'moderator'],
            'campaigns.approve'      => ['admin', 'moderator'],
            'orders.create'          => ['admin', 'user'],
            'orders.cancel_own'      => ['admin', 'user'],
            'orders.cancel_any'      => ['admin', 'moderator', 'staff'],
            'orders.fulfill'         => ['admin', 'moderator', 'staff'],
            'orders.refund_approve'  => ['admin', 'moderator', 'staff'],
            'payments.record'        => ['admin', 'staff'],
            'vouchers.redeem'        => ['admin', 'moderator', 'staff'],
            'reviews.create'         => ['admin', 'creator', 'user'],
            'disputes.create'        => ['admin', 'creator', 'user'],
            'disputes.arbitrate'     => ['admin', 'moderator'],
            'risk.view'              => ['admin', 'moderator'],
            'risk.manage'            => ['admin', 'moderator'],
            'users.manage'           => ['admin'],
            'audit.view'             => ['admin'],
            'notifications.manage'   => ['admin'],
            'after_sales.create'     => ['admin', 'user'],
            'after_sales.resolve'    => ['admin', 'moderator', 'staff'],
            'programs.create'        => ['admin', 'moderator'],
            'programs.approve'       => ['admin', 'moderator'],
            'view_sensitive_data'    => ['admin'],
        ];

        foreach ($permissionMatrix as $permissionName => $assignedRoles) {
            $permission = Permission::updateOrCreate(
                ['name' => $permissionName],
                ['description' => str_replace(['.', '_'], ' ', ucfirst($permissionName))],
            );

            // Sync roles for this permission
            $roleIds = collect($assignedRoles)->map(fn (string $role) => $roles[$role]->id)->toArray();
            $permission->roles()->sync($roleIds);
        }
    }
}
