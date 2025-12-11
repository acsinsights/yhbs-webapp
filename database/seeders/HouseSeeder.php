<?php

namespace Database\Seeders;

use App\Models\House;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $houses = [
            [
                'name' => 'Grand Luxury House',
                'slug' => 'grand-luxury-house',
                'description' => 'A luxurious 5-star house with world-class amenities and exceptional service.',
                'house_number' => 'H001',
                'is_active' => true,
                'price_per_night' => 500.00,
                'price_per_2night' => 950.00,
                'price_per_3night' => 1350.00,
                'additional_night_price' => 400.00,
                'adults' => 10,
                'children' => 5,
            ],
        ];

        foreach ($houses as $house) {
            House::updateOrCreate(
                ['slug' => $house['slug']],
                $house
            );
        }
    }
}
