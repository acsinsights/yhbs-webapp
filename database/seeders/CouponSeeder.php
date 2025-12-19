<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Enums\DiscountTypeEnum;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome Discount',
                'description' => 'Get 10% off on your first booking',
                'discount_type' => DiscountTypeEnum::PERCENTAGE,
                'discount_value' => 10,
                'min_booking_amount' => 50,
                'max_discount_amount' => 20,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'SUMMER25',
                'name' => 'Summer Special',
                'description' => 'Flat 25 KWD off on bookings above 100 KWD',
                'discount_type' => DiscountTypeEnum::FIXED,
                'discount_value' => 25,
                'min_booking_amount' => 100,
                'max_discount_amount' => null,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
                'usage_limit' => 50,
                'usage_limit_per_user' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'FLASH50',
                'name' => 'Flash Sale',
                'description' => '50% off - Limited time offer',
                'discount_type' => DiscountTypeEnum::PERCENTAGE,
                'discount_value' => 50,
                'min_booking_amount' => 200,
                'max_discount_amount' => 100,
                'valid_from' => now(),
                'valid_until' => now()->addDays(7),
                'usage_limit' => 20,
                'usage_limit_per_user' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'STAYFREE',
                'name' => 'Stay 3 Get 1 Free',
                'description' => 'Book 3 nights and get 1 night absolutely free!',
                'discount_type' => DiscountTypeEnum::FREE_NIGHTS,
                'discount_value' => 1,
                'min_booking_amount' => 100,
                'max_discount_amount' => null,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
                'usage_limit' => 30,
                'usage_limit_per_user' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'WEEKEND2FREE',
                'name' => 'Weekend Special - 2 Nights Free',
                'description' => 'Book extended stay and get 2 nights free',
                'discount_type' => DiscountTypeEnum::FREE_NIGHTS,
                'discount_value' => 2,
                'min_booking_amount' => 300,
                'max_discount_amount' => null,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(1),
                'usage_limit' => 15,
                'usage_limit_per_user' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }
    }
}
