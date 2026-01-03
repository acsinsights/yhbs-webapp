<?php

namespace App\Models;

use App\Enums\DiscountTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_nights_required',
        'min_booking_amount',
        'max_discount_amount',
        'valid_from',
        'valid_until',
        'usage_limit',
        'usage_count',
        'usage_limit_per_user',
        'is_active',
        'applicable_to',
        'applicable_rooms',
        'applicable_houses',
        'applicable_boats',
    ];

    protected $casts = [
        'discount_type' => DiscountTypeEnum::class,
        'discount_value' => 'decimal:2',
        'min_booking_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'usage_limit_per_user' => 'integer',
        'is_active' => 'boolean',
        'applicable_rooms' => 'array',
        'applicable_houses' => 'array',
        'applicable_boats' => 'array',
    ];

    /**
     * Get the bookings that used this coupon.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if coupon is valid for use.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($now->lt($this->valid_from) || $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount for given amount.
     * For free_nights type, pass the price_per_night as second parameter.
     */
    public function calculateDiscount(float $amount, ?float $pricePerNight = null): float
    {
        if ($this->discount_type === DiscountTypeEnum::PERCENTAGE) {
            $discount = ($amount * $this->discount_value) / 100;
        } elseif ($this->discount_type === DiscountTypeEnum::FREE_NIGHTS) {
            // For free nights, calculate based on nights * price per night
            if ($pricePerNight) {
                $discount = $this->discount_value * $pricePerNight;
            } else {
                // Fallback: If price per night not provided, treat as fixed amount
                $discount = $this->discount_value;
            }
        } else {
            // Fixed amount
            $discount = $this->discount_value;
        }

        // Apply max discount limit if set
        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        // Ensure discount doesn't exceed the amount
        return min($discount, $amount);
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
