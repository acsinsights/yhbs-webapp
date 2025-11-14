<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\RolesEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => RolesEnum::ADMIN->value,
            ]
        );

        // Create Reception User
        User::updateOrCreate(
            ['email' => 'reception@mail.com'],
            [
                'name' => 'Reception User',
                'password' => Hash::make('password'),
                'role' => RolesEnum::RECEPTION->value,
            ]
        );

        // Create Customer User
        User::updateOrCreate(
            ['email' => 'customer@mail.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('password'),
                'role' => RolesEnum::CUSTOMER->value,
            ]
        );
    }
}
