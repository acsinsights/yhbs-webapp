<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case PENDING = 'pending';
    case BOOKED = 'booked';
    case CHECKED_IN = 'checked_in';
    case CANCELLED = 'cancelled';
    case CHECKED_OUT = 'checked_out';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::BOOKED => 'Booked',
            self::CHECKED_IN => 'Checked In',
            self::CANCELLED => 'Cancelled',
            self::CHECKED_OUT => 'Checked Out',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'badge-soft badge-warning',
            self::BOOKED => 'badge-soft badge-info',
            self::CHECKED_IN => 'badge-soft badge-success',
            self::CANCELLED => 'badge-soft badge-error',
            self::CHECKED_OUT => 'badge-soft badge-primary',
        };
    }
}
