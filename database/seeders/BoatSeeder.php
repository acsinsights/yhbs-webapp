<?php

namespace Database\Seeders;

use App\Models\Boat;
use Illuminate\Database\Seeder;

class BoatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boats = [
            // Yacht (Marina Trips)
            [
                'name' => 'Marina 1',
                'slug' => 'marina-1',
                'service_type' => 'yacht',
                'description' => 'Luxury marina boat perfect for large groups and special occasions. Accommodates up to 60 passengers with premium amenities.',
                'image' => 'default/boats/1/cover.png',
                'min_passengers' => 1,
                'max_passengers' => 60,
                'price_1hour' => 100.00,
                'price_2hours' => 170.00,
                'price_3hours' => 220.00,
                'additional_hour_price' => 60.00,
                'location' => 'Marina',
                'features' => 'Air conditioning, Premium seating, Entertainment system, Refreshments',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Marina 2',
                'slug' => 'marina-2',
                'service_type' => 'yacht',
                'description' => 'Spacious marina boat ideal for family trips and celebrations. Holds up to 60 passengers comfortably.',
                'image' => 'default/boats/2/cover.png',
                'min_passengers' => 1,
                'max_passengers' => 60,
                'price_1hour' => 100.00,
                'price_2hours' => 170.00,
                'price_3hours' => 220.00,
                'additional_hour_price' => 60.00,
                'location' => 'Marina',
                'features' => 'Comfortable seating, Sound system, Outdoor deck, Safety equipment',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Marina 4',
                'slug' => 'marina-4',
                'service_type' => 'yacht',
                'description' => 'Intimate marina boat perfect for small groups and private tours. Accommodates up to 10 passengers.',
                'image' => 'default/boats/4/cover.png',
                'min_passengers' => 1,
                'max_passengers' => 10,
                'price_1hour' => 60.00,
                'price_2hours' => 90.00,
                'price_3hours' => 130.00,
                'additional_hour_price' => 40.00,
                'location' => 'Marina',
                'features' => 'Cozy interior, Music system, Fishing equipment available, Refreshments',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],

            // Private Taxi
            [
                'name' => 'Taxi 2',
                'slug' => 'taxi-2',
                'service_type' => 'taxi',
                'description' => 'Private water taxi service for quick and comfortable transportation. Ideal for up to 20 passengers.',
                'image' => 'default/boats/5/cover.png',
                'min_passengers' => 1,
                'max_passengers' => 20,
                'price_1hour' => 100.00,
                'price_2hours' => 180.00,
                'price_3hours' => 250.00,
                'additional_hour_price' => 80.00,
                'location' => 'Various Locations',
                'features' => 'Fast service, Professional captain, Safety equipment, Comfortable seating',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],

            // Ferry Services to Failaka
            [
                'name' => 'Sea Bus',
                'slug' => 'sea-bus',
                'service_type' => 'ferry',
                'description' => 'Large sea bus providing regular ferry service to Failaka Island with spacious seating.',
                'image' => 'default/boats/7/cover.png',
                'min_passengers' => 1,
                'max_passengers' => 100,
                'ferry_private_weekday' => 200.00,
                'ferry_private_weekend' => 250.00,
                'ferry_public_weekday' => 15.00,
                'ferry_public_weekend' => 20.00,
                'location' => 'Ferry Terminal',
                'features' => 'Large capacity, Air conditioned, Restrooms, Snack bar',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Bint Al-Khair',
                'slug' => 'bint-al-khair',
                'service_type' => 'ferry',
                'description' => 'Traditional ferry service to Failaka Island offering authentic maritime experience.',
                'image' => 'default/boats/8/cover.png',
                'min_passengers' => 1,
                'max_passengers' => 50,
                'ferry_private_weekday' => 500.00,
                'ferry_private_weekend' => 600.00,
                'ferry_public_weekday' => 15.00,
                'ferry_public_weekend' => 20.00,
                'location' => 'Ferry Terminal',
                'features' => 'Traditional design, Scenic views, Safety certified, Experienced crew',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Abu Al-Khair',
                'slug' => 'abu-al-khair',
                'service_type' => 'ferry',
                'description' => 'Premium ferry service to Failaka Island with enhanced comfort and amenities.',
                'image' => 'default/boats/9/cover.png',
                'min_passengers' => 1,
                'max_passengers' => 80,
                'ferry_private_weekday' => 1000.00,
                'ferry_private_weekend' => 1200.00,
                'ferry_public_weekday' => 10.00,
                'ferry_public_weekend' => 15.00,
                'location' => 'Ferry Terminal',
                'features' => 'Premium seating, Entertainment, Catering available, VIP lounge',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
            ],

            // Limousine Service
            [
                'name' => 'VIP Limousine',
                'slug' => 'vip-limousine',
                'service_type' => 'limousine',
                'description' => 'Luxury VIP limousine boat service for exclusive experiences. Perfect for special occasions.',
                'image' => 'default/boats/1/cover.png',
                'min_passengers' => 1,
                'max_passengers' => 20,
                'price_15min' => 5.00,
                'price_30min' => 10.00,
                'price_full_boat' => 80.00,
                'location' => 'VIP Terminal',
                'features' => 'Luxury interior, VIP service, Champagne service, Premium captain',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($boats as $boat) {
            Boat::updateOrCreate(
                ['slug' => $boat['slug']],
                $boat
            );
        }
    }
}
