<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\Room;
use App\Models\Category;
use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $amenities = Amenity::all();

        $houses = [
            [
                'name' => 'Grand Luxury Villa',
                'slug' => 'grand-luxury-villa',
                'description' => 'A luxurious 5-star villa with world-class amenities and exceptional service. Perfect for large families or groups seeking an unforgettable experience.',
                'house_number' => 'H001',
                'is_active' => true,
                'price_per_night' => 500.00,
                'price_per_2night' => 950.00,
                'price_per_3night' => 1350.00,
                'additional_night_price' => 400.00,
                'adults' => 10,
                'children' => 5,
                'rooms' => [
                    [
                        'name' => 'Master Suite',
                        'slug' => 'grand-luxury-villa-master-suite',
                        'room_number' => 'H001-101',
                        'description' => 'Spacious master suite with king bed, ensuite bathroom with jacuzzi, private balcony with stunning views.',
                        'price_per_night' => 150.00,
                        'price_per_2night' => 280.00,
                        'price_per_3night' => 400.00,
                        'additional_night_price' => 120.00,
                        'image' => '/default/rooms/master-suite.png',
                        'adults' => 2,
                        'children' => 2,
                    ],
                    [
                        'name' => 'Deluxe Double Room',
                        'slug' => 'grand-luxury-villa-deluxe-double',
                        'room_number' => 'H001-102',
                        'description' => 'Elegant double room with queen bed, ensuite bathroom, and modern amenities.',
                        'price_per_night' => 100.00,
                        'price_per_2night' => 190.00,
                        'price_per_3night' => 270.00,
                        'additional_night_price' => 80.00,
                        'image' => '/default/rooms/double-room.png',
                        'adults' => 2,
                        'children' => 1,
                    ],
                    [
                        'name' => 'Twin Room',
                        'slug' => 'grand-luxury-villa-twin',
                        'room_number' => 'H001-103',
                        'description' => 'Comfortable twin room with two single beds, perfect for friends or children.',
                        'price_per_night' => 80.00,
                        'price_per_2night' => 150.00,
                        'price_per_3night' => 210.00,
                        'additional_night_price' => 60.00,
                        'image' => '/default/rooms/suite.png',
                        'adults' => 2,
                        'children' => 2,
                    ],
                ],
            ],
            [
                'name' => 'Seaside Paradise House',
                'slug' => 'seaside-paradise-house',
                'description' => 'Beautiful beachfront house with direct sea access, infinity pool, and panoramic ocean views. Ideal for families seeking a coastal retreat.',
                'house_number' => 'H002',
                'is_active' => true,
                'price_per_night' => 600.00,
                'price_per_2night' => 1150.00,
                'price_per_3night' => 1650.00,
                'additional_night_price' => 500.00,
                'adults' => 12,
                'children' => 6,
                'rooms' => [
                    [
                        'name' => 'Sea View Master Suite',
                        'slug' => 'seaside-paradise-sea-view-master',
                        'room_number' => 'H002-201',
                        'description' => 'Luxurious master suite with king bed, floor-to-ceiling windows overlooking the ocean, private terrace.',
                        'price_per_night' => 180.00,
                        'price_per_2night' => 340.00,
                        'price_per_3night' => 480.00,
                        'additional_night_price' => 150.00,
                        'image' => '/default/rooms/sea-view.png',
                        'adults' => 2,
                        'children' => 2,
                    ],
                    [
                        'name' => 'Ocean View Double',
                        'slug' => 'seaside-paradise-ocean-view-double',
                        'room_number' => 'H002-202',
                        'description' => 'Bright double room with queen bed and partial ocean views.',
                        'price_per_night' => 110.00,
                        'price_per_2night' => 210.00,
                        'price_per_3night' => 300.00,
                        'additional_night_price' => 90.00,
                        'image' => '/default/rooms/sea-view.png',
                        'adults' => 2,
                        'children' => 1,
                    ],
                    [
                        'name' => 'Family Suite',
                        'slug' => 'seaside-paradise-family-suite',
                        'room_number' => 'H002-203',
                        'description' => 'Spacious family suite with one king bed and two single beds, perfect for families with children.',
                        'price_per_night' => 140.00,
                        'price_per_2night' => 270.00,
                        'price_per_3night' => 390.00,
                        'additional_night_price' => 120.00,
                        'image' => '/default/rooms/suites.png',
                        'adults' => 2,
                        'children' => 3,
                    ],
                    [
                        'name' => 'Pool View Room',
                        'slug' => 'seaside-paradise-pool-view',
                        'room_number' => 'H002-204',
                        'description' => 'Cozy room overlooking the infinity pool with direct pool access.',
                        'price_per_night' => 95.00,
                        'price_per_2night' => 180.00,
                        'price_per_3night' => 260.00,
                        'additional_night_price' => 75.00,
                        'image' => '/default/rooms/pool-view.png',
                        'adults' => 2,
                        'children' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Mountain View Retreat',
                'slug' => 'mountain-view-retreat',
                'description' => 'Peaceful mountain house surrounded by nature, featuring hiking trails, outdoor fire pit, and breathtaking mountain vistas.',
                'house_number' => 'H003',
                'is_active' => true,
                'price_per_night' => 450.00,
                'price_per_2night' => 850.00,
                'price_per_3night' => 1200.00,
                'additional_night_price' => 350.00,
                'adults' => 8,
                'children' => 4,
                'rooms' => [
                    [
                        'name' => 'Mountain Suite',
                        'slug' => 'mountain-view-retreat-mountain-suite',
                        'room_number' => 'H003-301',
                        'description' => 'Rustic luxury suite with king bed, stone fireplace, and private balcony facing the mountains.',
                        'price_per_night' => 130.00,
                        'price_per_2night' => 250.00,
                        'price_per_3night' => 360.00,
                        'additional_night_price' => 110.00,
                        'image' => '/default/rooms/suites.png',
                        'adults' => 2,
                        'children' => 2,
                    ],
                    [
                        'name' => 'Forest View Double',
                        'slug' => 'mountain-view-retreat-forest-double',
                        'room_number' => 'H003-302',
                        'description' => 'Cozy double room with queen bed and views of the surrounding forest.',
                        'price_per_night' => 90.00,
                        'price_per_2night' => 170.00,
                        'price_per_3night' => 240.00,
                        'additional_night_price' => 70.00,
                        'image' => '/default/rooms/double-room.png',
                        'adults' => 2,
                        'children' => 1,
                    ],
                    [
                        'name' => 'Loft Room',
                        'slug' => 'mountain-view-retreat-loft',
                        'room_number' => 'H003-303',
                        'description' => 'Charming loft room with exposed beams, two single beds, and mountain views.',
                        'price_per_night' => 85.00,
                        'price_per_2night' => 160.00,
                        'price_per_3night' => 230.00,
                        'additional_night_price' => 65.00,
                        'image' => '/default/rooms/suite.png',
                        'adults' => 2,
                        'children' => 2,
                    ],
                ],
            ],
        ];

        foreach ($houses as $houseData) {
            $rooms = $houseData['rooms'] ?? [];
            unset($houseData['rooms']);

            $house = House::updateOrCreate(
                ['slug' => $houseData['slug']],
                $houseData
            );

            // Create rooms for this house
            foreach ($rooms as $roomData) {
                $roomData['house_id'] = $house->id;
                $roomData['is_active'] = true;
                $roomData['meta_description'] = substr($roomData['description'], 0, 160);
                $roomData['meta_keywords'] = $house->name . ', ' . $roomData['name'];

                $room = Room::updateOrCreate(
                    [
                        'house_id' => $house->id,
                        'room_number' => $roomData['room_number'],
                    ],
                    $roomData
                );

                // Attach random categories if available
                if ($categories->isNotEmpty()) {
                    $randomCategories = $categories->random(min(2, $categories->count()));
                    $room->categories()->sync($randomCategories->pluck('id'));
                }

                // Attach random amenities if available
                if ($amenities->isNotEmpty()) {
                    $randomAmenities = $amenities->random(min(5, $amenities->count()));
                    $room->amenities()->sync($randomAmenities->pluck('id'));
                }
            }
        }

        $this->command->info('Created ' . count($houses) . ' houses with their rooms.');
    }
}
