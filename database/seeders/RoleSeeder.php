<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles =  [
            [
                'id'=>'22a9b70e-6a20-11ec-90d6-0242ac120003',
                'name' => 'Admin',
                'permission' => json_encode(['dashboard','user_management','saree_management','jobs','vendors','designs','reports']),
                'ip_address' => '192.127.0.1',

            ],
            [
                'id'=>'22a9bbd2-6a20-11ec-90d6-0242ac120003',
                'name' => 'Member',
                'permission' => json_encode(['dashboard']),
                'ip_address' => '192.127.0.1',

            ]

          ];
        Role::insert($roles);
    }
}
