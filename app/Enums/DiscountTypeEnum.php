<?php

namespace App\Enums;

enum DiscountTypeEnum: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case FREE_NIGHTS = 'free_nights';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage (%)',
            self::FIXED => 'Fixed Amount (KWD)',
            self::FREE_NIGHTS => 'Free Nights',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Discount as a percentage of the booking amount',
            self::FIXED => 'Fixed discount amount in KWD',
            self::FREE_NIGHTS => 'Number of free nights/days (e.g., Book 3 nights, get 1 free)',
        };
    }
}
