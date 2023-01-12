<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'role_id' => '22a9b70e-6a20-11ec-90d6-0242ac120003',
            'firstname' => 'Admin',
            'lastname' => 'Fxbytes',
            'mobile' => '9877655322',
            'address' => 'Indore',
            'gender' => 'M',
            'email' => 'admin@fxbytes.com',
            'password' => bcrypt('Admin@1234'),
            'status' => 'enable',
            'ip_address'=>'192.127.0.0',

        ]);
    }
}
