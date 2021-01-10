<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Backend\Models\Language;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Language::firstOrCreate(
            [
                'universal' => 'en',
            ],
            [
                'name' => 'English',
                'native' => 'English',
                'status' => 'active'
            ]
        );
        Language::firstOrCreate(
            [
                'universal' => 'bn',
            ],
            [
                'name' => 'Bengali',
                'native' => 'বাংলা',
            ]
        );


    }
}
