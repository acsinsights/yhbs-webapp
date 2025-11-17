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
        ];

        foreach ($hotels as $hotel) {
            Hotel::updateOrCreate(
                ['slug' => $hotel['slug']],
                $hotel
            );
        }
    }
}
