<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            [
                'name' => 'Wi-Fi',
                'slug' => 'wi-fi',
                'icon' => '📶',
            ],
            [
                'name' => 'Air Conditioning',
                'slug' => 'air-conditioning',
                'icon' => '❄️',
            ],
            [
                'name' => 'TV',
                'slug' => 'tv',
                'icon' => '📺',
            ],
            [
                'name' => 'Mini Bar',
                'slug' => 'mini-bar',
                'icon' => '🍷',
            ],
            [
                'name' => 'Room Service',
                'slug' => 'room-service',
                'icon' => '🍽️',
            ],
            [
                'name' => 'Balcony',
                'slug' => 'balcony',
                'icon' => '🌅',
            ],
            [
                'name' => 'Safe',
                'slug' => 'safe',
                'icon' => '🔒',
            ],
            [
                'name' => 'Jacuzzi',
                'slug' => 'jacuzzi',
                'icon' => '🛁',
            ],
            [
                'name' => 'Ocean View',
                'slug' => 'ocean-view',
                'icon' => '🌊',
            ],
            [
                'name' => 'City View',
                'slug' => 'city-view',
                'icon' => '🏙️',
            ],
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                ['slug' => $amenity['slug']],
                $amenity
            );
        }
    }
}
