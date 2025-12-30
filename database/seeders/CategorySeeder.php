<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'icon' => 'https://placehold.co/100x100/4F46E5/FFFFFF?text=Standard',
                'type' => 'room',
            ],
            [
                'name' => 'Deluxe',
                'slug' => 'deluxe',
                'icon' => 'https://placehold.co/100x100/10B981/FFFFFF?text=Deluxe',
                'type' => 'room',
            ],
            [
                'name' => 'Suite',
                'slug' => 'suite',
                'icon' => 'https://placehold.co/100x100/F59E0B/FFFFFF?text=Suite',
                'type' => 'room',
            ],
            [
                'name' => 'Executive',
                'slug' => 'executive',
                'icon' => 'https://placehold.co/100x100/8B5CF6/FFFFFF?text=Executive',
                'type' => 'room',
            ],
            [
                'name' => 'Presidential',
                'slug' => 'presidential',
                'icon' => 'https://placehold.co/100x100/EF4444/FFFFFF?text=Presidential',
                'type' => 'room',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
