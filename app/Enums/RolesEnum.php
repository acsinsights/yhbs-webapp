<?php


namespace App\Enums;

enum RolesEnum: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case RECEPTION = 'reception';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::RECEPTION => 'Reception',
            self::CUSTOMER => 'Customer',
        };
    }
}
