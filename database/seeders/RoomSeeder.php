<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\House;
use App\Models\Category;
use App\Models\Amenity;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $houses = House::all();
        $categories = Category::all();
        $amenities = Amenity::all();

        if ($houses->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Please run HouseSeeder and CategorySeeder first!');
            return;
        }

        $rooms = [
            [
                'house_id' => $houses->random()->id,
                'name' => 'Double Room',
                'slug' => 'double-room',
                'room_number' => '101',
                'description' => 'Double rooms are recently renovated, spacious accommodations, ensuite with toilet, shower and bath and toiletries. Rooms are spacious, comfortable and spotlessly clean. These rooms have been recently refurbished and are fully ensuite with toilet, shower and bath and toiletries. Rooms area equipped with flat-screen TVs with satellite channels and wifi. Rooms are spacious, comfortable and spotlessly clean. Rooms have king bed and Twin bed.',
                'price' => 60.00,
                'image' => '/default/rooms/double-room.png',
                'meta_description' => 'Recently renovated double rooms with ensuite facilities, satellite TV, and wifi. Spacious, comfortable and spotlessly clean accommodations.',
                'meta_keywords' => 'double room, king bed, twin bed, ensuite, satellite TV, wifi, renovated',
                'is_active' => true,
                'adults' => 2,
                'children' => 5,
            ],
            [
                'house_id' => $houses->random()->id,
                'name' => 'Sea View Room',
                'slug' => 'sea-view-room',
                'room_number' => '201',
                'description' => 'As spectacular as the breath-taking views they offer. Perfect for Couple\'s or family\'s use. Ensuite with toilet, shower and bath and toiletries. This category of room satisfies all the expectations one might have of a luxurious experience. Rooms are spacious, comfortable and spotlessly clean. These rooms have been recently refurbished and are fully ensuite with toilet, shower and bath and toiletries. Rooms area equipped with flat-screen TVs with satellite channels and wifi. Rooms are spacious, comfortable and spotlessly clean. Rooms have king bed and Twin bed.',
                'price' => 70.00,
                'image' => '/default/rooms/sea-view.png',
                'meta_description' => 'Spectacular sea view rooms perfect for couples or families. Luxurious experience with breath-taking views and modern amenities.',
                'meta_keywords' => 'sea view, luxury room, couple, family, ensuite, satellite TV, wifi, king bed',
                'is_active' => true,
                'adults' => 2,
                'children' => 5,
            ],
            [
                'house_id' => $houses->random()->id,
                'name' => 'Pool View Room',
                'slug' => 'pool-view-room',
                'room_number' => '301',
                'description' => 'If you like swimming and relaxing it is an ideal place to stay. The room has a connecting indoor to swimming pool. They are perfect for Couples or Small family\'s use. Ensuite with toilet, shower and bath and toiletries. Rooms are spacious, comfortable and spotlessly clean. These rooms have been recently refurbished and are fully ensuite with toilet, shower and bath and toiletries. Rooms area equipped with flat-screen TVs with satellite channels and wifi. Rooms are spacious, comfortable and spotlessly clean. Rooms have king bed and Twin bed.',
                'price' => 90.00,
                'image' => '/default/rooms/pool-view.png',
                'meta_description' => 'Pool view rooms with connecting indoor access to swimming pool. Perfect for couples or small families who enjoy swimming and relaxation.',
                'meta_keywords' => 'pool view, swimming pool, indoor pool, couple, family, ensuite, satellite TV, wifi',
                'is_active' => true,
                'adults' => 2,
                'children' => 5,
            ],
            [
                'house_id' => $houses->random()->id,
                'name' => 'Suite Room',
                'slug' => 'suite-room',
                'room_number' => '401',
                'description' => 'Suites are perfect for one person\'s or family\'s use. Ensuite with toilet, shower and bath and toiletries. Rooms are spacious, comfortable and spotlessly clean. These rooms have been recently refurbished and are fully ensuite with toilet, shower and bath and toiletries. Rooms area equipped with flat-screen TVs with satellite channels and wifi. Rooms are spacious, comfortable and spotlessly clean. Rooms have king bed and Twin bed.',
                'price' => 80.00,
                'image' => '/default/rooms/suites.png',
                'meta_description' => 'Spacious suite rooms perfect for individuals or families. Recently refurbished with modern amenities and comfortable furnishings.',
                'meta_keywords' => 'suite, family room, spacious, ensuite, satellite TV, wifi, king bed, twin bed',
                'is_active' => true,
                'adults' => 2,
                'children' => 5,
            ],
        ];

        foreach ($rooms as $roomData) {
            $room = Room::updateOrCreate(
                [
                    'house_id' => $roomData['house_id'],
                    'room_number' => $roomData['room_number'],
                ],
                $roomData
            );

            // Attach random categories
            $randomCategories = $categories->random(min(2, $categories->count()));
            $room->categories()->sync($randomCategories->pluck('id'));

            // Attach random amenities
            $randomAmenities = $amenities->random(min(5, $amenities->count()));
            $room->amenities()->sync($randomAmenities->pluck('id'));
        }
    }
}
