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
                'name' => 'Standard Room 101',
                'slug' => 'standard-room-101',
                'room_number' => '101',
                'description' => 'Comfortable standard room with all basic amenities.',
                'price' => 150.00,
                'discount_price' => 120.00,
                'meta_description' => 'Comfortable standard room with all basic amenities. Perfect for budget travelers.',
                'meta_keywords' => 'standard room, budget hotel, comfortable stay',
                'is_active' => true,
                'adults' => 2,
                'children' => 1,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'name' => 'Deluxe Room 102',
                'slug' => 'deluxe-room-102',
                'room_number' => '102',
                'description' => 'Spacious deluxe room with modern furnishings.',
                'price' => 250.00,
                'discount_price' => 200.00,
                'meta_description' => 'Spacious deluxe room with modern furnishings and premium comfort.',
                'meta_keywords' => 'deluxe room, spacious, modern, premium',
                'is_active' => true,
                'adults' => 2,
                'children' => 2,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'name' => 'Luxury Suite 201',
                'slug' => 'luxury-suite-201',
                'room_number' => '201',
                'description' => 'Luxurious suite with separate living area.',
                'price' => 400.00,
                'discount_price' => 350.00,
                'meta_description' => 'Luxurious suite with separate living area. Ideal for extended stays.',
                'meta_keywords' => 'luxury suite, spacious, living area, premium',
                'is_active' => true,
                'adults' => 4,
                'children' => 2,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'name' => 'Executive Room 202',
                'slug' => 'executive-room-202',
                'room_number' => '202',
                'description' => 'Executive room perfect for business travelers.',
                'price' => 300.00,
                'discount_price' => 250.00,
                'meta_description' => 'Executive room perfect for business travelers with work desk and high-speed internet.',
                'meta_keywords' => 'executive room, business, corporate, work desk',
                'is_active' => true,
                'adults' => 2,
                'children' => 0,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'name' => 'Presidential Suite 301',
                'slug' => 'presidential-suite-301',
                'room_number' => '301',
                'description' => 'Presidential suite with premium amenities.',
                'price' => 800.00,
                'discount_price' => 700.00,
                'meta_description' => 'Presidential suite with premium amenities and exceptional luxury.',
                'meta_keywords' => 'presidential suite, luxury, premium, exclusive',
                'is_active' => true,
                'adults' => 4,
                'children' => 3,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'name' => 'City View Room 302',
                'slug' => 'city-view-room-302',
                'room_number' => '302',
                'description' => 'Standard room with city view.',
                'price' => 180.00,
                'discount_price' => 150.00,
                'meta_description' => 'Standard room with beautiful city view. Great value for money.',
                'meta_keywords' => 'city view, standard room, budget, view',
                'is_active' => true,
                'adults' => 2,
                'children' => 1,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'name' => 'Ocean View Deluxe 401',
                'slug' => 'ocean-view-deluxe-401',
                'room_number' => '401',
                'description' => 'Deluxe room with ocean view and balcony.',
                'price' => 350.00,
                'discount_price' => 300.00,
                'meta_description' => 'Deluxe room with stunning ocean view and private balcony.',
                'meta_keywords' => 'ocean view, deluxe, balcony, beach',
                'is_active' => true,
                'adults' => 3,
                'children' => 2,
            ],
            [
                'hotel_id' => $hotels->random()->id,
                'name' => 'Jacuzzi Suite 402',
                'slug' => 'jacuzzi-suite-402',
                'room_number' => '402',
                'description' => 'Suite with jacuzzi and premium services.',
                'price' => 500.00,
                'discount_price' => 450.00,
                'meta_description' => 'Luxurious suite with private jacuzzi and premium concierge services.',
                'meta_keywords' => 'jacuzzi, suite, luxury, spa, premium',
                'is_active' => true,
                'adults' => 2,
                'children' => 1,
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
