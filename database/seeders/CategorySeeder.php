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
                'icon' => '🏨',
            ],
            [
                'name' => 'Deluxe',
                'slug' => 'deluxe',
                'icon' => '⭐',
            ],
            [
                'name' => 'Suite',
                'slug' => 'suite',
                'icon' => '👑',
            ],
            [
                'name' => 'Executive',
                'slug' => 'executive',
                'icon' => '💼',
            ],
            [
                'name' => 'Presidential',
                'slug' => 'presidential',
                'icon' => '🏛️',
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
