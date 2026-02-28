<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'users' => [
                'label_ar' => 'المستخدمين',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'roles' => [
                'label_ar' => 'الأدوار',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'clients' => [
                'label_ar' => 'العملاء',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'vehicles' => [
                'label_ar' => 'المركبات',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'drivers' => [
                'label_ar' => 'السائقين',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'stations' => [
                'label_ar' => 'المحطات',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'stationWorkers' => [
                'label_ar' => 'عمال المحطات',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'stationWallets' => [
                'label_ar' => 'محافظ المحطات',
                'actions'  => ['show', 'edit'],
            ],
            'wallets' => [
                'label_ar' => 'المحافظ',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'depositRequests' => [
                'label_ar' => 'طلبات الإيداع',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'fuelTransactions' => [
                'label_ar' => 'معاملات الوقود',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'settlements' => [
                'label_ar' => 'التسويات',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'fuelTypes' => [
                'label_ar' => 'أنواع الوقود',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'governorates' => [
                'label_ar' => 'المحافظات',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'districts' => [
                'label_ar' => 'المناطق',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'vehicleQuotas' => [
                'label_ar' => 'حصص المركبات',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'reports' => [
                'label_ar' => 'التقارير',
                'actions'  => ['show'],
            ],
            'activityLogs' => [
                'label_ar' => 'سجلات النشاط',
                'actions'  => ['show'],
            ],
            'dashboard' => [
                'label_ar' => 'لوحة المعلومات',
                'actions'  => ['show'],
            ],
            'transactions' => [
                'label_ar' => 'المعاملات',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
            'financialSettings' => [
                'label_ar' => 'الإعدادات المالية',
                'actions'  => ['show', 'edit'],
            ],
            'paymentMethods' => [
                'label_ar' => 'طرق الدفع',
                'actions'  => ['show', 'add', 'edit', 'delete'],
            ],
        ];

        $actions = [
            'show'   => 'عرض',
            'add'    => 'إضافة',
            'edit'   => 'تعديل',
            'delete' => 'حذف',
        ];

        foreach ($modules as $module => $config) {
            foreach ($config['actions'] as $action) {
                $displayName = "{$config['label_ar']} - {$actions[$action]}";

                Permission::updateOrCreate(
                    ['name' => "{$module}_{$action}"],
                    [
                        'display_name' => $displayName,
                        'description'  => "Permission to {$action} " . ucfirst($module),
                    ]
                );
            }
        }

        $this->command->info('Permissions seeded successfully with Arabic display names!');
    }
}
