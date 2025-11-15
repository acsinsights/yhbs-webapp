<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Hotel;
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
        $hotels = Hotel::all();
        $categories = Category::all();
        $amenities = Amenity::all();

        if ($hotels->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Please run HotelSeeder and CategorySeeder first!');
            return;
        }

        $rooms = [
            [
                'hotel_id' => $hotels->random()->id,
                'room_number' => '101',
                'description' => 'Comfortable standard room with all basic amenities.',
                'price' => 150.00,
                'discount_price' => 120.00,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'room_number' => '102',
                'description' => 'Spacious deluxe room with modern furnishings.',
                'price' => 250.00,
                'discount_price' => 200.00,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'room_number' => '201',
                'description' => 'Luxurious suite with separate living area.',
                'price' => 400.00,
                'discount_price' => 350.00,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'room_number' => '202',
                'description' => 'Executive room perfect for business travelers.',
                'price' => 300.00,
                'discount_price' => 250.00,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'room_number' => '301',
                'description' => 'Presidential suite with premium amenities.',
                'price' => 800.00,
                'discount_price' => 700.00,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'room_number' => '302',
                'description' => 'Standard room with city view.',
                'price' => 180.00,
                'discount_price' => 150.00,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'room_number' => '401',
                'description' => 'Deluxe room with ocean view and balcony.',
                'price' => 350.00,
                'discount_price' => 300.00,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'room_number' => '402',
                'description' => 'Suite with jacuzzi and premium services.',
                'price' => 500.00,
                'discount_price' => 450.00,
            ],
        ];

        foreach ($rooms as $roomData) {
            $room = Room::updateOrCreate(
                [
                    'hotel_id' => $roomData['hotel_id'],
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
