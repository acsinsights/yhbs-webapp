<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            HouseSeeder::class,
            CategorySeeder::class,
            AmenitySeeder::class,
            RoomSeeder::class,
            BoatServiceTypeSeeder::class,
            BoatSeeder::class,
            // BookingSeeder::class,
            WebsiteSettingSeeder::class,
            PageMetaSeeder::class,
            SliderSeeder::class,
            TestimonialSeeder::class,
            StatisticSeeder::class,
            PolicyPageSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
