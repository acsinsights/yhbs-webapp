<?php

namespace Database\Seeders;

use App\Models\Statistic;
use Illuminate\Database\Seeder;

class StatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statistics = [
            [
                'title' => 'Tour Completed',
                'count' => '26K+',
                'icon' => 'default/statistics/icon1.jpg',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Travel Experience',
                'count' => '12+',
                'icon' => 'default/statistics/icon2.jpg',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'title' => 'Happy Traveler',
                'count' => '20+',
                'icon' => 'default/statistics/icon3.jpg',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'title' => 'Retention Rate',
                'count' => '98%',
                'icon' => 'default/statistics/icon4.jpg',
                'is_active' => true,
                'order' => 4,
            ],
        ];

        foreach ($statistics as $statistic) {
            Statistic::create($statistic);
        }
    }
}
