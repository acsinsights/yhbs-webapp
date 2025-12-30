<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use App\Models\Room;
use App\Models\House;
use App\Enums\BookingStatusEnum;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $rooms = Room::all();
        $houses = House::all();

        if ($users->isEmpty()) {
            $this->command->warn('Please run RoleSeeder first to create users!');
            return;
        }

        $bookings = [];

        // Create room bookings
        if ($rooms->isNotEmpty()) {
            foreach ($rooms->take(5) as $room) {
                $checkIn = Carbon::now();
                $checkOut = $checkIn->copy()->addDays(rand(1, 7));

                $bookings[] = [
                    'bookingable_type' => Room::class,
                    'bookingable_id' => $room->id,
                    'user_id' => $users->random()->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'price' => $room->price_per_night,
                    'discount_price' => $room->discount_price,
                    'status' => BookingStatusEnum::cases()[array_rand(BookingStatusEnum::cases())]->value,
                    'payment_status' => ['pending', 'paid', 'failed'][array_rand(['pending', 'paid', 'failed'])],
                    'payment_method' => ['cash', 'card', 'other', 'online'][array_rand(['cash', 'card', 'other', 'online'])],
                    'notes' => 'Booking created via seeder.',
                ];
            }
        }

        // Create house bookings
        if ($houses->isNotEmpty()) {
            // Create multiple bookings per house with varied dates
            foreach ($houses as $house) {
                // Create 2-3 bookings per house with different date ranges
                $bookingsCount = rand(2, 3);

                for ($i = 0; $i < $bookingsCount; $i++) {
                    $daysOffset = rand(1, 30);
                    $checkIn = Carbon::now()->addDays($daysOffset);
                    $nights = rand(1, 7);
                    $checkOut = $checkIn->copy()->addDays($nights);

                    // Calculate price based on nights
                    $price = $house->price_per_night;
                    if ($nights == 2 && $house->price_per_2night) {
                        $price = $house->price_per_2night;
                    } elseif ($nights == 3 && $house->price_per_3night) {
                        $price = $house->price_per_3night;
                    } elseif ($nights > 3 && $house->price_per_3night && $house->additional_night_price) {
                        $price = $house->price_per_3night + (($nights - 3) * $house->additional_night_price);
                    } elseif ($nights > 1) {
                        $price = $price * $nights;
                    }

                    $bookings[] = [
                        'bookingable_type' => House::class,
                        'bookingable_id' => $house->id,
                        'user_id' => $users->random()->id,
                        'adults' => rand(1, $house->adults ?? 10),
                        'children' => rand(0, $house->children ?? 5),
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'price' => $price,
                        'discount_price' => $house->discount_price,
                        'status' => BookingStatusEnum::cases()[array_rand(BookingStatusEnum::cases())]->value,
                        'payment_status' => ['pending', 'paid'][array_rand(['pending', 'paid'])],
                        'payment_method' => ['cash', 'card'][array_rand(['cash', 'card'])],
                        'notes' => 'House booking created via seeder for ' . $nights . ' night(s).',
                    ];
                }
            }
        }

        foreach ($bookings as $booking) {
            Booking::create($booking);
        }
    }
}
