<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class UserRolleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $roles = [
            [
                'name'         => 'admin',
                'display_name' => 'مدير النظام',
                'description'  => 'صلاحيات كاملة على النظام',
            ],
            [
                'name'         => 'technical',
                'display_name' => 'الدعم الفني',
                'description'  => 'صلاحيات محدودة حسب الأدوار',
            ],
            [
                'name'         => 'supervisor',
                'display_name' => 'مشرف',
                'description'  => 'مشرف على العمليات',
            ],
            [
                'name'         => 'accountant',
                'display_name' => 'محاسب',
                'description'  => 'صلاحيات مالية ومحاسبية',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }

        // Define permissions modules
        $modules = [
            'users'              => 'المستخدمين',
            'roles'              => 'الأدوار والصلاحيات',
            'clients'            => 'العملاء',
            'drivers'            => 'السائقين',
            'vehicles'           => 'المركبات',
            'stations'           => 'المحطات',
            'station_workers'    => 'عمال المحطات',
            'fuel_types'         => 'أنواع الوقود',
            'transactions'       => 'المعاملات',
            'wallets'            => 'المحافظ',
            'settlements'        => 'التسويات',
            'reports'            => 'التقارير',
            'activity_logs'      => 'سجل النشاطات',
            'settings'           => 'الإعدادات',
            'governorates'       => 'المحافظات',
            'districts'          => 'المراكز/الأحياء',
        ];

        $actions = [
            'show'   => 'عرض',
            'add'    => 'إضافة',
            'edit'   => 'تعديل',
            'delete' => 'حذف',
        ];

        $permissions = [];

        foreach ($modules as $module => $moduleAr) {
            foreach ($actions as $action => $actionAr) {
                $permissions[] = [
                    'name'         => "{$module}_{$action}",
                    'display_name' => "{$actionAr} {$moduleAr}",
                    'description'  => "{$actionAr} {$moduleAr}",
                ];
            }
        }

        // Additional special permissions
        $specialPermissions = [
            ['name' => 'dashboard_view', 'display_name' => 'عرض لوحة التحكم', 'description' => 'الوصول للوحة التحكم الرئيسية'],
            ['name' => 'wallets_deposit', 'display_name' => 'إيداع في المحافظ', 'description' => 'إضافة رصيد للمحافظ'],
            ['name' => 'wallets_withdraw', 'display_name' => 'سحب من المحافظ', 'description' => 'سحب رصيد من المحافظ'],
            ['name' => 'settlements_approve', 'display_name' => 'اعتماد التسويات', 'description' => 'الموافقة على التسويات المالية'],
            ['name' => 'reports_export', 'display_name' => 'تصدير التقارير', 'description' => 'تصدير التقارير لملفات خارجية'],
            ['name' => 'api_tester_access', 'display_name' => 'أدوات المطور', 'description' => 'الوصول لأدوات اختبار API'],
        ];

        $permissions = array_merge($permissions, $specialPermissions);

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(['name' => $permData['name']], $permData);
        }

        // Assign all permissions to technical role for demo
        $technicalRole = Role::where('name', 'technical')->first();
        if ($technicalRole) {
            $allPermissions = Permission::pluck('id')->toArray();
            $technicalRole->syncPermissions($allPermissions);
        }
    }
}
