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
                'key' => 'maintenance_mode',
                'value' => 'off',
                'type' => 'toggle',
            ],
            [
                'name' => 'Map Location',
                'key' => 'map_location',
                'value' => 'Block 5, Sharq, Dayia Tower, 13th Floor.',
                'type' => 'text',
            ], 
            [
                'name' => 'Facebook URL',
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/',
                'type' => 'url',
            ],
            [
                'name' => 'Twitter URL',
                'key' => 'twitter_url',
                'value' => 'https://twitter.com/',
                'type' => 'url',
            ],
            [
                'name' => 'Instagram URL',
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/',
                'type' => 'url',
            ],
            [
                'name' => 'LinkedIn URL',
                'key' => 'linkedin_url',
                'value' => 'https://linkedin.com/',
                'type' => 'url',
            ],
            [
                'name' => 'YouTube URL',
                'key' => 'youtube_url',
                'value' => 'https://youtube.com/',
                'type' => 'url',
            ],
            [
                'name' => 'WhatsApp Number',
                'key' => 'whatsapp_number',
                'value' => '+965 XXXX XXXX',
                'type' => 'text',
            ],
            [
                'name' => 'Contact Email',
                'key' => 'contact_email',
                'value' => 'info@ikarusmarine.com',
                'type' => 'email',
            ],
            [
                'name' => 'Contact Phone',
                'key' => 'contact_phone',
                'value' => '+965 22022018',
                'type' => 'text',
            ],
            [
                'name' => 'Contact Address',
                'key' => 'contact_address',
                'value' => 'Enter full address',
                'type' => 'textarea',
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
