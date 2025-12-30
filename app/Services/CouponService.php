<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CouponService
{
    /**
     * Validate and get coupon by code.
     *
     * @param string $code
     * @param float $bookingAmount
     * @param float|null $pricePerNight
     * @param int|null $nights
     * @param int|null $userId
     * @param string|null $propertyType
     * @param int|null $propertyId
     * @return array
     */
    public function validateCoupon(string $code, float $bookingAmount, ?float $pricePerNight = null, ?int $nights = null, ?int $userId = null, ?string $propertyType = null, ?int $propertyId = null): array
    {
        $userId = $userId ?? Auth::id();

        // Find coupon by code
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'error' => 'Invalid coupon code.',
            ];
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'error' => 'This coupon is no longer active.',
            ];
        }

        // Check validity dates
        $now = now();
        if ($now->lt($coupon->valid_from)) {
            return [
                'valid' => false,
                'error' => 'This coupon is not yet valid. Valid from: ' . $coupon->valid_from->format('d M Y'),
            ];
        }

        if ($now->gt($coupon->valid_until)) {
            return [
                'valid' => false,
                'error' => 'This coupon has expired.',
            ];
        }

        // Check total usage limit
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            return [
                'valid' => false,
                'error' => 'This coupon has reached its usage limit.',
            ];
        }

        // Check minimum booking amount
        if ($bookingAmount < $coupon->min_booking_amount) {
            return [
                'valid' => false,
                'error' => 'Minimum booking amount of ' . number_format((float) $coupon->min_booking_amount, 2) . ' KWD is required to use this coupon.',
            ];
        }

        // Check per-user usage limit
        if ($userId) {
            $userUsageCount = \App\Models\Booking::where('user_id', $userId)
                ->where('coupon_id', $coupon->id)
                ->count();

            if ($userUsageCount >= $coupon->usage_limit_per_user) {
                return [
                    'valid' => false,
                    'error' => 'You have already used this coupon the maximum number of times.',
                ];
            }
        }

        // Check if coupon is applicable to specific properties
        // Only validate if coupon is set to 'specific' properties
        if ($coupon->applicable_to === 'specific') {
            // If property info is not provided, we can't validate
            if (!$propertyType || !$propertyId) {
                return [
                    'valid' => false,
                    'error' => 'Property information is required for this coupon.',
                ];
            }

            $isApplicable = false;

            if ($propertyType === 'room' && $coupon->applicable_rooms) {
                $isApplicable = in_array((int) $propertyId, array_map('intval', $coupon->applicable_rooms));
            } elseif ($propertyType === 'house' && $coupon->applicable_houses) {
                $isApplicable = in_array((int) $propertyId, array_map('intval', $coupon->applicable_houses));
            }

            if (!$isApplicable) {
                return [
                    'valid' => false,
                    'error' => 'This coupon is not applicable to this property.',
                ];
            }
        }
        // If applicable_to is 'all', coupon applies to any property - no check needed

        // Check minimum nights required for free_nights coupons
        if ($coupon->discount_type->value === 'free_nights' && $coupon->min_nights_required && $nights) {
            if ($nights < $coupon->min_nights_required) {
                return [
                    'valid' => false,
                    'error' => 'This coupon requires a minimum of ' . $coupon->min_nights_required . ' nights booking to get ' . $coupon->discount_value . ' night(s) free.',
                ];
            }
        }

        // Calculate discount
        $discount = $coupon->calculateDiscount($bookingAmount, $pricePerNight);

        // For free nights, calculate how many nights are free
        $freeNights = 0;
        if ($coupon->discount_type === 'free_nights' && $pricePerNight && $nights) {
            $freeNights = min($coupon->discount_value, $nights);
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $discount,
            'new_total' => max(0, $bookingAmount - $discount),
            'free_nights' => $freeNights,
            'message' => 'Coupon applied successfully!',
        ];
    }

    /**
     * Apply coupon to booking.
     *
     * @param Coupon $coupon
     * @param float $discount
     * @return void
     */
    public function applyCoupon(Coupon $coupon, float $discount): void
    {
        $coupon->incrementUsage();
    }
}
