<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (RolesEnum::cases() as $role) {
            Role::updateOrCreate(
                ['name' => $role->value],
                ['guard_name' => 'web']
            );
        }

        // Define users array
        $users = [
            [
                'email' => 'admin@mail.com',
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => RolesEnum::ADMIN->value,
            ],
            [
                'email' => 'reception@mail.com',
                'name' => 'Reception User',
                'password' => Hash::make('password'),
                'role' => RolesEnum::RECEPTION->value,
            ],
            [
                'email' => 'customer@mail.com',
                'name' => 'Customer User',
                'password' => Hash::make('password'),
                'role' => RolesEnum::CUSTOMER->value,
            ],
        ];

        // Create users and assign roles using Spatie
        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->assignRole($role);
        }
    }
}
