<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::PENDING => 'Pending',
            self::FAILED => 'Failed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PAID => 'badge-soft badge-success',
            self::PENDING => 'badge-soft badge-warning',
            self::FAILED => 'badge-soft badge-error',
        };
    }
}
