<?php

namespace Database\Seeders;

use Backend\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $setting = $this->findSetting("backend.title");
        if (!$setting->exists) {
            $setting->fill([
                "display_name" => "backend::settings.title",
                "value" => "backend - The Beautiful Laravel Admin",
                "details" => '{}',
                "type" => "text",
                "order" => "1",
                "group" => "admin",
            ])->save();
        }

        $setting = $this->findSetting("backend.description");
        if (!$setting->exists) {
            $setting->fill([
                "display_name" => "backend::settings.description",
                "value" => "backend is a Laravel Admin Package that includes Laravel Generator operations, menu builder, and much more.",
                "details" => '{}',
                "type" => "text",
                "order" => "1",
                "group" => "admin",
            ])->save();
        }

        $setting = $this->findSetting("backend.name");
        if (!$setting->exists) {
            $setting->fill([
                "display_name" => "backend::settings.backend_name",
                "value" => "backend",
                "details" => '{}',
                "type" => "text",
                "order" => "1",
                "group" => "admin",
            ])->save();
        }

        $setting = $this->findSetting("backend.logo");
        if (!$setting->exists) {
            $setting->fill([
                "display_name" => "backend::settings.logo",
                "value" => "/vendor/jubayed/backend/images/logos/Laravel.svg",
                "details" => '{}',
                "type" => "image",
                "order" => "1",
                "group" => "admin",
            ])->save();
        }

        $setting = $this->findSetting("backend.favicon");
        if (!$setting->exists) {
            $setting->fill([
                "display_name" => "backend::settings.favicon",
                "value" => "/vendor/jubayed/backend/images/logos/favicon.png",
                "details" => '{}',
                "type" => "image",
                "order" => "1",
                "group" => "admin",
            ])->save();
        }
    }
    /**
     * [setting description].
     *
     * @param [type] $key [description]
     *
     * @return [type] [description]
     */
    protected function findSetting($key)
    {
        return Setting::firstOrNew(['key' => $key]);
    }
}
