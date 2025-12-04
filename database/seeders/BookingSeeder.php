<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use App\Models\Room;
use App\Models\Yacht;
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
        $yachts = Yacht::all();

        if ($users->isEmpty()) {
            $this->command->warn('Please run RoleSeeder first to create users!');
            return;
        }

        $bookings = [];

        // Create room bookings
        if ($rooms->isNotEmpty()) {
            foreach ($rooms->take(5) as $room) {
                $checkIn = Carbon::now()->addDays(rand(1, 30));
                $checkOut = $checkIn->copy()->addDays(rand(1, 7));

                $bookings[] = [
                    'bookingable_type' => Room::class,
                    'bookingable_id' => $room->id,
                    'user_id' => $users->random()->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'price' => $room->price,
                    'discount_price' => $room->discount_price,
                    'status' => BookingStatusEnum::cases()[array_rand(BookingStatusEnum::cases())]->value,
                    'payment_status' => ['pending', 'paid', 'failed'][array_rand(['pending', 'paid', 'failed'])],
                    'payment_method' => ['cash', 'card', 'other', 'online'][array_rand(['cash', 'card', 'other', 'online'])],
                    'notes' => 'Booking created via seeder.',
                ];
            }
        }

        // Create yacht bookings
        if ($yachts->isNotEmpty()) {
            foreach ($yachts->take(3) as $yacht) {
                $checkIn = Carbon::now()->addDays(rand(1, 30));
                $checkOut = $checkIn->copy()->addDays(rand(1, 3));

                $bookings[] = [
                    'bookingable_type' => Yacht::class,
                    'bookingable_id' => $yacht->id,
                    'user_id' => $users->random()->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'price' => $yacht->price,
                    'discount_price' => $yacht->discount_price,
                    'status' => BookingStatusEnum::cases()[array_rand(BookingStatusEnum::cases())]->value,
                    'payment_status' => ['pending', 'paid', 'failed'][array_rand(['pending', 'paid', 'failed'])],
                    'payment_method' => ['cash', 'card', 'other', 'online'][array_rand(['cash', 'card', 'other', 'online'])],
                    'notes' => 'Yacht booking created via seeder.',
                ];
            }
        }

        foreach ($bookings as $booking) {
            Booking::create($booking);
        }
    }
}
