<?php

namespace App\Enums;

enum BookingStatusEnum: string
{
    case WAITING = 'waiting';
    case BOOKED = 'booked';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::WAITING => 'Waiting',
            self::BOOKED => 'Booked',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
        };
    }
}
