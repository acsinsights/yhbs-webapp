<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case ONLINE = 'online';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::ONLINE => 'Online',
            self::OTHER => 'Other',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::CASH => 'badge-soft badge-info',
            self::CARD => 'badge-soft badge-primary',
            self::ONLINE => 'badge-soft badge-success',
            self::OTHER => 'badge-soft badge-ghost',
        };
    }
}
