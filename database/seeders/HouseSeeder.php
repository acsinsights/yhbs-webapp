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
