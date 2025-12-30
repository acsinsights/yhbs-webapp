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
                'icon' => 'https://placehold.co/100x100/3B82F6/FFFFFF?text=WiFi',
                'type' => 'room',
            ],
            [
                'name' => 'Air Conditioning',
                'slug' => 'air-conditioning',
                'icon' => 'https://placehold.co/100x100/06B6D4/FFFFFF?text=AC',
                'type' => 'room',
            ],
            [
                'name' => 'TV',
                'slug' => 'tv',
                'icon' => 'https://placehold.co/100x100/6366F1/FFFFFF?text=TV',
                'type' => 'room',
            ],
            [
                'name' => 'Mini Bar',
                'slug' => 'mini-bar',
                'icon' => 'https://placehold.co/100x100/EC4899/FFFFFF?text=Bar',
                'type' => 'room',
            ],
            [
                'name' => 'Room Service',
                'slug' => 'room-service',
                'icon' => 'https://placehold.co/100x100/14B8A6/FFFFFF?text=Service',
                'type' => 'room',
            ],
            [
                'name' => 'Balcony',
                'slug' => 'balcony',
                'icon' => 'https://placehold.co/100x100/F59E0B/FFFFFF?text=Balcony',
                'type' => 'room',
            ],
            [
                'name' => 'Safe',
                'slug' => 'safe',
                'icon' => 'https://placehold.co/100x100/6B7280/FFFFFF?text=Safe',
                'type' => 'room',
            ],
            [
                'name' => 'Jacuzzi',
                'slug' => 'jacuzzi',
                'icon' => 'https://placehold.co/100x100/8B5CF6/FFFFFF?text=Jacuzzi',
                'type' => 'room',
            ],
            [
                'name' => 'Ocean View',
                'slug' => 'ocean-view',
                'icon' => 'https://placehold.co/100x100/0EA5E9/FFFFFF?text=Ocean',
                'type' => 'room',
            ],
            [
                'name' => 'City View',
                'slug' => 'city-view',
                'icon' => 'https://placehold.co/100x100/64748B/FFFFFF?text=City',
                'type' => 'room',
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
