<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Backend\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keys = [
            [
                "key" => 'backend.show',
                "table" => 'backend'
            ],
            [
                "key" => 'backend.about',
                "table" => 'backend'
            ],
            [
                "key" => 'backend.index',
                "table" => 'backend'
            ],
            [
                "key" => 'backend.artisan',
                "table" => 'backend'
            ],
            [
                "key" => 'backend.logs',
                "table" => 'backend'
            ],
            [
                "key" => 'customizers.show',
                "table" => 'customizers'
            ],
            
            

            //
            [
                "key" => 'users.about_me',
                "table" => 'users'
            ],
            [
                "key" => 'users.roles',
                "table" => 'users'
            ],
            [
                "key" => 'users.permissions',
                "table" => 'users'
            ],
            

            [
                "key" => 'menus.builder',
                "table" => 'menus'
            ],

            [
                "key" => 'menus.roles',
                "table" => 'menus'
            ],            
            [
                "key" => 'roles.permissions',
                "table" => 'roles'
            ],            
            [
                "key" => 'languages.translate_key',
                "table" => 'languages'
            ],

        ];

        foreach ($keys as $key) {

            $p[] = Permission::firstOrCreate([
                'name'        => $key['key'],
                'table_name'       => $key['table'],
            ]);
        }


        Permission::generateFor('menus');
        Permission::generateFor('roles');
        Permission::generateFor('users');
        Permission::generateFor('settings');
        Permission::generateFor('languages');
        Permission::generateFor('menu-items');
        Permission::generateFor('backups');


    }
}
