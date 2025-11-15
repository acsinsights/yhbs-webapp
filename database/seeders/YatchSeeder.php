<?php

namespace Database\Seeders;

use App\Models\Yatch;
use Illuminate\Database\Seeder;

class YatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yatches = [
            [
                'name' => 'Luxury Ocean Explorer',
                'slug' => 'luxury-ocean-explorer',
                'description' => 'Premium luxury yacht with state-of-the-art facilities and exceptional comfort.',
                'sku' => 1001,
                'price' => 5000.00,
                'discount_price' => 4500.00,
            ],
            [
                'name' => 'Royal Sea Voyager',
                'slug' => 'royal-sea-voyager',
                'description' => 'Elegant yacht perfect for special occasions and corporate events.',
                'sku' => 1002,
                'price' => 7500.00,
                'discount_price' => 6800.00,
            ],
            [
                'name' => 'Sunset Cruiser',
                'slug' => 'sunset-cruiser',
                'description' => 'Beautiful mid-size yacht ideal for romantic getaways and small groups.',
                'sku' => 1003,
                'price' => 3000.00,
                'discount_price' => 2700.00,
            ],
            [
                'name' => 'Adventure Seeker',
                'slug' => 'adventure-seeker',
                'description' => 'Sporty yacht designed for water activities and adventure enthusiasts.',
                'sku' => 1004,
                'price' => 4000.00,
                'discount_price' => 3600.00,
            ],
            [
                'name' => 'Executive Business Class',
                'slug' => 'executive-business-class',
                'description' => 'Professional yacht equipped for business meetings and corporate entertainment.',
                'sku' => 1005,
                'price' => 6000.00,
                'discount_price' => 5500.00,
            ],
        ];

        foreach ($yatches as $yatch) {
            Yatch::updateOrCreate(
                ['slug' => $yatch['slug']],
                $yatch
            );
        }
    }
}
