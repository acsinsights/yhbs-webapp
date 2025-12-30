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
            // Marina Trips
            [
                'name' => 'Marina 1',
                'slug' => 'marina-1',
                'service_type' => 'marina_trip',
                'description' => 'Luxury marina boat perfect for large groups and special occasions. Accommodates up to 60 passengers with premium amenities.',
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
                'service_type' => 'marina_trip',
                'description' => 'Spacious marina boat ideal for family trips and celebrations. Holds up to 60 passengers comfortably.',
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
                'service_type' => 'marina_trip',
                'description' => 'Intimate marina boat perfect for small groups and private tours. Accommodates up to 10 passengers.',
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
                'min_passengers' => 1,
                'max_passengers' => 20,
                'price_per_hour' => 100.00,
                'location' => 'Various Locations',
                'features' => 'Fast service, Professional captain, Safety equipment, Comfortable seating',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],

            // Ferry Services to Failaka
            [
                'name' => 'Taxi 2 Ferry',
                'slug' => 'taxi-2-ferry',
                'service_type' => 'ferry',
                'description' => 'Ferry service to Failaka Island. Available for private charter or public trips.',
                'min_passengers' => 1,
                'max_passengers' => 20,
                'private_trip_price' => 70.00,
                'private_trip_return_price' => 70.00,
                'price_per_person_adult' => 15.00,
                'price_per_person_child' => 10.00,
                'location' => 'Ferry Terminal',
                'features' => 'Island transport, Comfortable journey, Safety certified, Scheduled departures',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Sea Bus',
                'slug' => 'sea-bus',
                'service_type' => 'ferry',
                'description' => 'Large sea bus providing regular ferry service to Failaka Island with spacious seating.',
                'min_passengers' => 1,
                'max_passengers' => 100,
                'private_trip_price' => 200.00,
                'private_trip_return_price' => 200.00,
                'price_per_person_adult' => 15.00,
                'price_per_person_child' => 10.00,
                'location' => 'Ferry Terminal',
                'features' => 'Large capacity, Air conditioned, Restrooms, Snack bar',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Bint Al-Khair',
                'slug' => 'bint-al-khair',
                'service_type' => 'ferry',
                'description' => 'Traditional ferry service to Failaka Island offering authentic maritime experience.',
                'min_passengers' => 1,
                'max_passengers' => 50,
                'private_trip_price' => 500.00,
                'private_trip_return_price' => 500.00,
                'price_per_person_adult' => 15.00,
                'price_per_person_child' => 10.00,
                'location' => 'Ferry Terminal',
                'features' => 'Traditional design, Scenic views, Safety certified, Experienced crew',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Abu Al-Khair',
                'slug' => 'abu-al-khair',
                'service_type' => 'ferry',
                'description' => 'Premium ferry service to Failaka Island with enhanced comfort and amenities.',
                'min_passengers' => 1,
                'max_passengers' => 80,
                'private_trip_price' => 1000.00,
                'private_trip_return_price' => 1000.00,
                'price_per_person_adult' => 10.00,
                'price_per_person_child' => 5.00,
                'location' => 'Ferry Terminal',
                'features' => 'Premium seating, Entertainment, Catering available, VIP lounge',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
            ],

            // Limousine Service
            [
                'name' => 'VIP Limousine',
                'slug' => 'vip-limousine',
                'service_type' => 'limousine',
                'description' => 'Luxury VIP limousine boat service for exclusive experiences. Perfect for special occasions.',
                'min_passengers' => 1,
                'max_passengers' => 20,
                'price_15min' => 5.00,
                'price_30min' => 10.00,
                'price_full_boat' => 80.00,
                'location' => 'VIP Terminal',
                'features' => 'Luxury interior, VIP service, Champagne service, Premium captain',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 9,
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
