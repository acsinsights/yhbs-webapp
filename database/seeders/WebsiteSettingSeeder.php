<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'name' => 'Google Tag (GTag)',
                'key' => 'gtag',
                'value' => '',
                'type' => 'textarea',
            ],
            [
                'name' => 'Meta Pixel',
                'key' => 'meta_pixel',
                'value' => '',
                'type' => 'textarea',
            ],
            [
                'name' => 'Maintenance Mode',
                'key' => 'maintenance',
                'value' => 'off',
                'type' => 'toggle',
            ],
            [
                'name' => 'Currency Symbol',
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'text',
            ],
            [
                'name' => 'Currency Name',
                'key' => 'currency_name',
                'value' => 'USD',
                'type' => 'text',
            ],
        ];

        foreach ($settings as $setting) {
            WebsiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
