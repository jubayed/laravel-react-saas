<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Backend\Models\Customizer;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $user = User::updateOrCreate(
            [
                'email' => 'admin@admin.com',
            ],
            [
                'name' => 'Backend User',
                'email' => 'admin@admin.com',
                'password' => \bcrypt('password'),
            ]
        );

        // init user
        $user = $user->assignRole('admin');
        $user = User::where( 'email', 'admin@admin.com')->first();
        Customizer::updateOrCreate([
            'user_id' => $user->id
        ]);
    }
}
