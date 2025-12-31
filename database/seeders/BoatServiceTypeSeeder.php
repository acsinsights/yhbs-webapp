<?php

namespace Database\Seeders;

use App\Models\BoatServiceType;
use Illuminate\Database\Seeder;

class BoatServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            [
                'name' => 'Yacht',
                'slug' => 'yacht',
                'is_active' => true,
            ],
            [
                'name' => 'Limousine Service',
                'slug' => 'limousine',
                'is_active' => true,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            BoatServiceType::updateOrCreate(
                ['slug' => $serviceType['slug']],
                $serviceType
            );
        }
    }
}
