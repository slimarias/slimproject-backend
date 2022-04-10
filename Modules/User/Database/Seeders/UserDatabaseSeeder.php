<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $superAdminRole = [
            'slug'    => 'superadmin',
            'name' => 'Super Admin',
        ];

        $superAdminRoleData = \Sentinel::getRoleRepository()->createModel()->create($superAdminRole);

        $adminRole = [
            'slug'    => 'admin',
            'name' => 'Admin',
        ];

        \Sentinel::getRoleRepository()->createModel()->create($adminRole);

        $userRole = [
            'slug'    => 'user',
            'name' => 'User',
        ];

        \Sentinel::getRoleRepository()->createModel()->create($userRole);

        $adminUser = [
            'email'    => 'admin@mail.com',
            'username' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'password' => 'admin123456',
        ];

        $adminUserData = \Sentinel::registerAndActivate($adminUser);

        $adminUserData->roles()->attach($superAdminRoleData->id);
    }
}
