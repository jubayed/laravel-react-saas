<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::updateOrCreate(['name' => 'admin', 'description' => 'Backend System users']);
        Role::updateOrCreate(['name' => 'user', 'description' => 'Site user']);
        Role::updateOrCreate(['name' => 'ben', 'description' => 'Disable or ben users']);
    }
}
