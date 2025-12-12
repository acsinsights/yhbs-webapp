<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::PENDING => 'Pending',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PAID => 'badge-soft badge-success',
            self::PENDING => 'badge-soft badge-warning',
            self::CANCELLED => 'badge-error',
            self::FAILED => 'badge-soft badge-error',
        };
    }
}
