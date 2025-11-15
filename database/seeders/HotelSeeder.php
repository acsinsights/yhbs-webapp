<?php

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = [
            [
                'name' => 'Grand Luxury Hotel',
                'slug' => 'grand-luxury-hotel',
                'description' => 'A luxurious 5-star hotel with world-class amenities and exceptional service.',
            ],
            [
                'name' => 'Ocean View Resort',
                'slug' => 'ocean-view-resort',
                'description' => 'Beautiful beachfront resort with stunning ocean views and modern facilities.',
            ],
            [
                'name' => 'City Center Hotel',
                'slug' => 'city-center-hotel',
                'description' => 'Conveniently located in the heart of the city, perfect for business and leisure travelers.',
            ],
            [
                'name' => 'Mountain Retreat Lodge',
                'slug' => 'mountain-retreat-lodge',
                'description' => 'Peaceful mountain lodge offering tranquility and natural beauty.',
            ],
            [
                'name' => 'Boutique Hotel Downtown',
                'slug' => 'boutique-hotel-downtown',
                'description' => 'Charming boutique hotel with unique character and personalized service.',
            ],
        ];

        foreach ($hotels as $hotel) {
            Hotel::updateOrCreate(
                ['slug' => $hotel['slug']],
                $hotel
            );
        }
    }
}
