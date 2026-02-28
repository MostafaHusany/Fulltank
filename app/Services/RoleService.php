<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Models\Role;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Permission;

class RoleService
{
    protected array $protectedRoles = ['admin', 'super_admin', 'super-admin'];

    public function isProtectedRole(Role $role): bool
    {
        return in_array(strtolower($role->name), $this->protectedRoles);
    }

    public function createRole(array $data, ?array $permissionIds = null, ?array $userIds = null): Role
    {
        return DB::transaction(function () use ($data, $permissionIds, $userIds) {
            $role = Role::create([
                'name'         => $this->formatRoleName($data['name']),
                'display_name' => $data['name'],
                'description'  => $data['description'] ?? null,
            ]);

            if ($permissionIds) {
                $role->syncPermissions($permissionIds);
            }

            if ($userIds) {
                $this->syncUsers($role, $userIds);
            }

            return $role;
        });
    }

    public function updateRole(Role $role, array $data, ?array $permissionIds = null, ?array $userIds = null): Role
    {
        if ($this->isProtectedRole($role)) {
            throw new Exception(__('roles.protected_role_error'));
        }

        return DB::transaction(function () use ($role, $data, $permissionIds, $userIds) {
            $role->update([
                'name'         => $this->formatRoleName($data['name']),
                'display_name' => $data['name'],
                'description'  => $data['description'] ?? null,
            ]);

            if ($permissionIds !== null) {
                $role->syncPermissions($permissionIds);
            }

            if ($userIds !== null) {
                $this->syncUsers($role, $userIds);
            }

            return $role->fresh(['permissions', 'users']);
        });
    }

    public function deleteRole(Role $role): bool
    {
        if ($this->isProtectedRole($role)) {
            throw new Exception(__('roles.protected_role_error'));
        }

        return DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            RoleUser::where('role_id', $role->id)->delete();
            return $role->delete();
        });
    }

    public function updateRolePermissions(int $roleId, array $permissionIds): Role
    {
        $role = Role::findOrFail($roleId);

        if ($this->isProtectedRole($role)) {
            throw new Exception(__('roles.protected_role_error'));
        }

        $role->syncPermissions($permissionIds);

        return $role->fresh(['permissions']);
    }

    public function syncUsers(Role $role, array $userIds): void
    {
        RoleUser::whereIn('user_id', $userIds)->delete();

        $records = array_map(function ($userId) use ($role) {
            return [
                'role_id'   => $role->id,
                'user_id'   => $userId,
                'user_type' => User::class,
            ];
        }, $userIds);

        RoleUser::insert($records);
    }

    public function getPermissionsGrouped(): array
    {
        $permissions = Permission::orderBy('name')->get();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('_', $permission->name);
            $module = $parts[0] ?? 'other';

            $moduleLabel = $this->getModuleLabel($module);

            if (!isset($grouped[$moduleLabel])) {
                $grouped[$moduleLabel] = [];
            }

            $grouped[$moduleLabel][] = [
                'id'           => $permission->id,
                'name'         => $permission->name,
                'display_name' => $permission->display_name,
            ];
        }

        ksort($grouped);

        return $grouped;
    }

    protected function getModuleLabel(string $module): string
    {
        $labels = [
            'users'            => __('roles.module_users'),
            'roles'            => __('roles.module_roles'),
            'clients'          => __('roles.module_clients'),
            'vehicles'         => __('roles.module_vehicles'),
            'drivers'          => __('roles.module_drivers'),
            'stations'         => __('roles.module_stations'),
            'stationWorkers'   => __('roles.module_station_workers'),
            'stationWallets'   => __('roles.module_station_wallets'),
            'wallets'          => __('roles.module_wallets'),
            'depositRequests'  => __('roles.module_deposit_requests'),
            'fuelTransactions' => __('roles.module_fuel_transactions'),
            'settlements'      => __('roles.module_settlements'),
            'fuelTypes'        => __('roles.module_fuel_types'),
            'governorates'     => __('roles.module_governorates'),
            'districts'        => __('roles.module_districts'),
            'vehicleQuotas'    => __('roles.module_vehicle_quotas'),
            'reports'          => __('roles.module_reports'),
            'activityLogs'     => __('roles.module_activity_logs'),
            'dashboard'        => __('roles.module_dashboard'),
            'transactions'     => __('roles.module_transactions'),
        ];

        return $labels[$module] ?? ucfirst($module);
    }

    protected function formatRoleName(string $name): string
    {
        return strtolower(str_replace(' ', '_', trim($name)));
    }
}
