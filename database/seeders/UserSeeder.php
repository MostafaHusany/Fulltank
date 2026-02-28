<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'      => 'مدير النظام',
                'email'     => 'admin@fulltank.com',
                'phone'     => '01000000001',
                'password'  => Hash::make('123456'),
                'category'  => 'admin',
                'is_active' => true,
            ]
        );

        // Technical Users
        $technicals = [
            [
                'name'     => 'أحمد محمود',
                'username' => 'ahmed_tech',
                'email'    => 'ahmed@fulltank.com',
                'phone'    => '01000000002',
            ],
            [
                'name'     => 'محمد علي',
                'username' => 'mohamed_tech',
                'email'    => 'mohamed@fulltank.com',
                'phone'    => '01000000003',
            ],
        ];

        foreach ($technicals as $tech) {
            User::firstOrCreate(
                ['username' => $tech['username']],
                array_merge($tech, [
                    'password'  => Hash::make('123456'),
                    'category'  => 'technical',
                    'is_active' => true,
                ])
            );
        }

        // Client Companies
        $clients = [
            [
                'name'         => 'أحمد السيد',
                'company_name' => 'شركة النقل السريع',
                'username'     => 'fast_transport',
                'email'        => 'fast@example.com',
                'phone'        => '01100000001',
                'balance'      => 50000.00,
            ],
            [
                'name'         => 'محمود حسن',
                'company_name' => 'شركة التوصيل الذهبي',
                'username'     => 'golden_delivery',
                'email'        => 'golden@example.com',
                'phone'        => '01100000002',
                'balance'      => 75000.00,
            ],
            [
                'name'         => 'خالد إبراهيم',
                'company_name' => 'شركة النيل للنقل',
                'username'     => 'nile_transport',
                'email'        => 'nile@example.com',
                'phone'        => '01100000003',
                'balance'      => 100000.00,
            ],
            [
                'name'         => 'عمر فاروق',
                'company_name' => 'شركة مصر للشحن',
                'username'     => 'egypt_shipping',
                'email'        => 'egypt_ship@example.com',
                'phone'        => '01100000004',
                'balance'      => 35000.00,
            ],
            [
                'name'         => 'سامي عبدالله',
                'company_name' => 'شركة الصقر للنقل',
                'username'     => 'falcon_transport',
                'email'        => 'falcon@example.com',
                'phone'        => '01100000005',
                'balance'      => 60000.00,
            ],
        ];

        foreach ($clients as $clientData) {
            $balance = $clientData['balance'];
            unset($clientData['balance']);

            $client = User::firstOrCreate(
                ['username' => $clientData['username']],
                array_merge($clientData, [
                    'password'  => Hash::make('123456'),
                    'category'  => 'client',
                    'is_active' => true,
                ])
            );

            // Create wallet for client
            Wallet::firstOrCreate(
                ['user_id' => $client->id],
                [
                    'valide_balance'   => $balance,
                    'pendding_balance' => 0,
                    'is_active'        => true,
                ]
            );
        }

        // Station Managers
        $managers = [
            [
                'name'     => 'ياسر عبدالرحمن',
                'username' => 'station_nasr',
                'email'    => 'nasr_station@example.com',
                'phone'    => '01200000001',
            ],
            [
                'name'     => 'حسام الدين',
                'username' => 'station_giza',
                'email'    => 'giza_station@example.com',
                'phone'    => '01200000002',
            ],
            [
                'name'     => 'طارق سعيد',
                'username' => 'station_alex',
                'email'    => 'alex_station@example.com',
                'phone'    => '01200000003',
            ],
            [
                'name'     => 'مصطفى كامل',
                'username' => 'station_october',
                'email'    => 'october_station@example.com',
                'phone'    => '01200000004',
            ],
            [
                'name'     => 'عادل منصور',
                'username' => 'station_helwan',
                'email'    => 'helwan_station@example.com',
                'phone'    => '01200000005',
            ],
        ];

        foreach ($managers as $manager) {
            User::firstOrCreate(
                ['username' => $manager['username']],
                array_merge($manager, [
                    'password'  => Hash::make('123456'),
                    'category'  => 'station_manager',
                    'is_active' => true,
                ])
            );
        }
    }
}
