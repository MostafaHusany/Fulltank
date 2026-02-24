<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $targetUser = User::find(1);
        
        if (!isset($targetUser)) {
            $targetUser = User::create([
                'name'      => 'admin',
                'email'     => 'admin@goo.com',
                'phone'     => '01063200201',
                'password'  => bcrypt('12345678'),
                'category'  => 'admin',
                'is_active' => 1
            ]);

            Role::insert([
                [
                    'name'          => 'admin',
                    'display_name'  => 'Admin',
                    'description'   => 'The Super User!',
                ],
                [
                    'name'          => 'technical',
                    'display_name'  => 'Technical',
                    'description'   => 'Technical User',
                ],
            ]);

            $targetUser->addRole('admin');
        }

        $data = [];

        $permissions = [
            'users', 'roles', 'students', 'parents' , 'settings', 'tracks'
        ];

        foreach($permissions as $permission) {
            $data = array_merge($data, [
                [
                    'display_name' => 'add ' . $permission,
                    'name' => str_replace(' ', '_', $permission) . '_add',
                ],

                [
                    'display_name' => 'edit ' . $permission,
                    'name' => str_replace(' ', '_', $permission) . '_edit',
                ],

                [
                    'display_name' => 'show ' . $permission,
                    'name' => str_replace(' ', '_', $permission) . '_show',
                ],

                [
                    'display_name' => 'delete ' . $permission,
                    'name' => str_replace(' ', '_', $permission) . '_delete',
                ]

            ]);
        }

        Permission::insert($data);
    }
}
