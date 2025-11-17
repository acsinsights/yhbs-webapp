<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Yatch, Category, Amenity};

class YatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yatches = [
            [
                'name' => 'Luxury Ocean Explorer',
                'slug' => 'luxury-ocean-explorer',
                'description' => 'Premium luxury yacht with state-of-the-art facilities and exceptional comfort.',
                'sku' => 1001,
                'price' => 5000.00,
                'discount_price' => 4500.00,
                'length' => 120,
                'width' => 25,
                'max_guests' => 12,
                'max_crew' => 6,
                'max_fuel_capacity' => 5000,
                'max_capacity' => 18,
            ],
            [
                'name' => 'Royal Sea Voyager',
                'slug' => 'royal-sea-voyager',
                'description' => 'Elegant yacht perfect for special occasions and corporate events.',
                'sku' => 1002,
                'price' => 7500.00,
                'discount_price' => 6800.00,
                'length' => 150,
                'width' => 30,
                'max_guests' => 20,
                'max_crew' => 8,
                'max_fuel_capacity' => 8000,
                'max_capacity' => 28,
            ],
            [
                'name' => 'Sunset Cruiser',
                'slug' => 'sunset-cruiser',
                'description' => 'Beautiful mid-size yacht ideal for romantic getaways and small groups.',
                'sku' => 1003,
                'price' => 3000.00,
                'discount_price' => 2700.00,
                'length' => 80,
                'width' => 18,
                'max_guests' => 8,
                'max_crew' => 4,
                'max_fuel_capacity' => 3000,
                'max_capacity' => 12,
            ],
            [
                'name' => 'Adventure Seeker',
                'slug' => 'adventure-seeker',
                'description' => 'Sporty yacht designed for water activities and adventure enthusiasts.',
                'sku' => 1004,
                'price' => 4000.00,
                'discount_price' => 3600.00,
                'length' => 100,
                'width' => 22,
                'max_guests' => 10,
                'max_crew' => 5,
                'max_fuel_capacity' => 4000,
                'max_capacity' => 15,
            ],
            [
                'name' => 'Executive Business Class',
                'slug' => 'executive-business-class',
                'description' => 'Professional yacht equipped for business meetings and corporate entertainment.',
                'sku' => 1005,
                'price' => 6000.00,
                'discount_price' => 5500.00,
                'length' => 130,
                'width' => 28,
                'max_guests' => 16,
                'max_crew' => 7,
                'max_fuel_capacity' => 6000,
                'max_capacity' => 23,
            ],
        ];

        $categoryAssignments = [
            'luxury-ocean-explorer' => ['luxury-yacht', 'expedition-yacht'],
            'royal-sea-voyager' => ['luxury-yacht', 'party-yacht'],
            'sunset-cruiser' => ['family-cruiser'],
            'adventure-seeker' => ['expedition-yacht'],
            'executive-business-class' => ['corporate-charter'],
        ];

        $amenityAssignments = [
            'luxury-ocean-explorer' => ['onboard-chef', 'water-toys', 'helipad'],
            'royal-sea-voyager' => ['sun-deck-lounge', 'cinema-room', 'onboard-spa'],
            'sunset-cruiser' => ['sun-deck-lounge', 'water-toys'],
            'adventure-seeker' => ['water-toys', 'onboard-chef'],
            'executive-business-class' => ['onboard-chef', 'cinema-room', 'onboard-spa'],
        ];

        foreach ($yatches as $yatch) {
            $yatchModel = Yatch::updateOrCreate(
                ['slug' => $yatch['slug']],
                $yatch
            );

            if (isset($categoryAssignments[$yatch['slug']])) {
                $categoryIds = Category::whereIn('slug', $categoryAssignments[$yatch['slug']])->pluck('id');
                if ($categoryIds->isNotEmpty()) {
                    $yatchModel->categories()->sync($categoryIds);
                }
            }

            if (isset($amenityAssignments[$yatch['slug']])) {
                $amenityIds = Amenity::whereIn('slug', $amenityAssignments[$yatch['slug']])->pluck('id');
                if ($amenityIds->isNotEmpty()) {
                    $yatchModel->amenities()->sync($amenityIds);
                }
            }
        }
    }
}
